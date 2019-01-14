<?php

namespace Dkan\DataDictionary;

/**
 * Class Resource.
 */
class Resource extends \EntityDrupalWrapper {

  /**
   * Resource constructor.
   */
  public function __construct($id) {
    parent::__construct('node', $id);
  }

  /**
   * Getter.
   */
  public function getDataDictSchemaType() {
    return $this->field_describedby_spec->value();
  }

  /**
   * Getter.
   */
  public function getDataDictSchema() {
    // @TODO add Exception throw.
    if (!empty($this->field_describedby_file->value())) {
      $schema_file = $this->field_describedby_file->value();
      $wrapper = file_stream_wrapper_get_instance_by_uri($schema_file['uri']);

      $realpath = FALSE;
      if (empty($wrapper)) {
        $realpath = realpath($schema_file['uri']);
      }
      else {
        $realpath = $wrapper->realpath();
      }
      return $realpath;
    }
    elseif (!empty($this->field_describedby_schema->value())) {
      $schema_text = $this->field_describedby_schema->value();
      return $schema_text;
    }

    throw new \Exception(t("Node !nid doesn't have a proper Data Dictionary.",
      array('!nid' => $node->nid)));
  }

  /**
   * Getter.
   */
  public function getId() {
    return $this->getIdentifier();
  }

  /**
   * Getter.
   */
  public function getUUID() {
    return $this->uuid->value();
  }

  /**
   * Getter.
   */
  public function getVUUID() {
    return $this->vuuid->value();
  }

  /**
   * Getter.
   */
  public function getFilePath() {
    // @TODO add Exception throw.
    if (!empty($this->field_upload->value())) {
      $file = $this->field_upload->value();
      $wrapper = file_stream_wrapper_get_instance_by_uri($file->uri);
      return $wrapper->realpath();
    }
    elseif (!empty($this->field_link_remote_file->value())) {
      $file = $this->field_link_remote_file->value();
      return $file->uri;
    }

    throw new \Exception(t("Node !nid doesn't have a proper file path.", array('!nid' => $this->getIdentifier())));
  }

  /**
   * Create a resource from a Resource Node's uuid.
   *
   * @param string $uuid
   *   A node's uuid.
   *
   * @return Resource
   *   Resource.
   */
  public static function createFromDrupalNodeUuid($uuid) {
    $nid = self::getNidFromUuid($uuid);
    return self::createFromDrupalNodeNid($nid);
  }

  /**
   * Create a resource from a Resource Node's nid.
   *
   * @param string $nid
   *   A node's nid.
   *
   * @return Resource
   *   Resource.
   */
  public static function createFromDrupalNodeNid($nid) {
    if ($node = node_load($nid)) {
      return self::createFromDrupalNode($node);
    }
    throw new \Exception(t('Failed to load resource node.'));
  }

  /**
   * Create a resource from a Resource Node.
   *
   * @param object $node
   *   A node.
   *
   * @return Resource
   *   Resource.
   */
  public static function createFromDrupalNode($node) {
    if ($node->type != 'resource') {
      throw new \Exception(t('Invalid node type.'));
    }

    return new self($node->nid);
  }

  /**
   * Gets nid using uuid.
   */
  private static function getNidFromUuid($uuid) {
    $nid = db_query('SELECT nid FROM {node} WHERE uuid = :uuid', array(':uuid' => $uuid))->fetchField();
    if ($nid) {
      return $nid;
    }
    else {
      throw new \Exception(t("uuid !uuid not found.", array('!uuid' => $uuid)));
    }
  }

}
