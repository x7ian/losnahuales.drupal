<?php

namespace Drupal\losnahuales\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\losnahuales\Service\CustomUtils;

/**
 * @file
 * Contains \Drupal\losnahuales\Controller\PageCalendarios.
 *
 * Renders a haab calendar for a full year.
 * Prints the form and results for current calendar.
 */

/**
 * An example controller.
 */
class PageCalendarios extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function calendarioHaab(
    $yyyy = NULL,
    $sistema = NULL,
    $comienzo = NULL) {
    $u = new CustomUtils();
    if ($yyyy == NULL) {
      $yyyy = date('Y');
    }
    $form = \Drupal::formBuilder()->getForm(
      'Drupal\losnahuales\Form\CalendarioHaabForm',
      $yyyy, $sistema, $comienzo
    );
    $calendario = [
      '#theme' => 'calendario_haab',
      '#anno' => $yyyy,
      '#sistema' => $sistema,
      '#comienzo' => $comienzo,
    ];
    return $u->createContainer([$form, $calendario]);
  }

}
