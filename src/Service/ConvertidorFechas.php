<?php

namespace Drupal\losnahuales\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\losnahuales\Service\FasesLuna;
use Drupal\losnahuales\Service\CustomDate;

/**
 * @file
 * Contains Drupal\losnahuales\ConvertidorFechas.
 *
 * Convertidor de fechas del calendario gregoriano al calendario Maya.
 *
Cholqij - Tzolkin
0 B’ATZ’ - Chuwen
1 E - Eb
2 Aj - Ben
3 Ix - Ix
4 Tzikin - Men
5 Ajmaq - Kib
6 Noj - Kaban
7 Tijax - Etznab
8 Kawoq - Kawak
9 Ajpu - Ajaw
10 imox - Imix
11 Iq - Ik
12 Aqabal - Akbal
13 Kat - Kan
14 kan - Chikchan
15 Kame - Kimi
16 Kej - Manik
17 Qanil - Lamat
18 Toj - Muluk
19 Tzi - Ok

Haab
0 Pop
1 Wo
2 Sip
3 Sotz
4 Sek
5 Xul
6 yaxkin
7 Mol
8 Chen
9 Yax
10 Sak
11 Keh
12 Mak
13 Kankin
14 Muwan
15 Pax
16 Kayab
17 Kumku
18 Wayeb
 */

/**
 * Class ConvertidorFechas.
 *
 * @package Drupal\losnahuales
 */
class ConvertidorFechas {

  /**
   * Fecha Gregoriana.
   *
   * @var gregorian_date
   */
  protected $gregorian_date = NULL;

  /**
   * Resultados Fecha Maya.
   *
   * @var resuts
   */
  protected $results = NULL;

  /**
   * Lista de Nahuales iniciando en batz.
   *
   * @var nahuales
   */
  protected $nahuales_batz = array(
    0 => 'Batz',
    1 => 'E',
    2 => 'Aj',
    3 => 'Ix',
    4 => 'Tzikin',
    5 => 'Ajmaq',
    6 => 'Noj',
    7 => 'Tijax',
    8 => 'Kawoq',
    9 => 'Ajpu',
    10 => 'Imox',
    11 => 'Iq',
    12 => 'Aqabal',
    13 => 'Kat',
    14 => 'Kan',
    15 => 'Kame',
    16 => 'Kej',
    17 => 'Qanil',
    18 => 'Toj',
    19 => 'Tzi',
    );

    protected $nahuales_data = array(
      0 => [ //'Batz',
        'direccion' => '', // e, n, o, s
      ],
      1 => [ //'E',
        'direccion' => 's',
        'default_icon' => 'dia-sur-color.png',
      ],
      2 => [ //'Aj',
        'direccion' => ''
      ],
      3 => [ //'Ix',
        'direccion' => ''
      ],
      4 => [ //'Tzikin',
        'direccion' => ''
      ],
      5 => [ //'Ajmaq',
        'direccion' => ''
      ],
      6 => [ //'Noj',
        'direccion' => 'e',
        'default_icon' => 'dia-este-color.png',
      ],
      7 => [ //'Tijax',
        'direccion' => ''
      ],
      8 => [ //'Kawoq',
        'direccion' => ''
      ],
      9 => [ //'Ajpu',
        'direccion' => ''
      ],
      10 => [ //'Imox',
        'direccion' => ''
      ],
      11 => [ //'Iq',
        'direccion' => 'n',
        'default_icon' => 'dia-norte-color.png',
      ],
      12 => [ //'Aqabal',
        'direccion' => ''
      ],
      13 => [ //'Kat',
        'direccion' => ''
      ],
      14 => [ //'Kan',
        'direccion' => ''
      ],
      15 => [ //'Kame',
        'direccion' => ''
      ],
      16 => [ //'Kej',
        'direccion' => 'o',
        'default_icon' => 'dia-oeste-color.png',
      ],
      17 => [ //'Qanil',
        'direccion' => ''
      ],
      18 => [ //'Toj',
        'direccion' => ''
      ],
      19 => [ //'Tzi',
        'direccion' => ''
      ],
    );

