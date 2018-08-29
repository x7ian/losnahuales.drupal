<?php

namespace Drupal\losnahuales\Ajax;
use Drupal\Core\Ajax\CommandInterface;

class CalculadorDisplay implements CommandInterface {
  protected $message;
  
  public function __construct($message) {
    $this->message = $message;
  }
  
  public function render() {
    return array(
      'command' => 'updateCalculador',
      'content' => $this->message['content'],
      //'otracosa' => $this->message['algomas'],
    );
  }
}

