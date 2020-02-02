<?php

namespace Drupal\rsvp_events\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Annotation\ViewsField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to show a concatenated list of events that a certain user has attended.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("events_attended")
 */
class EventsAttendedViewsField extends FieldPluginBase {

  private $node_storage;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param EntityTypeManagerInterface $entity_type_manager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->node_storage = $entity_type_manager->getStorage('node');;
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    return parent::defineOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}



  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($values->uid) {
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'event')
        ->condition('field_attendees.target_id', $values->uid, 'IN');
      $events_attended = $query->execute();

      if ($events_attended) {
        $events_string = "";
        foreach ($events_attended as $key => $event_id) {
          $node = $this->node_storage->load($event_id);
          $events_string .= $node->getTitle() . ', ';
        }

        return trim($events_string, ', ');
      }
    }

    return "";
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }


}
