<?php

namespace Drupal\dkan_harvest\Drush;

use Drupal\dkan_harvest\Storage\File;
use Harvest\ETL\Factory;
use Harvest\Harvester;
use Harvest\ResultInterpreter;
use Harvest\Storage\Storage;
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
      throw new \Exception("Drupal\dkan_harvest\Drush\Helper requires the host to implement the getHarvestPlan method.");
    }

    return new Harvester(new Factory($this->getHarvestPlan($id),
      $this->getStorage($id, "item"),
      $this->getStorage($id, "hash")));
  }

  /**
   * Private..
   */
  private function getPlanStorage(): Storage {
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    return new File("{$path}/dkan_harvest/plans");
  }

  /**
   * Private..
   */
  private function getStorage($id, $type) {
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    return new File("{$path}/dkan_harvest/{$id}-{$type}");
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
