<?php

namespace Drupal\common\Events;

use Drupal\Component\EventDispatcher\Event as DrupalEvent;

/**
 * Custom DKAN extension of the Drupal Event class.
 *
 * @package Drupal\common\Events
 */
class Event extends DrupalEvent {

  /**
   * Validation callback.
   *
   * @var \Closure|mixed|null
   */
  private $validator;

  /**
   * Data.
   *
   * How this is used depends on the event we're responding to.
   *
   * @var mixed
   */
  private $data;

  /**
   * Exception.
   */
  private ?\Exception $exception;

  /**
   * Constructor.
   */
  public function __construct($data, $validator = NULL) {
    if (!isset($validator)) {
      $validator = function ($data) {
        return TRUE;
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
