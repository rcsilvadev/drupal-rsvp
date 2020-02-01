<?php

namespace Drupal\rsvp_general\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SocialNetworksBlock
 * @package Drupal\rsvp_general\Plugin\Block
 * @author Rafael Silva <meet.rafaelsilva@gmail.com>
 *
 * Provides a Social Networks block
 *
 * @Block(
 *   id="social_networks",
 *   admin_label= @Translation("Social Networks"),
 *   category = @Translation("RSVP EVENT"),
 * )
 */
class SocialNetworksBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#theme' => 'social_networks_block',
      '#social_networks' => $this->getConfiguration(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['social_networks'] = [
      '#type' => 'details',
      '#description' => $this->t('Insert below the URL of the Social Network pages'),
      '#title' => $this->t('Social Networks'),
      '#open' => TRUE,
    ];

    $form['social_networks']['facebook'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook URL'),
      '#default_value' => isset($config['facebook']) ? $config['facebook'] : '',
    ];

    $form['social_networks']['twitter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter URL'),
      '#default_value' => isset($config['twitter']) ? $config['twitter'] : '',
    ];

    $form['social_networks']['instagram'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instagram URL'),
      '#default_value' => isset($config['instagram']) ? $config['instagram'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    foreach ($values['social_networks'] as $social_network => $url) {
      $this->setConfigurationValue($social_network, $url);
    }
  }


}
