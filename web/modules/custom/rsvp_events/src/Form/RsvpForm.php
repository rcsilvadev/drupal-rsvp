<?php

namespace Drupal\rsvp_events\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RsvpForm extends FormBase {

  private $user_storage;
  private $node_storage;
  private $event;
  private $attendee;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->user_storage = $entity_type_manager->getStorage('user');
    $this->node_storage = $entity_type_manager->getStorage('node');
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'rsvp_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_attendee = $this->currentUser();

    // Only authenticated users must RSVP.
    if ($current_attendee->isAnonymous()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t("<a href='/user/login'>Log in</a> or <a href='/user/register'>register</a> now to join this event"),
      ];
    }

    $route_match = $this->getRouteMatch();
    $this->event = $route_match->getParameter('node');

    if ($this->event) {
      // Allows RSVP only once
      $event_attendees = $this->event->get('field_attendees')->getValue();
      if (!empty($event_attendees)) {
        foreach ($event_attendees as $key => $event_attendee) {
          if ($event_attendee['target_id'] === $current_attendee->id()) {
            return [
              '#type' => 'markup',
              '#markup' => $this->t("You have already RSVP'd for this event."),
            ];
          }
        }
      }

      // Allows only attendees less distant than 20 miles from the event location
      $this->attendee = $this->user_storage->load($current_attendee->id());

      $attendee_coordinates = $this->attendee->get('field_address_coordinates')->first()->getValue();
      $event_coordinates = $this->event->get('field_event_map')->first()->getValue();

      $miles_distant = $this->calculateMilesBetweenEventAndAttendee($attendee_coordinates, $event_coordinates);
      if ($miles_distant > 20) {
        return [
          '#type' => 'markup',
          '#markup' => $this->t("Sorry, you're too far away from this event. <a href='/events'>Try others!</a>"),
        ];
      }

    }

    $form['rsvp'] = [
      '#type' => 'button',
      '#value' => $this->t("Join the event"),
      '#ajax' => [
        'callback' => '::myAjaxCallback',
        'event' => 'click',
        'wrapper' => 'edit-rsvp',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Please wait...')
        ],
      ],
    ];

    return $form;
  }

  /**
   * Ajax callback.
   */
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    $this->event->get('field_attendees')->appendItem(['target_id' => $this->attendee->id()]);
    $this->node_storage->save($this->event);

    $form['rsvp'] = [
      '#type' => 'markup',
      '#markup' => $this->t("Thank you! You have RSVP'd!"),
    ];
    return $form['rsvp'];
  }

  private function calculateMilesBetweenEventAndAttendee($attendee_coordinates, $event_coordinates) {
    $attendee_lng = $attendee_coordinates['lng'];
    $attendee_lat = $attendee_coordinates['lat'];
    $event_lng = $event_coordinates['lng'];
    $event_lat = $event_coordinates['lat'];

    $theta = $attendee_lng - $event_lng;
    $dist = rad2deg(
      acos(
      sin(deg2rad($attendee_lat)) * sin(deg2rad($event_lat)) + cos(deg2rad($attendee_lat)) * cos(deg2rad($event_lat)) * cos(deg2rad($theta))
      )
    );

    return $dist * 60 * 1.1515;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('entity_type.manager'),
    );
  }

}
