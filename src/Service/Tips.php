<?php

namespace Drupal\losnahuales\Service;

use Drupal\Core\Entity;
use Drupal\image\Entity\ImageStyle;



/**
 * Class Tips.
 *
 * @package Drupal\losnahuales
 */
class Tips {

  public static function get($tip) {

    $tips = [
      'haab_calendar_form_system' => t(
        "El día POP 0 es considerado el " .
        "año nuevo del Haab. " .
        "Los hallazgos arqueológicos localizan el día POP 0 " .
        " en la fecha 1 de Abril 2017 del calendario gregoriano. " .
        "En contraste, según la tradición practicada " .
        "en la actualidad por los pueblos Mayas de Guatemala, " .
        "el día 0 POP se celebró el 20 de Febrero. " .
        "Incluimos aquí la opción de consultar ambos sistemas."
      ),
    ];

    return $tips[$tip];
  }
}
