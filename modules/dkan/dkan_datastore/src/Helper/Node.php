<?php
namespace Dkan\Datastore\Helper;

class Node {
  /**
   * Gets nid using uuid.
   */
  public static function getNid($uuid) {
    $nid = db_query('SELECT nid FROM {node} WHERE uuid = :uuid', array(':uuid' => $uuid))->fetchField();
    if ($nid) {
      return $nid;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Loads node from uuuid.
   */
  public static function getNodeFromUuid($uuid) {
    static $nodes = array();
    if (!isset($node[$uuid])) {
      $nid = self::getNid($uuid);
      if (!$nid) {
        throw new \Exception(t("uuid !uuid not found.", array('!uuid' => $uuid)));
      }
      else {
        $nodes[$uuid] = node_load($nid);
      }
    }
    return $nodes[$uuid];
  }

}