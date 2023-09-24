<?php

namespace Drupal\metastore\Exception;

/**
 * Exception thrown when a resource is already registered.
 *
 * @package Drupal\metastore\Exception
 */
class AlreadyRegistered extends \Exception {

  /**
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected array $alreadyRegistered;

  /**
   * Set the entities that are already registered.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $already_registered
   *   The resource mapping entities that are already registered.
   *
   * @return $this
   */
  public function setAlreadyRegistered(array $already_registered): self {
    $this->alreadyRegistered = $already_registered;
    return $this;
  }

  /**
   * Get the resource mapping entities that were already registered.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getAlreadyRegistered(): array {
    return $this->alreadyRegistered;
  }

}
