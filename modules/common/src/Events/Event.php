<?php

namespace Drupal\common\Events;

use Drupal\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Custom DKAN extension of the Drupal Event class.
 *
 * @package Drupal\common\Events
 */
class Event extends SymfonyEvent {

  /**
   * Validator.
   *
   * @var \Closure|mixed|null
   */
  public $validator;

  /**
   * Arbitrary data.
   *
   * @var mixed
   */
  protected $data;

  /**
   * The exception we stored.
   *
   * @var \Exception|null
   */
  protected ?\Exception $exception;

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
