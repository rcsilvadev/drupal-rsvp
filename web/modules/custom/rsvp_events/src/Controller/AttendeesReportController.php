<?php

namespace Drupal\rsvp_events\Controller;

use Drupal\Core\Controller\ControllerBase;

class AttendeesReportController extends ControllerBase {

  public function content() {
    $attendees_report_block = views_embed_view('attendees_report', 'attendees_report');
    return [
      '#markup' =>  render($attendees_report_block),
    ];
  }


}
