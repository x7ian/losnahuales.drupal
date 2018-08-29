<?php

namespace Drupal\losnahuales\Service;

use Drupal\Core\Entity;
use Drupal\image\Entity\ImageStyle;

/**
 * Class CustomUtils.
 *
 * @package Drupal\losnahuales
 */
class CustomUtils {

  public function getQueueList($queue_name) {
    $queue = \Drupal::entityManager()->getStorage('entity_subqueue')->load($queue_name);
    $list = $queue->get('items')->getValue();
    return array_map(function($item) {
      return $item['target_id'];
    }, $list);
  }

  function getStyledImage($nid, $field,
      $image_style = 'glifos_calculador',
      $classes = '', $index = 0) {

    if (is_numeric($nid)) {
      $node = \Drupal::entityManager()->getStorage('node')->load($nid);
    } else {
      $node = $nid;
    }

    $img_field = $node->$field;
    $glifo_attr = $img_field->getValue()[$index];
    $fid = $glifo_attr['target_id'];
    $image = [];
    if ($fid!=null) {
      $file = \Drupal\file\Entity\File::load($fid);
      $path = $file->getFileUri();
      $image = [
        '#theme' => 'image_style',
        '#uri' => $path, //$img_field->entity->getFileUri(),
        '#style_name' => $image_style,
        '#alt' => $glifo_attr['alt'],
        '#title' => $glifo_attr['title'],
      ];

      return array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => 'img-container ' . $classes,
        ),
        'img' => $image,
      );
    }
    return null;
  }

  function createContainer($elements, $classes = '') {
    $container = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => $classes,
      ),
    );
    $elements = is_string($elements)? [$elements] : $elements;
    foreach($elements as $ii => $ele) {
      if (!is_array($ele)) {
        $ele = array(
          '#type' => 'markup',
          '#attributes' => array(
            'class' => 'contained-item',
          ),
          '#markup' => ''.$ele,
        );
      }
      $container['ele' . $ii] = $ele;
    }
    return $container;
  }
}