    /**
     * Lista de Nahuales iniciando en imox.
     *
     * @var nahuales
     */
    protected $nahuales_imox = array(
      0 => 'Imox',
      1 => 'Iq',
      2 => 'Aqabal',
      3 => 'Kat',
      4 => 'Kan',
      5 => 'Kame',
      6 => 'Kej',
      7 => 'Qanil',
      8 => 'Toj',
      9 => 'Tzi',
      10 => 'Batz',
      11 => 'E',
      12 => 'Aj',
      13 => 'Ix',
      14 => 'Tzikin',
      15 => 'Ajmaq',
      16 => 'Noj',
      17 => 'Tijax',
      18 => 'Kawoq',
      19 => 'Ajpu',
      );

  /**
   * Lista de Meses del haab.
   *
   * @var haab
   */
  protected $haab = array(
    0 => 'Pop',
    1 => 'Wo',
    2 => 'Sip',
    3 => 'Sotz',
    4 => 'Sek',
    5 => 'Xul',
    6 => 'yaxkin',
    7 => 'Mol',
    8 => 'Chen',
    9 => 'Yax',
    10 => 'Sak',
    11 => 'Sej',
    12 => 'Mak',
    13 => 'Kankin',
    14 => 'Muwan',
    15 => 'Pax',
    16 => 'Kayab',
    17 => 'Kumku',
    18 => 'Wayeb',
  );

  /**
   * Los diferentes Sistemas de conteo decalendarios utilizados.
   *
   * @var nahuales
   */
   protected $sistemas = [
     // test of mayan epochs
     // 3114-08-11 - GMT 584282.5
     //var_dump($do->gregorianToJd(-3113, 8, 11));
     // 3117-7-27 - Patrick 583172.5
     //var_dump($do->gregorianToJd(-3116, 7, 27));
     0 => [
       'id' => 0,
       'name' => 'Arqueologico (Goodman, Martínez Thompson)',
       'nahual0' => 'imox',
       'mayan_epoch' => 584282.5, // 584283 Goodman Martínez Thompson
       'cholqij' => [
         'inicio' => [4, 19/*ajpu*/],
         //'inicio_kin' => -0.25 // al amanecer
       ],
       'haab' => [
         'inicio' => [8, 17/*kumku*/],
       ],
       'g' => [
         'inicio' => 8
       ],
       'dia1_haab' => [0, 0/*pop*/],
       'dia1_cholqij' => [1, 0/*Batz*/],
       'inicio' => [
         'cuenta_larga' => '13.0.0.0.0',
         'g' => 8,
         'cholqij' => [4, 19/*ajpu*/],
         'haab' => [8, 17/*kumku*/],
         'gregorian' => '-3114/08/11',
       ],
     ],
     1 => [
       'id' => 1,
       'name' => 'Tradicional (ALMG)',
       'nahual0' => 'batz',
       'mayan_epoch' => 584282.5,
       'cholqij' => [
         'inicio' => [4, 19/*ajpu*/],
         //'inicio_kin' => -0.25 // al amanecer
       ],
       'haab' => [
         'inicio' => [3, 1/*kumku*/],
       ],
       'g' => [
         'inicio' => 8
       ],
       'dia1_haab' => [0, 0/*Uo*/],
       'dia1_cholqij' => [8, 0/*Batz*/],
       'inicio' => [
         'cuenta_larga' => '13.0.0.0.0', /* Supuestamente?? */
         'g' => 8,
         'cholqij' => [4, 19/*ajpu*/],
         'haab' => [3, 1/*kumku*/], //[3, 1/*uo*/],
         'gregorian' => '-3114/08/11',
       ],
     ],




     /*2 => [
       'id' => 2,
       'name' => 'Julio Menchú (Ajuste del Fuego Nuevo)',
       'nahual0' => 'batz',
       'mayan_epoch' => 584282.5, // 11 ago 3113
       'cholqij' => [
         'inicio' => [4, 19], // ajpu
         //'inicio_kin' => -0.25 // al amanecer
       ],
       'haab' => [
         'inicio' => [8, 17], // kumku
       ],
       'g' => [
         'inicio' => 8
       ],
       'dia1_haab' => [0, 0], // Uo
       'dia1_cholqij' => [8, 0], // Batz
       'inicio' => [
         'cuenta_larga' => '0.0.0.0.0', // inicio cuenta larga
         'g' => 8,
         'cholqij' => [4, 9], //ajpu
         'haab' => [8, 1], // kumku
         'gregorian' => '-3114/08/11',
       ],
       'cargadores' => [
         'orden' => [
           1, // e
           6, // noj
           11, // iq
           16, // kej
         ],
         'ajuste_fuego_nuevo' => [ // wayeb largo
           'duracion' => 13, // haab kines: 13 biciestos en 52 haabs
           'frecuencia' => 18980, // haab kines: cada 52 haabs
           'descanso' => [
             'frecuencia' => 144000, // 144000 kines = 1 Baktun
             'duracion' => 365*12 // 12 haabs
           ]
         ]
       ],
     ],
     3 => [
       'id' => 3,
       'name' => 'Geraldine Patrick (Ritual del Cambio de cargador)',
       'nahual0' => 'imox',
       'mayan_epoch' => 583172.5, //  -3117/07/27
                        // 1136 dias de diferencia con GMT(584283)
       'cholqij' => [
         'inicio' => [4, 19], // ajpu
         'inicio_kin' => -0.5 // al amanecer
       ],
       'g' => [
         'inicio' => 8,
       ],
       'inicio' => [
         'cuenta_larga' => '0.0.0.0.0', // inicio cuenta larga
         'g' => 8,
         'cholqij' => [4, 19], // ajpu
         'haab' => [8, 17], // kumku
         'gregorian' => '-3117/07/27', // 27 jul 3117
       ],

       'cargador_inicial' => [8, 6], // 8 noj
       'ccc' => [ // ceremonias de cambio de cargador anual
         'duracion' => 0.25,
         'descanso' => [
           'frecuencia' => 144000, // 144000 kines = 1 Baktun
           'duracion' => 4383, // floor(12*365.25) kines
         ],
         'calendarios' => ['haab', 'cholQij', 'cuentaLarga']
       ],
     ]*/
   ];

