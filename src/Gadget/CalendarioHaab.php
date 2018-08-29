<?php

namespace Drupal\losnahuales\Gadget;

use Drupal\Core\Url;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\losnahuales\Service\ConvertidorFechas;
use Drupal\losnahuales\Service\CustomUtils;
use Drupal\losnahuales\Service\CustomDate;

/**
 * Generar calendario haab
 *
 */
class CalendarioHaab {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * Build haab calendar.
   *
   */
  public function dataBuild(&$variables) {
    $anno = $variables['anno'];
    $sistema = $variables['sistema'];
    $comienzo = $variables['comienzo'];
    // $calendario = new CalendarioHaab($anno, $sistema, $comienzo);
    //$tabla = $this->build();

    $cache_id = 'losnahuales_calendario_haab_'
      . str_replace('-', 'bc_', $anno)
      . '_' . $sistema . '_' . $comienzo;
    if ( $calendario = \Drupal::cache()->get($cache_id)) {
      return $calendario->data;
    }
    $variables['tabla_calendario'] = $this->createCalendarioHaabPage($anno, $sistema, $comienzo);
    \Drupal::cache()->set(
      $cache_id,
      $variables,
      CacheBackendInterface::CACHE_PERMANENT,
      array('calendario_haab_page', 'calendario_haab')
    );
    return $variables;
  }

  /**
   * Returns Titles for special days in calendar.
   *
   * @param array $datos_fecha
   *   Data return by a call to convertidorFechas::getAll()
   * @param $convertidor
   *   convertidorFecha object
   */
  function titulos_de_dias($datos_fecha, $convertidor) {
    list($yyyy, $mm, $dd) = explode('/', $datos_fecha['fecha_gregoriana']);
    $batz = 0;
    if($datos_fecha["sistema"]["nahual0"]=='imox') {
      $batz = 10;
    }
    $month_of_year = $convertidor->getGregorianMonthOfYear();
    $titulo = $titulo_greg = '&nbsp;';
    $class = 'titulo titulo-maya ';
    $greg_class = 'titulo titulo-greg ';
    if ($datos_fecha['cholqij_index']==19 && $datos_fecha['cholqij_no']==4
        && $datos_fecha['haab_index']==17 && $datos_fecha['haab_no']==8) {
      $class.= 'titulo mayan-begining';
      $titulo = t("Begining");
    } else if ($datos_fecha['haab_index']==0 && $datos_fecha['haab_no']==0) {
      /* Anno nuevo haab Pop 0 */
      $class.= 'titulo haab-tun';
      $titulo = t("Haab' Tun");
    } else if ($datos_fecha['cholqij_index']==$batz && $datos_fecha['cholqij_no']==8) {
      $class.= 'titulo waqxakib-batz';
      $titulo = t("Waqxakib’ B’atz");
    } else if ($datos_fecha['haab_index']==18) {
      if ($datos_fecha['haab_no']==0) {
        $class.= 'titulo wayeb';
        $titulo = 'Wayeb';
      } else {
        $class.= 'titulo wayeb empty';
      }
    } else {
      $class.= 'titulo empty';
    }
    if ($datos_fecha['fecha_gregoriana']==date('Y/m/d')) {
      $greg_class.= 'titulo hoy';
      $titulo_greg = t("Hoy");
    } else if ($dd==1) {
      $titulo_greg = $month_of_year['name'];
      $greg_class.= 'titulo mes';
      if ($mm==1) {
        $titulo_greg .= ' ' . $yyyy;
      }
    } else if ($datos_fecha['cholqij_index']==19 && $datos_fecha['cholqij_no']==4
        && $datos_fecha['haab_index']==17 && $datos_fecha['haab_no']==8) {
      $greg_class.= 'titulo mayan-epoch';
      $titulo_greg = t("Mayan&nbsp;Epoch");
    } else {
      $greg_class.= 'titulo empty';
    }
    return [$titulo, $class, $titulo_greg, $greg_class];
  }

