<?php

namespace Dkan\Datastore;

/**
 * Class Resource.
 */
class Resource {

  private $id;
  private $node;
  private $filePath;

  /**
   * Resource constructor.
   */
  public function __construct($id, $file_path) {
    // Check to make sure node type is correct
    $node = node_load($id);
    // No access if content type is not default datastore type (usually resource).
    if ($node->type != self::resourceContentType()) {
      throw new Exception("Not a valid content type for datastore; $type expected.");
    }
    $this->id = $id;
    $this->node = node_load($id);
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
    return self::createFromDrupalNode(node_load($nid));
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
    // Return false if wrong type.
    if ($node->type != self::resourceContentType()) {
      return FALSE;
    }
    $id = $node->nid;
    $file_path = self::filePath($node);
    return new self($id, $file_path);
  }

  /**
   * Get the correct datastore resource content type; usually "resource".
   */
  public static function resourceContentType() {
    static $node_type;
    if (!$node_type) {
      $node_type = 'resource';
      drupal_alter('dkan_datastore_node_type', $node_type);
    }
    return $node_type;
  }

  /**
   * Returns name of upload field.
   */
  public static function fileUploadField() {
    static $field;
    if (!$field) {
      $field = 'field_upload';
      drupal_alter('dkan_datastore_file_upload_field', $field);
    }
    return $field;
  }

  /**
   * Returns name of link api field.
   */
  public static function apiLinkField() {
    static $field;
    if (!$field) {
      $field = 'field_link_api';
      drupal_alter('dkan_datastore_field_link_api', $field);
    }
    return $field;
  }

  /**
   * Returns name of remote file field.
   */
  public static function fileLinkField() {
    static $field;
    if (!$field) {
      $field = 'field_link_remote_file';
      drupal_alter('dkan_datastore_field_link_remote_file', $field);
    }
    return $field;
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
   * Private method.
   */
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

  /**
   * Datastore manager access
   */
  public function datastoreAccess($op, $account = NULL) {
    global $user;
    if (!isset($account)) {
      $account = $user;
    }

    switch ($op) {
      case 'view':
        return node_access('view', $this->node, $account);
        break;

      case 'drop':
      case 'delete':
      case 'import':
      case 'manage':
        // All available operations require the 'manage datastore' permission.
        if (user_access('manage datastore', $account) && node_access('update', $this->node, $account)) {
          return TRUE;
        }
        break;
    }

  }

}
