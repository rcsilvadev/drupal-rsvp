<?php

namespace Drupal\rsvp_events\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Class AddressFieldFormatter
 * @package Drupal\rsvp_events\Plugin\Field\FieldFormatter
 * @author Rafael Silva <meet.rafaelsilva@gmail.com>
 *
 * @FieldFormatter(
 *   id = "address_formatter",
 *   label = @Translation("RSVP Address formatter"),
 *   field_types = {
 *      "address"
 *   }
 * )
 */
class AddressFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Formats the Address output into a plain text like ""334, Sebastian Street, São Paulo"');
    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    // Formats the address field into a string like "334, Sebastian Street, São Paulo"
    foreach ($items as $delta => $item) {
      $address_chunks = $item->getValue();
      $element[$delta] = [
        '#markup' => sprintf("%s, %s", $address_chunks['address_line1'], $address_chunks['locality']),
      ];
    }

    return $element;
  }



}