  /**
   * Build the actual calendar table.
   *
   */
  public function createCalendarioHaabPage($anno, $sistema, $comienzo) {
    $do = new CustomDate();
    $u = new CustomUtils();
    $queue_numeros = \Drupal::entityManager()->getStorage('entity_subqueue')->load('numeros');
    $lista_numeros = $queue_numeros->get('items')->getValue();
    $numeros_values = [];
    foreach($lista_numeros as $index=>$numero_item) {
      $numero_nid = $numero_item['target_id'];
      $numero_data = \Drupal::entityManager()->getStorage('node')->load($numero_nid);
      $numero_img = $u->getStyledImage($numero_data, 'field_glifos', 'glifos_calculador', '');
      $numero_img_rotado = $u->getStyledImage($numero_data, 'field_glifos', 'numeros_rotados', 'numero numero-rotado');
      $numero_name = $numero_data->title->getValue()[0]['value'];
      $numeros_values[$index] = [
        'node' => $numero_data,
        'name' => $numero_data->title->getValue()[0]['value'],
        'glifo' => $numero_img,
        'glifo_rotado' => $numero_img_rotado
      ];
    }
    $queue_nahuales = \Drupal::entityManager()->getStorage('entity_subqueue')->load('nahuales');
    $lista_nahuales = $queue_nahuales->get('items')->getValue();
    $nahuales_values = [];
    foreach($lista_nahuales as $index=>$nahual) {
      $nahual_nid = $nahual['target_id'];
      $nahual_data = \Drupal::entityManager()->getStorage('node')->load($nahual_nid);
      $nahual_img = $u->getStyledImage($nahual_data, 'field_glifos', 'glifos_calculador', 'glifo');
      $nahual_name = $nahual_data->title->getValue()[0]['value'];
      $nahuales_values[$index] = [
        'node' => $nahual_data,
        'name' => $nahual_data->title->getValue()[0]['value'],
        'glifo' => $nahual_img
      ];
    }
    $queue_haab = \Drupal::entityManager()->getStorage('entity_subqueue')
      ->load('haab');
    $lista_haab = $queue_haab->get('items')->getValue();
    $haab_values = [];
    foreach($lista_haab as $index=>$haab_item) {
      $haab_nid = $haab_item['target_id'];
      $haab_data = \Drupal::entityManager()->getStorage('node')
        ->load($haab_nid);
      $haab_img = $u->getStyledImage($haab_data,
        'field_glifos', 'glifos_calculador', 'glifo');
      $haab_name = $haab_data->title->getValue()[0]['value'];
      $haab_values[$index] = [
        'node' => $haab_data,
        'name' => $haab_data->title->getValue()[0]['value'],
        'glifo' => $haab_img
      ];
    }
    /*$queue_senores = \Drupal::entityManager()->getStorage('entity_subqueue')
      ->load('senores_de_la_noche');
    $lista_senores = $queue_senores->get('items')->getValue();
    $queue_lunas = \Drupal::entityManager()->getStorage('entity_subqueue')
      ->load('fases_de_la_luna');
    $lista_lunas = $queue_lunas->get('items')->getValue();*/

    $glifos_haab = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['glifos-haab'],
      ],
    ];
    $glifos_nahuales = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['glifos-nahuales'],
      ],
    ];
    $tabla_haab = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['tabla-haab'],
      ],
    ];
    $convertidor_pop0 = new ConvertidorFechas($anno, $sistema);
    $cargador_anterior = $convertidor_pop0->getCargador();
    $pop0Day = $convertidor_pop0->getpop0Day();
    $convertidor_pop0->addDays($pop0Day);
    $cargador = $convertidor_pop0->getCargador();
    $pop0_gregorian = $convertidor_pop0->getGregorian();
    $pop0_julian = $convertidor_pop0->getJulian();
    $cargador_nahual = $cargador[2];
    $cargador_no = $cargador[0];
    $cargador_nahual_index = $cargador[1];
    $data = $convertidor_pop0->getAll();
    if($data["sistema"]["nahual0"]=='imox') {
      $cargador_nahual_index = ($cargador[1] + 10) % 20;
    }

    $cargador_data = $cargador[3];
    $cargador_anterior_nahual = $cargador_anterior[2];
    $cargador_anterior_no = $cargador_anterior[0];
    $cargador_anterior_nahual_index = $cargador_anterior[1];
    if ($data["sistema"]["nahual0"]=='imox') {
      $cargador_anterior_nahual_index = ($cargador_anterior[1] + 10) % 20;
    }
    $cargador_anterior_data = $cargador_anterior[3];
    //obtener cargador del año actual
    $cargador_glifo = $nahuales_values[$cargador_nahual_index]['glifo'];
    $cargador_name = $nahuales_values[$cargador_nahual_index]['name'];
    $cargador_no_img = $numeros_values[$cargador_no]['glifo_rotado'];
    // obtener cargador anterior
    $cargador_anterior_glifo = $nahuales_values[$cargador_anterior_nahual_index]['glifo'];
    $cargador_anterior_name = $nahuales_values[$cargador_anterior_nahual_index]['name'];
    $cargador_anterior_no_img = $numeros_values[$cargador_anterior_no]['glifo_rotado'];
    $glifos_haab['introductorio'] = $u->createContainer(
      [ '<div class="text titulo">Cargador del A&ntilde;o</div>',
        $cargador_no_img,
        $cargador_glifo,
        '<div class="text cargador">' . $cargador_no . ' ' . $cargador_name . '</div>',
      ],
      'glifo-introductorio'
    );
    $nahual_index = $cargador_nahual_index;
    for($ii=0; $ii<20; $ii++) {
      $nahual_img = $nahuales_values[$nahual_index]['glifo'];
      $nahual_name = $nahuales_values[$nahual_index]['name'];
      $nahual_name_container = $u->createContainer(
        array( '<h3>'.$nahual_name.'</h3>' ),
        'title'
      );
      $glifos_nahuales['nahual_glifos_'.$nahual_name] = $u->createContainer(
        array( $nahual_img, $nahual_name_container ),
        'glifo-nahual glifo-' . str_replace("'", '', $nahual_name)
      );
      if (++$nahual_index>19) {
        $nahual_index = 0;
      }
    }
    $convertidor = new ConvertidorFechas($anno, $sistema);
    switch ($comienzo) {
      case 1: /* Comienzo en 0 pop */
        $convertidor->addDays($pop0Day);
      break;
      case 2:
        $convertidor->addDays($pop0Day - 20);
      break;
    }
    /* Get the first day */
    $datos_fecha = $convertidor->getAll();
    $sistema_info = $convertidor->getSistema();
    $nota = 'Segun el sistema ' . $sistema_info['name'];
    /* Comienzo el dia 1 de Enero */
    if ($comienzo==0 or $comienzo==2) {
      $cholqij_index = $datos_fecha['cholqij_index'];
      if($datos_fecha["sistema"]["nahual0"]=='imox') {
        $cholqij_index = ($cholqij_index + 10) % 20;
      }
      /* El numero de dias en blanco sera igual a la resta de
         la posicion del cholkij el primer dia 1 de enero
         menos(-) el nahual cargador que es el primero de la fila
          top de nahuales
      */
      $empty_days = $cholqij_index - $cargador_nahual_index;
      if ($empty_days<0) {
        $empty_days = 20 + $empty_days;
      }
      // Generar esa cantidad de dias en blanco en el calendario
      for($ii=0; $ii<$empty_days; $ii++) {
        $tabla_haab['dia_empty_' . $ii] = $u->createContainer(
          array('&nbsp;'),
          'dia empty'
        );
      }
    } else if ($comienzo==2) {
      /* Add spaces at the begining if we are in the begining of time */
    }
    /* Building Haab simbols column */
    $haab_index = $datos_fecha['haab_index'];
    if ($empty_days>0 and $haab_index<$empty_days) {
      $haab_index = $haab_index-1;
      if ($haab_index<0) {
        $haab_index = 19;
      }
    }
    $no_uinal_pop0 = floor(($pop0Day + 20) / 20);
    for($ii=0; $ii<19+$no_uinal_pop0; $ii++) {
      $haab_img = $haab_values[$haab_index]['glifo'];
      $haab_name = $haab_values[$haab_index]['name'];
      $haab_name_container = $u->createContainer(
        array( '<h3>'.$haab_name.'</h3>' ),
        'title'
      );
      $cambio_cargador = '';
      if ($haab_name=='Pop' and $convertidor->getCCC()) {
        $direccion_anterior = $cargador_anterior_data['direccion'];
        $direccion_actual = $cargador_data['direccion'];
        $cambio_cargador = $this->ritualCambioCargador(
          $u,
          $convertidor,
            $direccion_anterior,
            $direccion_actual,
            $cargador_anterior_name,
            $cargador_anterior_no,
            $cargador_anterior_no_img,
            $cargador_anterior_glifo,
            $cargador_no_img,
            $cargador_glifo,
            $cargador_no,
            $cargador_name
        );
      }
      $glifos_haab['haab_glifos_'.$haab_name.'_'.$ii] = $u->createContainer(
        array( $haab_img, $haab_name_container, $cambio_cargador ),
        'glifo-haab glifo-' . str_replace("'", '', $haab_name)
      );
      if (++$haab_index>18) {
        $haab_index = 0;
      }
    }
    // Build calendar days.
    for($year_day=0; $year_day<(365+$pop0Day+1); $year_day++) {
      $fecha_gregoriana = $datos_fecha['gregoriana'];
      list($yyyy, $mm, $dd) = explode('/', $fecha_gregoriana);

      $datos_fecha['fecha_gregoriana'] = $fecha_gregoriana;
      list($titulo, $class, $titulo_greg, $greg_class) = $this->titulos_de_dias($datos_fecha, $convertidor);
      $titulo = $u->createContainer(
        array( $titulo ),
        $class
      );
      $titulo_greg = $u->createContainer(
        array( $titulo_greg ),
        $greg_class
      );
      $cholqij_index = $datos_fecha['cholqij_index'];
      $cholqij_item_index = $cholqij_index;
      // If current system is imox, Reconvert to batz system to work
      // as index of $nahuales_values
      if($datos_fecha["sistema"]["nahual0"]=='imox') {
        $cholqij_item_index = ($cholqij_index + 10) % 20;
      }
      $cholqij_img = $nahuales_values[$cholqij_item_index]['glifo'];
      $cholqij_no_img = $numeros_values[$datos_fecha['cholqij_no']]['glifo'];
      $cholqij_no_img_rotado = $numeros_values[$datos_fecha['cholqij_no']]['glifo_rotado'];
      $cholqij_no_name = $numeros_values[$datos_fecha['cholqij_no']]['name'];
      $cholqij_no_img_rotado = $numeros_values[$datos_fecha['cholqij_no']]['glifo_rotado'];
      $haab_img = $haab_values[$datos_fecha['haab_index']]['glifo'];
      $haab_no_img = $numeros_values[$datos_fecha['haab_no']]['glifo_rotado'];

      $baktun = $datos_fecha['baktun'];
      $katun = $datos_fecha['katun'];
      $tun = $datos_fecha['tun'];
      $uinal = $datos_fecha['uinal'];
      $kin = $datos_fecha['kin'];
      $cuenta_larga_numeros = $baktun . '.' . $katun . '.' . $tun . '.'
        . $uinal . '.' . $kin;
      $direccion = '';
      if ($convertidor->getCCC()) {
        if (!$convertidor->cccEnDescanso()) {
          $cargador = $convertidor->getCargador();
          $direccion = '<span class="direccion-letra">&nbsp;' . $cargador[3]['direccion'] . '</span>'
            . '<div class="direccion-icon img-container">'
            . '<img class="img-responsive" src="' . $GLOBALS['base_path']
            . 'themes/nahuales/images/' . $cargador[3]['default_icon']
            . '" /></div>';
        }
      }

      $cholqij_data = $u->createContainer([
          $cholqij_no_img_rotado,
          $cholqij_img,
          '<div class="cholqij-txt">' . $datos_fecha['cholqij_no'] . ' '
            . $datos_fecha['nahual'] . '</div>',
        ],
        'cholqij-info'
      );

      $haab_data = $u->createContainer([
          $haab_no_img,
          $haab_img,
          $datos_fecha['haab_no'] . ' ' . $datos_fecha['haab_name']
        ],
        'haab-info'
      );

      $resto_fecha = '<div class="cuenta-larga">' . $cuenta_larga_numeros
        . '-G' . $datos_fecha['g']
        . '</div>';

      $direccion = '<div class="direccion-info">'
        . $direccion
        . '</div>';

      $kins = '<div class="kins"><label>Kins:</label>'
        . $datos_fecha['kins'] . '</div>';
      $days = '<div class="days"><label>Days:</label>'
        . $datos_fecha['mayan_days_absolute'] . '</div>';
      $cholqij_no_img = $u->createContainer(
          array( $cholqij_no_img ),
          'energia'
      );
      $info_mayan_classes = 'info-mayan direccion-' . $cargador[3]['direccion'];
      if ($convertidor->getCCC()) {
        $info_mayan_classes .= ' offset';
      }
      $info_mayan = $u->createContainer(
        [ $cholqij_data, $haab_data, $resto_fecha, $direccion, $kins, $days
          , $titulo ], $info_mayan_classes
      );
      $week_day = $convertidor->getGregorianWeekDay();
      $month_of_year = $convertidor->getGregorianMonthOfYear();
      $fecha_greg = $u->createContainer(
          [$week_day['dim'] . ' ' . $dd . ' ' .
          $month_of_year['dim']],
          'gregorian'
      );
      $fecha_jul = $u->createContainer(
          [$datos_fecha['julian'],],
          'julian'
      );
      $ruler = $u->createContainer(
          '&nbsp;',
          'ruler'
      );
      $info_gregorian = $u->createContainer([
        $titulo_greg,
        $fecha_greg,
        $fecha_jul,
        $ruler,
        ], 'info-gregorian'
      );

      $content_dia = $u->createContainer(
        [$info_gregorian, $info_mayan],
        'dia populated dia-'.$year_day
      );
      $tabla_haab['dia_'.$year_day] = [
        '#title' => $content_dia,
        '#type' => 'link',
        '#url' => Url::fromRoute(
          'losnahuales.losnahuales_calcular_fecha',
          ['yyyy'=>$yyyy,'mm'=>$mm,
            'dd'=>$dd, 'sistema' =>$sistema]
        ),
      ];
      $convertidor->addDays(1);
      $datos_fecha = $convertidor->getAll();

    }
    $tabla_container_derecho = $u->createContainer(
      [$glifos_nahuales, $tabla_haab],
      'tabla-right-container'
    );
    $calendar =  $u->createContainer(
      array( $glifos_haab, $tabla_container_derecho, ),
      'tabla-haab-wrapper'
    );
    return  $u->createContainer(
      [ $nota, $calendar ],
      'haab-calendar-wrapper'
    );
  }

  function ritualCambioCargador(
    $u,
    $convertidor,
    $direccion_anterior,
    $direccion_actual,
    $cargador_anterior_name,
    $cargador_anterior_no,
    $cargador_anterior_no_img,
    $cargador_anterior_glifo,
    $cargador_no_img,
    $cargador_glifo,
    $cargador_no,
    $cargador_name) {

    $week_day = $convertidor->getGregorianWeekDay();
    $month_of_year = $convertidor->getGregorianMonthOfYear();
    /*$fecha_greg = $u->createContainer(
      [ $week_day['dim'] . ' ' . $dd . ' ' . $month_of_year['dim'] ],
      'gregorian'
    );*/
    $cambio_icon =
      $convertidor->getCambioIcon($direccion_anterior, $direccion_actual);
    $inicio_dia = '';
    switch($direccion_actual) {
      case 's':
        $inicio_dia = "0am.";
      break;
      case 'e':
        $inicio_dia = "6am.";
      break;
      case 'n':
        $inicio_dia = "12pm.";
      break;
      case 'o':
        $inicio_dia = "6pm.";
      break;
    }
    $cargador_anterior = $u->createContainer(
      [
        $cargador_anterior_no_img,
        $cargador_anterior_glifo,
        '<div class="text"><small>' . $cargador_anterior_no . ' '
        . $cargador_anterior_name . '</small></div>',
        '<div class="arrow-change"><small>'
        . '<i class="fa fa-arrow-down" aria-hidden="true"></i></small></div>',
      ],
      'glifo-anterior'
    );
    $cargador_info = $u->createContainer(
      [ $cargador_no_img,
        $cargador_glifo,
        '<div class="text"><small>' . $cargador_no . ' '
        . $cargador_name . '</small></div>',
      ],
      'glifo-nuevo'
    );
    $direccion = '<div class="cambio-direccion">'
      . '<div class="img-container">'
      . '<img class="img-responsive" src="' . $GLOBALS['base_path']
      . 'themes/nahuales/images/' . $cambio_icon
      . '" /></div><small>'
      . $direccion_anterior . '-' . $direccion_actual . '</small>'
      . '</div>';
    $cambio_cargador = $u->createContainer(
      [ $cargador_anterior,
        $cargador_info,
        $direccion,
        '<small><strong>Inicio kin: ' . $inicio_dia . '</strong></small>',
      ],
      'cambio_cargador'
    );
    return $cambio_cargador;
  }

}
