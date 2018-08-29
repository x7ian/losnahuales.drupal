<?php

namespace Drupal\losnahuales\Service;

/**
 * Class CustomDate.
 *
 * Custom dates handler that is based on Julian day correlations.
 * We need to be able to manage dates from before Gregorian Epoch,
 * For this reason we cannot relay on DrupalDate objects wich are based in
 * UNIX timestapms, we need a custom date handler.
 * Adapted to LosNahuales. Great deal of this comes from
 * http://pueblosoriginarios.com/meso/maya/maya/correlacion.html
 *
 * @package Drupal\losnahuales
 */
class CustomDate {

  /**
   * Fecha juliana en que inicia el calendairo greogriano.
   *
   * 1 de enero año 1 DC = 1721425.5
   *
   * @var gregorianEpoch
   */
  protected $gregorianEpoch = 1721425.5;
  protected $GREGORIAN_EPOCH = 1721425.5;

  /**
   * Get information about a week day name in Gregorian calendar.
   */
  public function getDay($day = NULL, $date = NULL) {
    $days = [
      0 => [
        'name' => t('sunday'),
        'dim' => t('sun'),
      ],
      1 => [
        'name' => t('monday'),
        'dim' => t('mon'),
      ],
      2 => [
        'name' => 'tuesday',
        'dim' => 'tue',
      ],
      3 => [
        'name' => 'wednesday',
        'dim' => 'wed',
      ],
      4 => [
        'name' => 'thursday',
        'dim' => 'thu',
      ],
      5 => [
        'name' => 'friday',
        'dim' => 'fri',
      ],
      6 => [
        'name' => 'saturday',
        'dim' => 'sat',
      ],
    ];
    if ($day === NULL) {
      return $days;
    }
    return $days[$day];
  }

  /**
   * Get information about a MONTH name in Gregorian calendar.
   *
   */
  public function getMonth($mm = NULL) {
    $months = [
      1 => [
        'name' => 'january',
        'dim' => 'jan',
      ],
      2 => [
        'name' => 'february',
        'dim' => 'feb',
      ],
      3 => [
        'name' => 'march',
        'dim' => 'mar',
      ],
      4 => [
        'name' => 'april',
        'dim' => 'apr',
      ],
      5 => [
        'name' => 'May',
        'dim' => 'may',
      ],
      6 => [
        'name' => 'June',
        'dim' => 'jun',
      ],
      7 => [
        'name' => 'july',
        'dim' => 'jul',
      ],
      8 => [
        'name' => 'August',
        'dim' => 'aug',
      ],
      9 => [
        'name' => 'september',
        'dim' => 'sep',
      ],
      10 => [
        'name' => 'october',
        'dim' => 'oct',
      ],
      11 => [
        'name' => 'november',
        'dim' => 'nov',
      ],
      12 => [
        'name' => 'december',
        'dim' => 'dec',
      ],
    ];
    if ($mm === NULL) {
      return $months;
    }
    if ($mm <= 0) {
      $mm = 12 + $mm;
    }
    return $months[(int) $mm];
  }

  /**
   * Is the given year a leap year in gregorian calendar.
   *
   * @param int $year
   *   Gregorian Year.
   */
  public function leapGregorian($year) {
    return ($this->mod($year, 4) == 0) &&
      (!(($this->mod($year, 100) == 0) && ($this->mod($year, 400) != 0)));
  }

  /**
   * Convertir fecha gegoriana a juliana.
   *
   * @var julian_date
   */
  public function gregorianToJd($year, $month, $day, $hh, $mn, $ss) {
    if ($year<0) {
      $year++;
    }
    return $this->astroToJd($year, $month, $day, $hh, $mn, $ss);
  }

