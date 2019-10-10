<?php

namespace Dkan\Datastore;

/**
 * Class Resource.
 */
class Resource {

  private $id;
  private $filePath;

  /**
   * Resource constructor.
   */
  public function __construct($id, $file_path) {
    $this->id = $id;
    $this->filePath = $file_path;
  }

  /**
   * Getter.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Getter.
   */
  public function getFilePath() {
    return $this->filePath;
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

  /**
   * Get the full path to a resource's file.
   *
   * Regardless of whether it was uploaded or a remote file.
   */
  private static function filePath($node) {
    if (isset($node->field_upload[LANGUAGE_NONE][0]['fid'])) {
      // We can't trust that the field_upload array will contain a real uri, so
      // we need to load the full file object.
      $file = file_load($node->field_upload[LANGUAGE_NONE][0]['fid']);
      $filemime = $file->filemime;
      if (!in_array($filemime, ["text/csv", "text/tsv", "text/tab-separated-values"])) {
        throw new \Exception("This filemime type ({$filemime}) can be added as a resource, but cannot be imported to our datastore.");
      }

      $drupal_uri = $file->uri;
      return drupal_realpath($drupal_uri);
    }
    if (isset($node->field_link_remote_file[LANGUAGE_NONE][0]['fid'])) {
      $file = file_load($node->field_link_remote_file[LANGUAGE_NONE][0]['fid']);
      if ($filemime = $file->filemime) {
        if (!in_array($filemime, ["text/csv", "text/tsv", "text/psv", "text/tab-separated-values"])) {
          throw new \Exception("This filemime type ({$filemime}) can be added as a resource, but cannot be imported to our datastore.");
        }
      }
      stream_wrapper_restore("https");
      stream_wrapper_restore("http");
      return $file->uri;
    }
    throw new \Exception(t("Node !nid doesn't have a proper file path.", array('!nid' => $node->nid)));
  }

}