  /**
  * Lista de Nahuales.
  *
  * @var nahuales
  */
  protected $sistema_default = 1;

  /**
   * Changed que denota que hubo cambios en los valores y es necesario
   * recontruir los resultados.
   *
   * @var changed'g'
   */
  protected $changed = false;

  //  AMOD  --  Modulus function which returns numerator if modulus is zero

  protected function amod($a, $b)
  {
      return $this->mod(($a - 1), $b) + 1;
  }

  /*  MOD  --  Modulus function which works for non-integers.  */
  function mod($a, $b)
  {
      return $a - ($b * floor($a / $b));
  }

  /**
   * Constructor.
   *
   * @param $gregorian_date
   * If its a number will be used as a gregorian year
   */
  public function __construct($gregorian_date, $sistema_id=NULL) {


    if (!$sistema_id) {
      $sistema_id = $this->sistema_default;
    }
    $this->sistema_id = $sistema_id;
    $this->sistema = $this->sistemas[$sistema_id];
    $this->nahuales = $this->sistema['nahuales'] = $this->nahuales_imox;

    /*switch ($this->sistema['nahual0']) {
      case 'imox':
        $this->nahuales = $this->sistema['nahuales'] = $this->nahuales_imox;
      break;
      case 'batz':
      default:
        $this->nahuales = $this->sistema['nahuales'] = $this->nahuales_batz;
      break;
    }*/
    $this->mayan_epoch = $this->sistema['mayan_epoch'];// - 1;
    $this->inicio = $this->sistema['inicio'];

    if ($gregorian_date==NULL) {
      $gregorian_date = $this->inicio['gregorian'];
    } elseif (is_numeric($gregorian_date))
    {
      $gregorian_date = $gregorian_date . '/1/1';
      //$gregorian_date = $do->gregorianToJd($gregorian_date, 1,1);
    }

    $this->setDate($gregorian_date);
      //$this->cargadores = isset($this->sistema['cargadores']['orden'])?
        //  $this->sistema['cargadores']['orden'] : null;
      //$this->ccc = isset($this->sistema['cargadores']['ccc'])?
        //  $this->sistema['cargadores']['ccc'] : null;

  }

