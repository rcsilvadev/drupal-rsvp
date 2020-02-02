<?php

namespace Drupal\rsvp_events\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RsvpForm
 * @package Drupal\rsvp_events\Form
 *
 * Implements the RSVP button form logic
 */
class RsvpForm extends FormBase {

  /**
   * Maximum distance in miles between the current Attendee and the Event location
   */
  const MAXIMUM_DISTANCE = 20;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   * User storage from entity type manager
   */
  private $user_storage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   * Node storage from entity type manager
   */
  private $node_storage;

  /**
   * @var
   * The Event node entity
   */
  private $event;

  /**
   * @var
   * The Attendee user entity
   */
  private $attendee;

  /**
   * @var LoggerChannelFactory
   */
  private $logger;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactory $logger_factory) {
    $this->user_storage = $entity_type_manager->getStorage('user');
    $this->node_storage = $entity_type_manager->getStorage('node');
    $this->logger = $logger_factory->get('rsvp_events');
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'rsvp_form';
  }

  /**
   * @inheritDoc
   *
   * Builds the RSVP form, showing either the RSVP button or a message when the current Attendee has already joined
   * the event, or if his address is too far away from the Event location.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_attendee = $this->currentUser();

    // Only authenticated users must RSVP.
    if ($current_attendee->isAnonymous()) {
      return $this->getFormMarkupReturn("<a href='/user/login'>Log in</a> or <a href='/user/register'>register</a> now to join this event");
    }

    // Getting the event node from the URL
    $route_match = $this->getRouteMatch();
    $this->event = $route_match->getParameter('node');

    // Allows RSVP only once
    if ($this->attendeeJoinedTheEventAlready($current_attendee->id())) {
      return $this->getFormMarkupReturn("You have already RSVP'd for this event.");
    }

    // Allows only attendees less distant than 20 miles from the event location
    $this->attendee = $this->user_storage->load($current_attendee->id());
    if ($this->attendeeOutsideTheEventDistanceRadius()) {
      return $this->getFormMarkupReturn("Sorry, you're too far away from this event. <a href='/events'>Try others!</a>");
    }

    // "Join the event" AJAX triggered button
    $form['rsvp'] = [
      '#type' => 'button',
      '#value' => $this->t("Join the event"),
      '#ajax' => [
        'callback' => '::rsvpButtonAjaxCallback',
        'event' => 'click',
        'wrapper' => 'edit-rsvp',
        'progress' => [
          'message' => $this->t('Please wait...')
        ],
      ],
    ];

    return $form;
  }

  /**
   * @inheritDoc
   *
   * Submit being executed by a custom AJAX Callback
   * @see rsvpButtonAjaxCallback
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}


  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   *
   * Tries to add the current Attendee into the Event attendees list. It returns a markup array showing either a positive
   * or a negative feedback to the user in case of failing to save the node.
   */
  public function rsvpButtonAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Adding current Attendee into the Attendees field of the Event node
    $this->event->get('field_attendees')->appendItem(['target_id' => $this->attendee->id()]);

    // Saving the Event node
    try {
      $this->node_storage->save($this->event);
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return $this->getFormMarkupReturn("Something went wrong. Please try again later.");
    }

    return $this->getFormMarkupReturn("Thank you! You have RSVP'd!");
  }

  /**
   * @param $text
   * @return array
   *
   * Returns a markup array for form feedback messages
   */
  private function getFormMarkupReturn($text) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t($text),
    ];
  }

  /**
   * @return bool
   *
   * Checks if the current Attendee has already rsvp'd for an event.
   */
  private function attendeeJoinedTheEventAlready($current_attendee_id) {
    $event_attendees = $this->event->get('field_attendees')->getValue();
    foreach ($event_attendees as $key => $event_attendee) {
      if ($event_attendee['target_id'] === $current_attendee_id) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @return bool
   *
   * Checks if the current Attendee is outside the Event distance radius
   */
  private function attendeeOutsideTheEventDistanceRadius() {
    // Attendee user address coordinates
    $attendee_coordinates = $this->attendee
      ->get('field_address_coordinates')->first()->getValue();

    // Event node address coordinates
    $event_coordinates = $this->event
      ->get('field_event_map')
      ->first()
      ->getValue();

    // Returns the distance in miles between the current Attendee and the Event location
    $miles_away = $this->getMilesBetweenAttendeeAndEvent($attendee_coordinates, $event_coordinates);
    if ($miles_away > self::MAXIMUM_DISTANCE) {
      // Attendee outside the Event distance radius
      return TRUE;
    }

    // Attendee inside the Event distance radius
    return FALSE;
  }

  /**
   * @param $attendee_coordinates
   * @param $event_coordinates
   * @return float
   *
   * Returns the distance between the current Attendee and the Event location using the Haversine formula
   */
  private function getMilesBetweenAttendeeAndEvent($attendee_coordinates, $event_coordinates) {
    $attendee_lng = $attendee_coordinates['lng'];
    $attendee_lat = $attendee_coordinates['lat'];
    $event_lng = $event_coordinates['lng'];
    $event_lat = $event_coordinates['lat'];

    // The Haversine formula calculates the great-circle distance between two points – that is, the shortest distance over the earth’s surface
    $theta = $attendee_lng - $event_lng;
    $dist = rad2deg(
      acos(
      sin(deg2rad($attendee_lat)) * sin(deg2rad($event_lat)) + cos(deg2rad($attendee_lat)) * cos(deg2rad($event_lat)) * cos(deg2rad($theta))
      )
    );

    return $dist * 60 * 1.1515;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

}
