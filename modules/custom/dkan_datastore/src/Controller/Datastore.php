<?php

namespace Drupal\dkan_datastore\Controller;

use Dkan\Datastore\Manager\IManager;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\dkan_datastore\SqlParser;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Datastore implements ContainerInjectionInterface{
    
    /**
     * Service container.
     * 
     * @var ContainerInterface
     */
    protected $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function runQuery($queryStr) {
    if ($this->container
            ->get('dkan_datastore.sql_parser')
            ->validate($queryStr) === TRUE) {
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

        /** @var \Drupal\Core\Database\Connection $connection */
        $connection = $this->container->get('database');

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
  
  protected function explode(string $queryStr) {
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

  protected function getUuidFromSelect(string $select) {
    $pieces = explode("FROM", $select);
    return trim(end($pieces));
  }

  /**
   * @{inheritdocs}
   * @codeCoverageIgnore
   */
    public static function create(ContainerInterface $container) {
        return new static($container);
    }
}