  /**
   * Convertir fecha gegoriana a juliana.
   *
   * @var julian_date
   */
  public function astroToJd($year, $month, $day, $hh, $mn, $ss) {
    $jd = ($this->gregorianEpoch - 1) +
      (365 * ($year - 1)) +
      floor(($year - 1) / 4) +
      (-floor(($year - 1) / 100)) +
      floor(($year - 1) / 400) +
      floor((((367 * $month) - 362) / 12) +
      (($month <= 2) ? 0 : ($this->leapGregorian($year) ? -1 : -2)) +
      $day);
    return $jd;
  }

  public function jdToGregorian($jd) {
    list($year, $month, $day) = $this->jdToAstro($jd);
    if ($year<=0) {
      $year--;
    }
    return [$year, $month, $day];
  }

  /**
   * Convertir fecha juliana a gegoriana.
   *
   * @var gegorian_date
   */
  public function jdToAstro($jd) {
    $wjd = floor($jd - 0.5) + 0.5;
    //$wjd = floor($jd);
    $depoch = $wjd - $this->gregorianEpoch;
    $quadricent = floor($depoch / 146097);
    $dqc = $this->mod($depoch, 146097);
    $cent = floor($dqc / 36524);
    $dcent = $this->mod($dqc, 36524);
    $quad = floor($dcent / 1461);
    $dquad = $this->mod($dcent, 1461);
    $yindex = floor($dquad / 365);
    $year = ($quadricent * 400) + ($cent * 100) + ($quad * 4) + $yindex;
    if (!(($cent == 4) || ($yindex == 4))) {
      $year++;
    }
    $yearday = $wjd - $this->astroToJd($year, 1, 1, 0, 0, 0, 0);
    $leapadj = (
      ($wjd < $this->astroToJd($year, 3, 1, 0, 0, 0, 0)) ? 0
      : ($this->leapGregorian($year) ? 1 : 2)
    );
    $month = floor(((($yearday + $leapadj) * 12) + 373) / 367);
    $day = ($wjd - $this->astroToJd($year, $month, 1, 0, 0, 0, 0)) + 1;

    return [$year, $month, $day];
  }

  /**
   * Custom  MOD  --  Mod function that works for non-integers.
   */
  public function mod($a, $b) {
    return $a - ($b * floor($a / $b));
  }

  /**
   * Get century roman representation.
   */
  public function get_century_code($century) {
  	// XVIII
  	if (1700 <= $century && $century <= 1799)
  	  return 4;
  	// XIX
  	if (1800 <= $century && $century <= 1899)
  		return 2;
  	// XX
  	if (1900 <= $century && $century <= 1999)
  		return 0;
  	// XXI
  	if (2000 <= $century && $century <= 2099)
  		return 6;
  	// XXII
  	if (2100 <= $century && $century <= 2199)
  		return 4;
  	// XXIII
  	if (2200 <= $century && $century <= 2299)
  		return 2;
  	// XXIV
  	if (2300 <= $century && $century <= 2399)
  		return 0;
  	// XXV
  	if (2400 <= $century && $century <= 2499)
  		return 6;
  	// XXVI
  	if (2500 <= $century && $century <= 2599)
  		return 4;
  	// XXVII
  	if (2600 <= $century && $century <= 2699)
  		return 2;
  }

  /**
   * Get the day of a given date
   * Thanks to
   * https://www.mindstick.com/blog/387/calculating-day-of-the-week-for-any-date-in-javascript
   * @param $date
   */
  public function get_week_day($date) {
  	list($yyyy, $mm, $dd) = explode('/', $date);
    $a = floor((14 - $mm) / 12);
    $y = $yyyy - $a;
    $m = $mm + 12 * $a - 2;
    $d = $this->mod(
      ($dd + $y + floor($y / 4) - floor($y / 100) +
      floor($yyyy / 400) + floor((31 * $m) / 12)),
      7
    );
    return $this->getDay($d, $date);
  }

  /**
   * Get month name given a date.
   */
  public function get_month_of_year($date) {
  	$dateParts = explode('/', $date);
  	return $this->getMonth($dateParts[1]);
  }