  public function setDate($gregorian_date_or_julian) {
    $do = new CustomDate();

    if (is_numeric($gregorian_date_or_julian)) { // si es numero es juliana
      $this->julian_date = $gregorian_date_or_julian;
      list($this->yyyy, $this->mm, $this->dd)
        = $do->jdToGregorian($this->julian_date);
      $gregorian_date = [$this->yyyy, $this->mm, $this->dd];
      $this->gregorian_date = implode('/', $gregorian_date);
    } else { // si no es numero debe ser string asi que es gregoriana
      $this->gregorian_date = $gregorian_date_or_julian;
      list($this->yyyy, $this->mm, $this->dd)
        = explode('/', $this->gregorian_date);
      $this->julian_date = $jd = $do->gregorianToJd(
        (int)$this->yyyy,
        (int)$this->mm,
        (int)$this->dd,
        0,
        0,
        0
      );
    }
    if ($this->isBeforeMayanEpoch()) {
      $this->setDate($this->mayan_epoch);
    }
  }

  public function getGregorian() {
    return $this->gregorian_date;
  }


  public function getJulian() {
    return $this->julian_date;
  }

  public function getInicioGregoriano() {
    return explode('/', $this->sistema['inicio']['gregorian']);
  }

  public function SetHaab0() {
    $this->setDate($this->mayan_epoch);
  }

  public function addDays($days) {
    $this->julian_date += $days;
    $this->setDate($this->julian_date);
    $this->changed = true;
  }

  public function getSistemas() {
    return $this->sistemas;
  }

  public function getNahualesList() {
    return $this->nahuales;
  }

  function getHaabList() {
    return $this->haab;
  }

  function getSistema() {
    return $this->sistema;
  }

  /**
    * Function GetAll().
    * Given a gregorian date prevously set, it returns thye corresponding
    * date information for the maya calendars.
    *
    */
  public function getAll() {
    $cache_id = 'losnahuales_calculo_' . $this->gregorian_date
        . '_' . $this->sistema['id'];
    if ($resultados = \Drupal::cache()->get($cache_id)) {
      return $resultados->data;
    }
    $count = list($baktun, $katun, $tun, $uinal, $kin)
      = $this->jdToMayanCount($this->julian_date);
    list($haab_index, $haab_no, $haab_tun_data) =
      $this->jdToMayanHaab();
    $haab_index--;
    list($cholqij_index, $cholqij_no) =
      $this->jdToMayanCholqij();
    $haab = $this->haab[$haab_index];
    $cholqij = $this->nahuales[$cholqij_index];
    $g = $this->getG();
    $luna = $this->faseLunar();
    $this->changed = false;
    $mayan_haabs = $this->mayanHaabs();
    $mayan_days = $this->mayanDays();
    $cargador = $this->getCargador();
    list($kins, $data) = $this->kinsBajoCargador(); //getKins();//$this->mayanDaysWithOutCCC();
    $mayan_days_absolute = $mayan_days + $data['factor_direccional'];
    $mayan_julian_days = $this->mayanDaysWithCCC();
    $this->results = array(
      'julian' => $this->julian_date,
      'gregoriana' => $this->gregorian_date,
      'baktun' => $baktun,
      'katun' => $katun,
      'tun' => $tun,
      'uinal' => $uinal,
      'kin' => $kin,
      'haab_no' => $haab_no,
      'haab' => $haab,
      'haab_index' => $haab_index,
      'haab_name' => $this->haab[$haab_index],
      'cholqij_no' => $cholqij_no,
      'cholqij' => $cholqij,
      'cholqij_index' => $cholqij_index,
      'nahual' => $this->nahuales[$cholqij_index],
      'g' => $g,
      'luna' => $luna,
      'mayan_haabs' => $mayan_haabs,
      'sistema' => $this->sistema,
      'count' => $count,
      'cargador' => $cargador,
      'mayan_days' => $mayan_days,
      'haab_tun_direccion' => $haab_tun_data['direccion'],
      'haab_tun_icon' => $haab_tun_data['default_icon'],
      'mayan_days_ccc' => $kins,
      'mayan_julian_days' => $mayan_julian_days,
      'kins' => $kins,
      'mayan_days_absolute' => $mayan_days_absolute,
    );
    \Drupal::cache()->set(
      $cache_id,
      $this->results,
      CacheBackendInterface::CACHE_PERMANENT,
      array('calendario_haab_page', 'calendario_haab')
    );
    return $this->results;
  }

