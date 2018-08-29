<?php

namespace Drupal\losnahuales\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\losnahuales\Service\CustomUtils;

/**
 * @file
 * Contains \Drupal\losnahuales\Controller\PageCargadores.
 *
 * Lists all year Barers of the mayan calendar.
 * Ordering the list in columns of years..
 */

/**
 * Cargadores page controller.
 */
class PageCargadores extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function listadoCargadores($sistema=NULL) {
    $u = new CustomUtils();
    $cargadores = [
      '#theme' => 'listado_cargadores',
      '#sistema' => $sistema,
    ];
    return $u->createContainer([$cargadores]);
  }

}
