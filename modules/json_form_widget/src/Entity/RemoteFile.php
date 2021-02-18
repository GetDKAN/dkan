<?php

namespace Drupal\json_form_widget\Entity;

use Drupal\file\Entity\File;

/**
 * Defines the remote file entity class.
 *
 * @ingroup file
 *
 * @ContentEntityType(
 *   id = "remote_file",
 *   label = @Translation("Remote file"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\ContentEntityNullStorage",
 *   },
 *   base_table = "file_managed",
 *   entity_keys = {
 *     "id" = "fid",
 *     "label" = "filename",
 *     "langcode" = "langcode",
 *     "owner" = "uid",
 *     "uuid" = "uuid"
 *   }
 * )
 * @codeCoverageIgnore
 */
class RemoteFile extends File {

  /**
   * {@inheritdoc}
   */
  public static function load($id) {
    return parent::create(['uri' => $id]);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getFileUri();
  }

}
