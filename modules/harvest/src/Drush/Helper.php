<?php

namespace Drupal\harvest\Drush;

use Drupal\harvest\Storage\DatabaseTable;
use Harvest\ETL\Factory;
use Harvest\Harvester;
use Harvest\ResultInterpreter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Trait Helper.
 *
 * @codeCoverageIgnore
 */
trait Helper {

  /**
   * Private..
   */
  private function getHarvester($id) {

    if (!method_exists($this, 'getHarvestPlan')) {
      throw new \Exception("Drupal\harvest\Drush\Helper requires the host to implement the getHarvestPlan method.");
    }

    return new Harvester(new Factory($this->getHarvestPlan($id),
      $this->getStorage($id, "item"),
      $this->getStorage($id, "hash")));
  }

  /**
   * Private..
   */
  private function getPlanStorage() {
    $connection = \Drupal::service('database');
    return new DatabaseTable($connection, "harvest_plans");
  }

  /**
   * Private..
   */
  private function getStorage($id, $type) {
    $connection = \Drupal::service('database');
    return new DatabaseTable($connection, "harvest_{$id}_{$type}");
  }

  /**
   * Private..
   */
  private function renderResult($result) {
    $interpreter = new ResultInterpreter($result);

    $table = new Table(new ConsoleOutput());
    $table->setHeaders(['processed', 'created', 'updated', 'errors']);
    $table->addRow([
      $interpreter->countProcessed(),
      $interpreter->countCreated(),
      $interpreter->countUpdated(),
      $interpreter->countFailed(),
    ]);
    $table->render();
  }

}
