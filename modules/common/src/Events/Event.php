<?php

namespace Drupal\common\Events;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Class Event.
 *
 * @package Drupal\common\Events
 */
class Event extends SymfonyEvent {
  private $data;
  private $exception;
  private $validator;

  /**
   * Constructor.
   */
  public function __construct($data, $validator = NULL) {
    if (!isset($validator)) {
      $validator = function($data) {
        return true;
      };
    }

    $this->validator = $validator;

    if (call_user_func($this->validator, $data)) {
      $this->data = $data;
    }
    else {
      throw new \Exception("Invalid event data.");
    }

    $this->exception = NULL;
  }

  /**
   * Getter.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Setter.
   */
  public function setData($data): void {
    if (call_user_func($this->validator, $data)) {
      $this->data = $data;
    }
    else {
      throw new \Exception("Invalid event data.");
    }
  }

  /**
   * Getter.
   */
  public function getException(): ?\Exception {
    return $this->exception;
  }

  /**
   * Setter.
   */
  public function setException(\Exception $exception): void {
    $this->exception = $exception;
  }

}
