<?php

namespace Drupal\metastore_entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a Contact entity.
 *
 * We have this interface so we can join the other interfaces it extends.
 *
 * @ingroup metastore_entity
 */
interface MetadataInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
