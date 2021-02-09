<?php

namespace Drupal\metastore_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Metastore item entities.
 *
 * @ingroup metastore_entity
 */
interface MetastoreItemInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Metastore item title.
   *
   * @return string
   *   Title of the Metastore item.
   */
  public function getTitle();

  /**
   * Sets the Metastore item title.
   *
   * @param string $title
   *   The Metastore item title.
   *
   * @return \Drupal\metastore_entity\Entity\MetastoreItemInterface
   *   The called Metastore item entity.
   */
  public function setTitle($title);

  /**
   * Gets the Metastore item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Metastore item.
   */
  public function getCreatedTime();

  /**
   * Sets the Metastore item creation timestamp.
   *
   * @param int $timestamp
   *   The Metastore item creation timestamp.
   *
   * @return \Drupal\metastore_entity\Entity\MetastoreItemInterface
   *   The called Metastore item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Metastore item revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Metastore item revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\metastore_entity\Entity\MetastoreItemInterface
   *   The called Metastore item entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Metastore item revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Metastore item revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\metastore_entity\Entity\MetastoreItemInterface
   *   The called Metastore item entity.
   */
  public function setRevisionUserId($uid);

}