  public function getGregorianWeekDay() {
    $do = new CustomDate();
    $gregorian_week_day = $do->get_week_day($this->gregorian_date);
    return $gregorian_week_day;
  }

  public function getGregorianMonthOfYear() {
    $do = new CustomDate();
    $gregorian_month = $do->get_month_of_year($this->gregorian_date);
    return $gregorian_month;
  }

  /**
   * Get Julian Day given mayan long count.
   *
   *
   * @param int $baktun
   * @param int $katun
   * @param int $tun
   * @param int $uinal
   * @param int $kin
   */
  public function MayanCountToJd($baktun, $katun, $tun, $uinal, $kin)
  {
    return $this->mayan_epoch +
           ($baktun * 144000) +
           ($katun  *   7200) +
           ($tun    *    360) +
           ($uinal  *     20) +
           $kin;
  }

  /**
   * Get Ceremonia de cambio de cargador.
   * If it exists. if not it returns false to specify if there is no
   * Year berer Change ceremony.
   *
   */
  function getCCC() {
    if (isset($this->ccc)) {
      return $this->ccc;
    }
    $this->ccc = $ccc = isset($this->sistema['ccc']['duracion'])?
      $this->sistema['ccc']['duracion'] : 0;
    return $ccc;
  }

  /**
   * Return true if the current date is in the baktun rest.
   *
   */
  public function cccEnDescanso($mayanDays=NULL) {
    if ($mayanDays==NULL) {
      $mayanDays = $this->mayanDays();
    }
    $frecuencia_descanso = $this->sistema['ccc']['descanso']['frecuencia'];
    $duracion_descanso = $this->sistema['ccc']['descanso']['duracion'];
    if ($mayanDays > $frecuencia_descanso) {
      $dif_from_descanso = $this->mod($mayanDays, $frecuencia_descanso);
      if ( $dif_from_descanso < $duracion_descanso) {
        return $dif_from_descanso;
      }
      return 0;
    }
    return 0;
  }

  public function mayanDaysSumOfCCC($jd=NULL) {
    $mayanDays = $this->mayanDays($jd);
    if (!($ccc = $this->getCCC())) {
      return $mayanDays;
    }
    $frecuencia_descanso = $this->sistema['ccc']['descanso']['frecuencia'];
    $duracion_descanso = $this->sistema['ccc']['descanso']['duracion'];
    $sum_ccc = floor(($mayanDays - 17 + (365 + $ccc)) / (365 + $ccc)) * $ccc;

    $descansos = floor($mayanDays / $frecuencia_descanso);
    $day_dif = 0;
    if ($diff_from_descanso = $this->cccEnDescanso($mayanDays)) {
      $descansos--;
      $day_dif = floor($diff_from_descanso / 365) * $ccc;
    }
    $ajuste_descansos = $descansos * floor($duracion_descanso/365) * $ccc + $day_dif;

    /*if (false and $this->julian_date==584283.5 or
        $this->julian_date==584284.5 or
        $this->julian_date==584285.5
    ) {
      echo $this->julian_date . "-------------<br />";
      var_dump($sum_ccc);
    }*/
    return $sum_ccc - $ajuste_descansos;
  }

  public function mayanDaysWithOutCCC($jd=NULL) {
    $mayanDays = $this->mayanDays($jd);
    $kines = $mayanDays - $this->mayanDaysSumOfCCC($jd);
    return $kines;
  }

  public function mayanDaysWithCCC($jd=NULL) {
    $mayanDays = $this->mayanDays($jd);
    $kines = $mayanDays + $this->mayanDaysSumOfCCC($jd);
    return $kines;
  }

  public function mayanDays($jd=NULL) {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    $mayanDays = $jd - $this->mayan_epoch;
    return $mayanDays;
  }

  public function mayanHaabs($jd=NULL) {
    $ccc = $this->getCCC();
    if ($ccc==0) {
      $kins = $this->mayanDays($jd);
    } else {
      $kins = $this->getKins();
      //$kins = $this->mayanDaysWithOutCCC($jd);
    }
    $haabs = 0;
    if ($kins>=17) {
      $haabs = floor(($kins-16)/365) + 1;
    }
    return $haabs;
  }

