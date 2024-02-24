<?php

namespace Drupal\harvest;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a harvest hash entity type.
 */
interface HarvestHashInterface extends ContentEntityInterface, \JsonSerializable {

}
