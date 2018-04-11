<?php

namespace Dkan\Datastore;

class Resource {
  private $id;
  private $filePath;

  public function __construct($id, $file_path) {
    $this->id = $id;
    $this->filePath = $file_path;
  }

  public function getId() {
    return $this->id;
  }

  public function getFilePath() {
    return $this->filePath;
  }

  public static function createFromDrupalNodeUuid($uuid) {
    $nid = self::getNidFromUuid($uuid);
    return self::createFromDrupalNodeNid($nid);
  }

  public static function createFromDrupalNodeNid($nid) {
    return self::createFromDrupalNode(node_load($nid));
  }

  public static function createFromDrupalNode($node) {
    $id = $node->nid;
    $file_path = self::filePath($node);
    return new self($id, $file_path);
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

  private static function filePath($node) {
    if (!empty($node->field_upload)) {
      $drupal_uri = $node->field_upload[LANGUAGE_NONE][0]['uri'];
      return drupal_realpath($drupal_uri);
    }
    if (!empty($node->field_link_remote_file)) {
      stream_wrapper_restore("https");
      stream_wrapper_restore("http");
      return $node->field_link_remote_file[LANGUAGE_NONE][0]['uri'];
    }
    throw new \Exception(t("Node !nid doesn't have a proper file path.", array('!nid' => $node->nid)));

  }

}