  /**
   * convert calendar to Julian date
   */
  public function gregorianToJd2( $y, $m, $d, $h, $mn, $s )
  {
  	if( $y == 0 ) {
  		alert("There is no year 0 in the Julian system!");
      return "invalid";
    }
    if( $y == 1582 && $m == 10 && $d > 4 && $d < 15 ) {
  		alert("The dates 5 through 14 October, 1582, do not exist in the Gregorian system!");
      return "invalid";
    }
  	if( $y < 0 )  $y++;
  	if( $m > 2 ) {
  		$jy = $y;
  		$jm = $m + 1;
  	} else {
  		$jy = $y - 1;
  		$jm = $m + 13;
  	}
  	$intgr = floor( floor(365.25 * $jy)
      + floor(30.6001 * $jm) + $d + 1720995 );
  	//check for switch to Gregorian calendar
    $gregcal = 15 + 31*( 10 + 12 * 1582 );
  	if ( $d + 31 * ($m + 12 * $y) >= $gregcal )
    {
  		$ja = floor(0.01 * $jy);
  		$intgr += 2 - $ja + floor(0.25 * $ja);
  	}
  	//correct for half-day offset
  	$dayfrac = $h / 24.0 - 0.5;
  	if( $dayfrac < 0.0 ) {
  		$dayfrac += 1.0;
  		--$intgr;
  	}
  	//now set the fraction of a day
  	$frac = $dayfrac + ($mn + $s / 60.0) / 60.0 / 24.0;
      //round to nearest second
      $jd0 = ($intgr + $frac) * 100000;
      $jd  = floor($jd0);
      if( $jd0 - $jd > 0.5 ) ++$jd;
      return $jd / 100000;
  }

  /**
   * convert Julian date to calendar date
   * (algorithm adopted from Press et al.)
   */
  public function jdToGregorian2( $jd )
  {
  	//
  	// get the date from the Julian day number
  	//
    $intgr   = floor($jd);
    $frac    = $jd - $intgr;
    $gregjd  = 2299161;
    $era = 1;
    if ($gregjd > $jd) {
      $era = -1;
    }
  	if ( $intgr >= $gregjd )
    {
  		$tmp = floor( ( ($intgr - 1867216) - 0.25 ) / 36524.25 );
  		$j1 = $intgr + 1 + $tmp - floor(0.25 * $tmp);
  	} else
  		$j1 = $intgr;

  	//correction for half day offset
  	$dayfrac = $frac + 0.5;
  	if( $dayfrac >= 1.0 ) {
  		$dayfrac -= 1.0;
  		++$j1;
  	}
  	$j2 = $j1 + 1524;
  	$j3 = floor( 6680.0 + ( ($j2 - 2439870) - 122.1 ) / 365.25 );
  	$j4 = floor($j3 * 365.25);
  	$j5 = floor( ($j2 - $j4) / 30.6001 );
  	$d = floor($j2 - $j4 - floor($j5 * 30.6001));
  	$m = floor($j5 - 1);
  	if( $m > 12 ) $m -= 12;
  	$y = floor($j3 - 4715);
  	if( $m > 2 )   --$y;
  	if( $y <= 0 )  --$y;
  	// get time of day from day fraction
  	//
  	$hr  = floor($dayfrac * 24.0);
  	$mn  = floor(($dayfrac * 24.0 - $hr)*60.0);
  	$f  = (($dayfrac * 24.0 - $hr) * 60.0 - $mn) * 60.0;
  	$sc  = floor($f);
  	$f -= $sc;
    if( $f > 0.5 ) ++$sc;
    if( $y < 0 ) {
      $y = -$y;
    }
    if ($m>12) {
      $m = $this->mod($m, 12);
    }
    return [($y*$era), $m, $d, $hr, $mn, $sc, $f];
  }

}