  public function jdToMayanCount($jd=NULL) {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    $d = floor($jd - $this->mayan_epoch + 0.5);
    $baktun = floor($d / 144000);
    if ($baktun<0) $baktun*=(-1);
    $d = $d % 144000;
    $katun = floor($d / 7200);
    if ($katun<0) $katun*=(-1);
    $d = $d % 7200;
    $tun = floor($d / 360);
    if ($tun<0) $tun*=(-1);
    $d =  $d % 360;
    $uinal = floor($d / 20);
    if ($uinal<0) $uinal*=(-1);
    $kin = $d % 20;
    if ($kin<0) $kin*=(-1);
    return [$baktun, $katun, $tun, $uinal, $kin];
  }

  function jdToMayanHaab($jd=NULL) {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    $ccc = $this->getCCC();
    if ($ccc==0) {
      $kins = $this->mayanDays($jd);
    } else {
      list($kins, $data) = $this->kinsBajoCargador($jd);
    }
    $cargador = $this->getCargador($jd);
    $data = $this->nahuales_data[$cargador[1]];
    list($inicio_haab_no, $inicio_haab) = $this->sistema['inicio']['haab'];
    $day = $this->mod(($kins + $inicio_haab_no + ($inicio_haab * 20)), 365);
    return [floor($day / 20) + 1, $day % 20, $data];
  }

  function getKins($jd=NULL) {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    $ccc = $this->getCCC();
    if ($ccc==0) {
      //$jd = floor($jd);
      $kins = $this->mayanDays($jd);
    } else {
      $kins = $this->mayanDaysWithOutCCC($jd);
    }
    return $kins;
  }

  public function kinsBajoCargador($jd=NULL) {
    $kins = $this->getKins($jd);
    $cargador = $this->getCargador($jd);
    $cargador_index = $cargador[1];
    /*if ($this->sistema["nahual0"]=='imox') {
      $cargador_index = ($cargador_index + 10) % 20;
    }*/
    $data = $this->nahuales_data[$cargador_index];

    switch($data['direccion']) {
      case 's':
        $factor_direccional = 0;
      break;
      case 'e':
        $factor_direccional = 0.25;
      break;
      case 'n':
        $factor_direccional = 0.5;
      break;
      case 'o':
        $factor_direccional = 0.75;
      break;
    }
    //$factor_direccional = 0;
    $kins = floor($kins + $factor_direccional);

    $data['factor_direccional'] = $factor_direccional;
    return [
      $kins,
      $data
    ];
  }

  public function isBeforeMayanEpoch($jd=NULL) {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    if ($jd<$this->mayan_epoch) {
      return true;
    }
    return false;
  }

  function isHaab0($jd) {
    if ($jd < ($this->mayan_epoch + 17)) {
      return true;
    }
    return false;
  }

  function getCargador($jd=NULL) {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    $kins = $this->getKins();
    $haabs = $this->mayanHaabs($jd);

    //$ciclo_cargadores_no = $this->getCicloNumerosCargadores();
    $ciclo_cargadores = $this->getCicloCargadores();
    if ($haabs==0) {
      $cargador_no = 8;
      $cargador = 1;
      if ($this->isHaab0($jd)) {
        $cargador_no = 7;
        $cargador = 0;
      }
    } else {
      $cargador_no = $this->mod($haabs + 7, 13);
      $cargador = $this->mod($haabs, 4);
    }
  //  $cargador_no = $ciclo_cargadores_no[$cargador_no_pos];
    $cargador = $ciclo_cargadores[$cargador];

    $data = $this->nahuales_data[$cargador];
    $cargador_index = $cargador;
    /*if ($this->sistema["nahual0"]=='imox') {
      $cargador_index = ($cargador + 10) % 20;
    }*/
    return [$cargador_no, $cargador_index, $this->nahuales[$cargador_index], $data];
  }

  function _getCicloNumerosCargadores() {
    return [
      0 => 1,
      1 => 8,
      2 => 2,
      3 => 9,
      4 => 3,
      5 => 10,
      6 => 4,
      7 => 11,
      8 => 5,
      9 => 12,
      10 => 6,
      11 => 13,
      12 => 7
    ];
  }

  function getCicloCargadores() {
    // Basado en el orden de nahuales que comienza con Batz
    return [
      0 => 1, // e
      1 => 6, // noj
      2 => 11, // ik
      3 => 16, // kej
    ];
  }

