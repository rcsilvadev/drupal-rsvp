<?php

namespace Drupal\rsvp_events\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SocialNetworksBlock
 * @package Drupal\rsvp_events\Plugin\Block
 * @author Rafael Silva <meet.rafaelsilva@gmail.com>
 *
 * Renders the RSVP form for Event content type
 *
 * @Block(
 *   id="rsvp_form_block",
 *   admin_label= @Translation("RSVP Form Block"),
 *   category = @Translation("RSVP EVENT"),
 * )
 */
class RsvpFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  private $form_builder;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param FormBuilderInterface $form_builder
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->form_builder = $form_builder;
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#theme' => 'rsvp_form_block',
      '#form' => $this->form_builder->getForm('Drupal\rsvp_events\Form\RsvpForm'),
    ];
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

}
