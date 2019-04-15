<?php

namespace Drupal\dkan_datastore\Controller;

use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\dkan_datastore\SqlParser;

class Datastore {

  public function runQuery($queryStr) {
    if (SqlParser::validate($queryStr) === TRUE) {
      $query_pieces = $this->explode($queryStr);
      $select = array_shift($query_pieces);
      $uuid = $this->getUuidFromSelect($select);

      try {
        $manager = Util::getDatastoreManager($uuid);

        /* @todo This is bad we should respect the levels of abstraction.
         * The manager should not assume what the storage mechanism looks like
         * and neither should we.
         */

        $table = $manager->getTableName();

        $connection = \Drupal::database();

        $query_string = "SELECT * FROM {$table} " . implode(" ", $query_pieces);

        $query = $connection->query($query_string);
        $result = $query->fetchAll();

        return new JsonResponse($result);
      }
      catch (\Exception $e) {
        return new JsonResponse("Invalid query string.");
      }
    }
    else {
      return new JsonResponse("Invalid query string.");
    }
  }

  private function explode(string $queryStr) {
    $pieces =  explode("]", $queryStr);
    foreach ($pieces as $key => $piece) {
      $pieces[$key] = str_replace("[", "", $piece);
      if (substr_count($pieces[$key], "ORDER BY") > 0) {
        $pieces[$key] .= " ASC";
      }
    }
    array_pop($pieces);
    return $pieces;
  }

  private function getUuidFromSelect(string $select) {
    $pieces = explode("FROM", $select);
    return trim(end($pieces));
  }

}
