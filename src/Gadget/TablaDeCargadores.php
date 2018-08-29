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
class TablaDeCargadores {

  /**
   * Constructor.
   */
  public function __construct($sistema = NULL) {
    $this->sistema = $sistema;
  }

  /**
   * Build year Bearer table.
   *
   */
  public function build() {
    $cache_id = 'losnahuales_cargadores_' . '_' . $this->sistema;
    if (false and $cargadores = \Drupal::cache()->get($cache_id)) {
      return $cargadores->data;
    }
    $cargadores = $this->createCargadoresTable();
    \Drupal::cache()->set(
      $cache_id,
      $cargadores,
      CacheBackendInterface::CACHE_PERMANENT,
      array('calendario_haab_page', 'calendario_haab')
    );
    return $cargadores;
  }

  /**
   * Build the actual cargadores table.
   *
   */
  public function createCargadoresTable() {
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
      $nahual_img = $u->getStyledImage($nahual_data, 'field_glifos', 'glifos_calculador', '');
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
        'field_glifos', 'glifos_calculador', '');
      $haab_name = $haab_data->title->getValue()[0]['value'];
      $haab_values[$index] = [
        'node' => $haab_data,
        'name' => $haab_name,
        'glifo' => $haab_img
      ];
    }

    $headers_cargadores = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['glifos-haab'],
      ],
    ];
    $convertidor= new ConvertidorFechas(NULL, $this->sistema);
    // Construyendo los header con los cargadores.
    $ciclo_cargadores = $convertidor->getCicloCargadores();
    $sistema = $convertidor->getSistema();
    for($index = 0; $index<4; $index++) {
      $cargador_index = ($index + 1) % 4;
      $cargador = $ciclo_cargadores[$cargador_index];
      if($sistema["nahual0"]=='imox') {
        $cargador = ($cargador + 10) % 20;
      }
      $cargador_glifo = $nahuales_values[$cargador]['glifo'];
      $cargador_name = $nahuales_values[$cargador]['name'];

      $headers_cargadores['header-columna-' . $cargador_name] =
        $u->createContainer(
          array( $cargador_glifo, $cargador_name ),
          'header-columna-cargador cargador-' . $cargador_name
      );
    }
    $listado_data_cargadores = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['listado-cargadores'],
      ],
    ];


    list($yyyy, $mm, $dd) = $convertidor->getInicioGregoriano();
    // Agregar los idas del inicio del tiempo
    $anno1 = $yyyy;
    while($yyyy < 2100) {
      $convertidor->setDate(($yyyy) . '/1/1');
      //$cargador = $convertidor->getCargador();
      if ($yyyy==$anno1) {
        $pop0Days = 17;
      } else {
        $pop0Days = $convertidor->getpop0Day();
      }
      $convertidor->addDays($pop0Days);
      $data = $convertidor->getAll();

      $cargador = $convertidor->getCargador();
      $pop0_gregorian = $convertidor->getGregorian();
      $pop0_julian = $convertidor->getJulian();
      $cargador_nahual = $cargador[2];
      $cargador_no = $cargador[0];
      $cargador_nahual_index = $cargador[1];
      $cargador_data = $cargador[3];

      if($data["sistema"]["nahual0"]=='imox') {
        $cargador_nahual_index = ($cargador[1] + 10) % 20;
      }

      $cargador_glifo = $nahuales_values[$cargador_nahual_index]['glifo'];
      $cargador_name = $nahuales_values[$cargador_nahual_index]['name'];
      $cargador_no_img = $numeros_values[$cargador_no]['glifo_rotado'];
      $cholqij_img = $nahuales_values[$cholqij_item_index]['glifo'];
      $cholqij_no_img = $numeros_values[$datos_fecha['cholqij_no']]['glifo'];
      $cholqij_no_img_rotado = $numeros_values[$datos_fecha['cholqij_no']]['glifo_rotado'];
      $cholqij_no_name = $numeros_values[$datos_fecha['cholqij_no']]['name'];
      $cholqij_no_img_rotado = $numeros_values[$datos_fecha['cholqij_no']]['glifo_rotado'];
      $haab_img = $haab_values[$datos_fecha['haab_index']]['glifo'];
      $haab_no_img = $numeros_values[$datos_fecha['haab_no']]['glifo_rotado'];
      $month_of_year = $convertidor->getGregorianMonthOfYear();
      $fecha_greg = $u->createContainer(
        array( $week_day['dim'] . ' ' . $dd . ' ' .
          $month_of_year['dim']),
          'gregorian'
      );
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
            . '<div class="direccion-icon-calculador img-container">'
            . '<img class="img-responsive" src="' . $GLOBALS['base_path']
            . 'themes/nahuales/images/' . $cargador[3]['default_icon']
            . '" /></div>';
        }
      }
      $info_text = '<div class="text"><small><div class="cholqij">'
        . $datos_fecha['cholqij_no'] . ' '
        . $datos_fecha['nahual'] . '</div><div class="haab">'
        . $datos_fecha['haab_no'] . ' ' . $datos_fecha['haab_name']
        . '</div><div class="cuenta-larga">' . $cuenta_larga_numeros
        . ' - G' . $datos_fecha['g']
        . '</div><div class="direccion-info">'
        . $direccion
        . '</div>'
        . '<div class="julian">' . $datos_fecha['julian']  . '</div>'
        . '<div class="haab-kins"><label>Days:</label>'
        . $datos_fecha['mayan_days_absolute'] . '- <label>Kins:</label>'
        . $datos_fecha['kins'] . '</div>'
        . '</small></div>';
      $cholqij_no_img = $u->createContainer(
        array( $cholqij_no_img ),
        'energia'
      );
      $info = $u->createContainer(
        array( $cholqij_no_img_rotado, $cholqij_img, $haab_no_img, $haab_img, $info_text ),
        'info'
      );
      $content_cargador = $u->createContainer(
        array($titulo, $cholqij_no_img, $fecha_greg, $info),
        'dia populated dia-'.$year_day
      );

      $listado_data_cargadores[] = $content_cargador;

      $yyyy++;
    }


    $tabla_cargadores = $u->createContainer(
      [$headers_cargadores,
      $listado_data_cargadores],
      'tabla-cargadores-wrapper'
    );
    return $tabla_cargadores;
  }

}
