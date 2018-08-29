<?php

namespace Drupal\losnahuales\Gadget;

use Drupal\Core\Url;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\losnahuales\Service\ConvertidorFechas;
use Drupal\losnahuales\Service\CustomUtils;
use Drupal\losnahuales\Service\CustomMetatags;

/**
 * Generar Resultados calculo fecha maya
 *
 */
class ResultadoCalculo {

  /**
   * Constructor.
   */
  public function __construct() {}

  public function dataBuild(&$variables) {
    $fecha = $variables['fecha'];
    $sistema = $variables['sistema'];
    $set_metatags = $variables['set_metatags'];
    $cache_id = 'losnahuales_calculo_result_page_preprocess_variables_'
      . str_replace('/', '_', $fecha)
      . '_' . $sistema;
    if (false and $cache = \Drupal::cache()->get($cache_id)) {

      $this->data = $cache->data;
      return $cache->data;
    }
    $data = $this->build($fecha, $sistema);
    $variables = array_merge($variables, $data);

    \Drupal::cache()->set(
      $cache_id,
      $variables,
      CacheBackendInterface::CACHE_PERMANENT,
      array('resultado_calculo_page_preprocess_variables')
    );
    return $variables;
  }

  public function build($fecha, $sistema) {
  	$convertidor = new ConvertidorFechas($fecha, $sistema);
    $datos_fecha = $this->data = $convertidor->getAll();
    list( $julian, $gregoriana, $baktun, $katun, $tun, $uinal,
      $kin, $haab_no, $haab, $haab_index, $haab_name, $cholqij_no, $cholqij,
      $cholqij_index, $nahual, $g, $luna, $mayan_haabs, $sistema, $count,
      $cargador, $mayan_days)
        = array_values($datos_fecha);
    $data = [];
    $data['count'] = $count;
    $data["cholqij_no"] = $cholqij_no;
    $data["cholqij"] = $cholqij;
    $data["haab_no"] = $haab_no;
    $data["haab"] = $haab;

    /*if($datos_fecha ["sistema"] ["nahual0"]=='imox') {
      $cholqij_index = ($cholqij_index + 10) % 20;
    }*/
    $u = new CustomUtils();
    list($intro_nid, $kin_nid, $uinal_nid, $tun_nid, $katun_nid, $baktun_nid,
      $piktun_nid) = $u->getQueueList('cuenta_larga');
    $lista_numeros = $u->getQueueList('numeros');
    $lista_nahuales = $u->getQueueList('nahuales');
    $lista_haab = $u->getQueueList('haab');
    $lista_senores = $u->getQueueList('senores_de_la_noche');
    $lista_lunas = $u->getQueueList('fases_de_la_luna');

    /* Introductory Glyph */
    $glifo_img = $u->getStyledImage($intro_nid, 'field_glifos', 'glifo_introductorio', 'glifo_introductorio');
    $glifo_introductorio = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'img-container glifo-introductorio',
      ],
      'img' => $glifo_img,
    ];
    $data['intro'] = $glifo_introductorio;
    /* Helper Function to reduce code */
    /* Builds a pair of glyphs of the long count **/
    function getEstelaPair($item_nid, $no_nid, $title, $u) {
      $no_img = $u->getStyledImage($no_nid, 'field_glifos', 'numeros_rotados', 'number');
      $item_img = $u->getStyledImage($item_nid, 'field_glifos', 'glifos_calculador', 'glifo');
      $title = $u->createContainer($title, 'data-title');
      return $u->createContainer(array($no_img, $item_img, $title), 'dato-calendario');
    }
    /* Baktun Glyphs */
    $data['baktun'] = getEstelaPair(
      $baktun_nid, $lista_numeros[$baktun], $baktun.' Baktun', $u
    );
    /* Katun Glyphs */
    $data['katun'] = getEstelaPair(
      $katun_nid, $lista_numeros[$katun], $katun.' Katun', $u
    );
    /* Tun Glyphs */
    $data['tun'] = getEstelaPair(
      $tun_nid, $lista_numeros[$tun], $tun.' Tun', $u
    );
    /* Uinal Glyphs */
    $data['uinal'] = getEstelaPair(
      $uinal_nid, $lista_numeros[$uinal], $uinal.' Uinal', $u
    );
    /* Kin Glyphs */
    $data['kin'] = getEstelaPair(
      $kin_nid, $lista_numeros[$kin], $kin.' Kin', $u
    );
    /* Glyphs & data of the Cholqij */
    $nahual_nid = ($lista_nahuales[$cholqij_index] + 10) % 20;
    $cholqij_no_img_nid = $lista_numeros[$cholqij_no];
    $nahual_node = \Drupal::entityManager()->getStorage('node')->load($nahual_nid);
    $data['nahual_img'] = $u->getStyledImage($nahual_node, 'field_glifos', 'glifos_calculador', 'glifo glifos');
    $nahual_img_original = $u->getStyledImage($nahual_node, 'field_glifos', 'original', 'glifo glifos');
    $data['nahual_img_second'] = $u->getStyledImage($nahual_node, 'field_glifos', 'original', 'glifos fright', 1);
    $this->data['nahual_img'] = $nahual_img_original;
    $cholqij_no_data = \Drupal::entityManager()->getStorage('node')->load($cholqij_no_img_nid);
    $data['cholqij_no_img'] = $u->getStyledImage($cholqij_no_data, 'field_glifos', 'numeros_rotados', 'number');
    $data['cholqij_no_img_normal'] = $u->getStyledImage($cholqij_no_data, 'field_glifos', 'numero_normal', '');
    $data['cholqij_no_title'] = $cholqij_no_data->title->getValue()[0]['value'];
    $data['cholqij_no_description'] = $u->createContainer(array(
      $cholqij_no_data->body->getValue()[0]['value']
    ));
    $data['cholqij_no_name'] = $cholqij_no_data->field_nombre->getValue()[0]['value'];
    $cholqij_data = $u->createContainer($cholqij_no . ' ' . $nahual, 'data-title');
    $data['cholqij'] = $u->createContainer(
      array($data['cholqij_no_img'], $data['nahual_img'], $cholqij_data),
      'dato-calendario'
    );
    $data['nahual_body'] = $u->createContainer(
      $nahual_node->body->getValue()[0]['value']
    );
    $data['nahual_persona'] = $u->createContainer(
      $nahual_node->field_la_persona->getValue()[0]['value']
    );
    $data['nahual_resumen'] = $u->createContainer(
      $nahual_node->field_resumen->getValue()[0]['value']
    );
    $data['nahual_atributos'] = $u->createContainer(
      $nahual_node->field_atributos->getValue()[0]['value']
    );
    $descripcion_corta = $nahual_node->field_descripcion_corta->getValue()[0]['value'];
    $data['nahual_descripcion_corta'] = $u->createContainer(
      $descripcion_corta
    );
    $data['nahual_cruz_description'] = $u->createContainer(
      $nahual_node->field_informacion_de_la_cruz_may->getValue()[0]['value']
    );
    /* Glifos Haab */
    $haab_img_nid = $lista_haab[$haab_index];
    $haab_no_img_nid = $lista_numeros[$haab_no];
    $haab_img = $u->getStyledImage($haab_img_nid, 'field_glifos', 'glifos_calculador_forced', 'glifo');
    $haab_no_img = $u->getStyledImage($haab_no_img_nid, 'field_glifos', 'numeros_rotados', 'number');
    $haab_data = $u->createContainer($haab_no . ' ' . $haab_name, 'data-title');
    $data['haab'] = $u->createContainer(
      array($haab_no_img, $haab_img, $haab_data),
      'dato-calendario'
    );
    /* Glifos Senor de la noche */
    $g_img_nid = $lista_senores[$g-1];
    $g_img = $u->getStyledImage($g_img_nid, 'field_glifos', 'glifos_calculador_forced', 'glifo');
    $g_data = array(
      '#type' => 'markup',
      '#markup' => 'G' . $g,
      '#prefix' => '<div class="data-title">',
      '#suffix' => '</div>',
      );
    $data['senor_de_la_noche'] = $u->createContainer(array($g_img, $g_data), 'dato-calendario g');
    /* Fase de la luna */
    $luna_img_nid = $lista_lunas[$luna];
    $luna_node = \Drupal::entityManager()
      ->getStorage('node')->load($luna_img_nid);
    $luna_title = $luna_node->field_titulo_de_fase_lunar->getValue()[0]['value'];
    $luna_img = $u->getStyledImage($luna_node, 'field_imagenes');
    $luna_data = array(
      '#type' => 'markup',
      '#markup' => '<strong>' . t('Moon') . ' </strong>"' . $luna_title . '"',
      '#prefix' => '<div class="data-title">',
      '#suffix' => '</div>',
      );
    $data['fase_lunar'] = $u->createContainer(array($luna_data, $luna_img));
    /* Cuenta larga notacion numerica */
    $cuenta = $u->createContainer(array(
        '<strong>' . t('Long count') . ' </strong>',
        '<span>'
          . $baktun . '.'
          . $katun . '.' . $tun . '.' . $uinal . '.' . $kin
          . '</span>'
    ));
    $cuenta = array(
      '#type' => 'markup',
      '#attributes' => array(
        'class' => 'fecha-cuenta-larga',
      ),
      '#markup' => render($cuenta),
    );
    /* Detalles de fecha */
    $fecha_haab = $u->createContainer(array('<strong>Macewal Q\'ij (Haab): </strong>', '<span>' . $haab_no . ' ' . $haab_name . '</span>'));
    $fecha_cholqij =  $u->createContainer(array('<strong>Chol Q\'ij (Tzolkin): </strong>', '<span>' . $cholqij_no . ' ' . $nahual . '</span>'));
    $senor = $u->createContainer(array('<strong>' . t('Lord of the night') . ': </strong>' . '<span> G' . $g . '</span>'));
    $haabs = $u->createContainer(array('<strong>' . t('Haab Year') . ' </strong>' . '<span>' . $mayan_haabs . '</span>'));
    $julians = $u->createContainer(array('<strong>' . t('Julian days') . ': </strong>' . '<span>' . $julian . '</span>'));
    $mayan_days = $u->createContainer(array('<strong>' . t('Mayan days') . ': </strong>' . '<span>' . $mayan_days . '</span>'));
    $cargador = $u->createContainer(array('<strong>' . t('Year Bearer') . ': </strong><span>' . $cargador[0] . ' ' . $cargador[2] . '</span>'));
    $data['data'] = $this->data;
    // Datos e icono de direccion
    $cargador_data = $datos_fecha['cargador'];
    $letra_direccion = $cargador_data[3]['direccion'];
    $direccion = $direccion_data = $nombre_direccion = $mensaje_direccion = '';

    if ($convertidor->getCCC()) {
      switch($letra_direccion) {
        case 's':
          $mensaje_direccion = t('"Days start at Midnight."');
          $nombre_direccion = t('south');
        break;
        case 'e':
          $mensaje_direccion = t('"Days start at Sunrise."');
          $nombre_direccion = t('east');
        break;
        case 'n':
          $mensaje_direccion = t('"Days start at Midday."');
          $nombre_direccion = t('north');
        break;
        case 'o':
          $mensaje_direccion = t('"Days start at Sunset."');
          $nombre_direccion = t('west');
        break;
      }

      $direccion_data = $u->createContainer(
        '<div class="direccion-icon img-container fleft">'
        . '<img class="img-responsive" src="' . $GLOBALS['base_path']
        . 'themes/nahuales/images/' . $cargador_data[3]['default_icon']
        . '" /></div><div class="mensaje-direccion fleft">'
        . $mensaje_direccion . '</div>'
      );
      $direccion = $u->createContainer(
        array('<strong>' . t('Direction:') . '</strong>' . '<span>' . $nombre_direccion . '</span>')
      );
    }
    // Crear contenedor con toda la informacion.
    $data['fecha_completa'] = $u->createContainer([
      $fecha_cholqij, $fecha_haab, $cuenta, $senor, $cargador,
      $direccion, $direccion_data, $haabs, $julians, $mayan_days
    ], 'contained-w100 contained-fleft');
    $data['nombre_nahual'] = $nahual;
    /* Gregorian Date description */
    $data['datos_fecha'] = $datos_fecha;
    $week_day = $convertidor->getGregorianWeekDay();
    $month_of_year = $convertidor->getGregorianMonthOfYear();
    list($yyyy, $mm, $dd) = explode('/', $fecha);
    $data['fecha'] = t(
      '@weekday @dd, @monthofyear @yyyy',
      array(
        '@weekday' => $week_day['name'],
        '@dd' => $dd,
        '@monthofyear' => $month_of_year['name'],
        '@yyyy' => $yyyy,
      )
    );

    /* Day reading */
    $query = \Drupal::entityQuery('node')
                ->condition('type', 'article');
    $date = date('Y-m-d', mktime(0, 0, 0, $mm, $dd, $yyyy));

    $query->condition('field_interpretacion_de_fecha', $date, '=')
          ->condition('status', NODE_PUBLISHED);
    $interpretacion = $query->execute();

    $data['article_id'] = 0;
    $descripcion = $fb_img = '';
    if (count($interpretacion)) {
      $nid = array_pop($interpretacion);
      $data['article_id'] = $nid;
      $article = \Drupal::entityManager()->getStorage('node')->load($nid);
      //$data['article'] = $article;
      $build = \Drupal::entityTypeManager()
                ->getViewBuilder('node')->view($article, 'in_tab');
      $data['article_render'] = render($build);
      $data['article_body'] = $article->body->getValue()[0]['value'];
      //$data['article_author'] = $article->author_name->getValue()[0]['value'];
      $data['article_title'] = $article->title->getValue()[0]['value'];

      if (count($article->field_descripcion_corta_del_dia->getValue())) {

        $descripcion = $data['article_descripcion_corta_del_dia'] =
          $article->field_descripcion_corta_del_dia->getValue()[0]['value'];
      }
      //$article_field_image = $article->field_image->getValue()[0]['value'];
      $article_field_tags = $article->field_tags->getValue();
      $data['article_img'] = $u->getStyledImage($article, 'field_images', 'day_image', 'article-image');

      $data['fb_img'] = $u->getStyledImage($article, 'field_imagenes_glifos', 'fb_og_img', 'fb-image');

      if ($data['fb_img']) {
        $fb_img = file_create_url($data['fb_img']['img']["#uri"]);
      }
    }
    $count =  implode('.', $data['count']);
    //echo "<pre>";var_dump($data['data']['haab']);
    //["cholqij_no_name"]
    $titles = $data['data']["cholqij_no"] . ' ' . $data['data']["cholqij"] . ' ' . $data['data']["haab_no"] . ' ' . $data['data']['haab'] . ' ' .
      ' ' . $count;
    if ($fb_img=='') {
      $fb_img = file_create_url($data['nahual_img']['img']["#uri"]);
    }
    if ($descripcion=='') {
      $descripcion = $descripcion_corta;
    }
    $current_uri = 'http://' . \Drupal::request()->getHost()
      . \Drupal::request()->getRequestUri();
    CustomMetatags::setMetatags([
      'titles' => $titles,
      'images' => $fb_img,
      'descriptions' => $descripcion,
      'urls' => $current_uri,
    ]);

    return $data;
  }
}
