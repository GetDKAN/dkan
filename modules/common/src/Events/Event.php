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

  /**
   * Constructor.
   */
  public function __construct($data) {
    $this->data = $data;
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
    $this->data = $data;
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
