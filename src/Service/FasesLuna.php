<?php

namespace Drupal\losnahuales\Service;

/**
 * Class CustomUtils.
 *
 * @package Drupal\losnahuales
 */
class FasesLuna {

  /**
   * Return current moon phase.
   */
  public function getFase($year, $month, $day) {
    return $this->trig2($year, $month, $day);
  }

  /**
   * A method to get current moon phase.
   */
  private function trig2($year, $month, $day) {
    $n = floor(12.37 * ($year - 1900 + ((1.0 * $month - 0.5) / 12.0)));
    $rad = 3.14159265 / 180.0;
    $t = $n / 1236.85;
    $t2 = $t * $t;
    $as = 359.2242 + 29.105356 * $n;
    $am = 306.0253 + 385.816918 * $n + 0.010730 * $t2;
    $xtra = 0.75933 + 1.53058868 * $n
      + (((float) ('1.178e-4'))
      - ((float) ('1.55e-7')) * $t) * $t2;
    $xtra += (0.1734 - ((float) ('3.93e-4')) * $t) * sin($rad * $as)
      - 0.4068 * sin($rad * $am);
    $i = ($xtra > 0.0 ? floor($xtra) : ceil($xtra - 1.0));
    $j1 = $this->julday($year, $month, $day);
    $jd = (2415020 + 28 * $n) + $i;
    return ($j1 - $jd + 30) % 30;
  }

  /**
   * Moon Phase julian day proessor.
   */
  private function julday($year, $month, $day) {
    if ($year < 0) {
      $year++;
    }
    $jy = (int) ($year);
    $jm = (int) ($month) + 1;
    if ($month <= 2) {
      $jy--;
      $jm += 12;
    }
    $jul = floor(365.25 * $jy) + floor(30.6001 * $jm) + (int) ($day) + 1720995;
    if ($day + 31 * ($month + 12 * $year) >= (15 + 31 * (10 + 12 * 1582))) {
      $ja = floor(0.01 * $jy);
      $jul = $jul + 2 - $ja + floor(0.25 * $ja);
    }
    return $jul;
  }

}
