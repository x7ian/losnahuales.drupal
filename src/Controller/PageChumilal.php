<?php

namespace Drupal\losnahuales\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * @file
 * Contains \Drupal\losnahuales\Controller\PageChumilal.
 *
 */

/**
 * Class PageChumilal.
 *
 * @package Drupal\losnahuales\Controller
 */
class PageChumilal extends ControllerBase {

  /**
   * Calcular.
   *
   * @return string
   *   Return Hello string.
   */
  public function show(
    $yyyy = NULL,
    $mm = NULL,
    $dd = NULL,
    $sistema = NULL) {

    if ($yyyy != NULL and $mm != NULL and $dd != NULL) {
      $date = $yyyy . '/' . $mm . '/' . $dd;
    }
    else {
      $date = date('Y/m/d');
    }
    $form = \Drupal::formBuilder()->getForm(
      'Drupal\losnahuales\Form\CalculadorForm',
      $yyyy, $mm, $dd, $sistema
    );
    $estela = [
      '#theme' => 'calculador',
      '#fecha' => $date,
      '#sistema' => $sistema,
      '#set_metatags' => TRUE,
      '#attached' => array(
        'library' => array(
          'losnahuales/vue',
          //'losnahuales/vuetify',
          'losnahuales/estela'
        ),
      ),
    ];
    $wrapper = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['calculador-wrapper row'],
      ],
    ];
    $wrapper['form'] = [
      '#type' => 'markup',
      '#markup' => render($form),
      '#prefix' => '<div class="col-sm-12">',
      '#suffix' => '</div>',
    ];
    $wrapper['resultado'] = [
      '#type' => 'markup',
      '#markup' => render($estela),
      '#prefix' => '<div class="calculador-result-container col-sm-12">',
      '#suffix' => '</div>',
    ];
    return [
      '#type' => 'markup',
      '#markup' => render($wrapper),
      '#attached' => [
        'library' => [
          'losnahuales/losnahuales.commands',
        ],
      ],
    ];
  }

}