  public function getCambioIcon($anterior, $actual) {
    if ($anterior=='s' and $actual=='e') {
      return 'transicion-sur-este.png';
    }
    if ($anterior=='e' and $actual=='n') {
      return 'transicion-este-norte.png';
    }
    if ($anterior=='n' and $actual=='o') {
      return 'transicion-norte-oeste.png';
    }
    if ($anterior=='o' and $actual=='s') {
      return 'transicion-oeste-sur.png';
    }
  }

  function jdToMayanCholqij($jd=NULL)
  {
    if ($jd==NULL) {
      $jd = $this->julian_date;
    }
    $ccc = $this->getCCC();
    if ($ccc==0) {
      $kins = $this->mayanDays($jd);
    } else {
      //$kins = $this->getkins($jd);
      list($kins, $data) = $this->kinsBajoCargador($jd);
    }
    //$kins = floor($kins);
    $inicio_cholqij_no = $this->sistema['inicio']['cholqij'][0];
    $inicio_cholqij = $this->sistema['inicio']['cholqij'][1];
    //$lcount = floor($jd - $this->mayan_epoch);
    //$lcount = floor($jd - $this->mayan_epoch+0.5);
    $nahual = $this->amod($kins + $inicio_cholqij, 20);
    $nahual = ($nahual==20)? 0 : $nahual;
    /*if ($this->sistema["nahual0"]=='batz') {
      $nahual = ($nahual + 10) % 20;
    }*/
    return [
      $nahual,
      $this->amod($kins + $inicio_cholqij_no, 13)
    ];
  }

  public function getG($jd=NULL) {
    $ccc = $this->getCCC();
    if ($ccc==0) {
      $kins = $this->mayanDays($jd);
    } else {
      $kins = $this->getKins($jd);
    }
    //$jd = $this->julian_date;
    //$lcount = $jd - $this->mayan_epoch;
    $g_inic = $this->sistema['g']['inicio'];
    $g = ($kins + $g_inic) % 9;
    return $g+1;
  }

  public function getpop0Day() {
    $conv = new ConvertidorFechas($this->yyyy, $this->sistema_id);
    list($haab_index, $haab_no) = $conv->jdToMayanHaab();
    $diff_topop_haab_sin_wayeb = 0;
    $dias_wayeb = 5;
    $diff_top_no = 0;
    if ($haab_index<18) {
      $diff_topop_haab_sin_wayeb = 18 - $haab_index;
      $diff_top_no = 19 - $haab_no + 1;
    } else if ($haab_index==18) {
      $diff_top_no = 19 - $haab_no + 1;
    } else {
      $dias_wayeb = 5 - $haab_no;
    }
    $numero_dia_pop0 = ($diff_topop_haab_sin_wayeb * 20)
      + $dias_wayeb + $diff_top_no;
    return $numero_dia_pop0;
  }

  public function getNumeroDiaPop0() {
    return $this->numero_dia_pop0;
  }

  function lunaTitle($fase_lunar=NULL) {
    if ($fase_lunar==NULL) {
      $fase_lunar = $this->faseLunar();
    }
    $descripcion = '';
    if ($fase_lunar<14) {
      $descripcion = 'Luna Llena';
    } else
    if ($fase_lunar==1) {
      $descripcion = 'Luna Nueva';
    } else
    if ($fase_lunar==22) {
      if ($fase_lunar_por==22) {
        $descripcion = 'Cuarto Menguante';
      } else
      if ($fase_lunar_por) {
        $descripcion = 'Menguante Gibosa';
      } else {
        $descripcion = 'Menguante Cóncava';
      }
    } else
    if ($fase_lunar_por>50) {
      if ($fase_lunar_por>(75-2.69) and $fase_lunar_por<(75+2.69)) {
        $descripcion = 'Cuarto Creciente';
      } else
      if ($fase_lunar_por<75) {
        $descripcion = 'Creciente Cóncava';
      } else {
        $descripcion = 'Creciente Gibosa';
      }
    }
    return $descripcion;
  }

  function faseLunar() {
    $fases = new FasesLuna();
    $luna = $fases->getFase($this->yyyy, $this->dd, $this->mm);
    return $luna;
  }
}
