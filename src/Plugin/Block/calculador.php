<?php

namespace Drupal\losnahuales\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\losnahuales\ConvertidorFechas;

/**
 * Provides a 'calculador' block.
 *
 * @Block(
 *  id = "calculador",
 *  admin_label = @Translation("Calculador de Fechas"),
 * )
 */
class calculador extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['calculador']['#markup'] = 'Implement calculador.';

    return $build;
  }

}
