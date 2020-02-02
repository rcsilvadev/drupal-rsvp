<?php

/**
 * Implements hook_views_data().
 */
function rsvp_events_views_data() {

  $data['views']['events_attended'] = array(
    'title' => t('Events attended'),
    'help' => t('Get a concatenated list of events that a certain user has attended.'),
    'field' => array(
      'id' => 'events_attended',
    ),
  );

  return $data;
}
