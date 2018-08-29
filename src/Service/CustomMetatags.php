<?php

namespace Drupal\losnahuales\Service;

/**
 * Class CustomMetatags.
 *
 * @package Drupal\losnahuales
 */
class CustomMetatags {

  static function setMetatags($metatags) {
    $id = 'losnahuales_custom_metatags';
    $current_metatags = isset($GLOBALS[$id])? $GLOBALS[$id] : [];
    $GLOBALS[$id] = array_merge( $current_metatags, $metatags );
  }

  static function getMetatags() {
    $id = 'losnahuales_custom_metatags';
    return isset($GLOBALS[$id])? $GLOBALS[$id] : [];
  }

}
