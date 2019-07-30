<?php

namespace Drupal\dkan_harvest\Service;

use Drupal\dkan_harvest\Reverter;
use Harvest\Storage\Storage;
use Drupal\dkan_harvest\Storage\File;

use Harvest\ETL\Factory as EtlFactory;
use Harvest\Harvester;
use Harvest\ResultInterpreter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Factory class for bits and pieces of dkan_harvest.
 *
 * Coverage is mostly ignored for this class since it's mostly a proxy to
 * initialise new instances.
 *
 * @codeCoverageIgnore
 */
class Factory {

    /**
     * New instance of Reverter.
     *
     * @param mixed $harvest_plan
     *   Harvest plan.
     *
     * @codeCoverageIgnore
     *
     * @return \Drupal\dkan_harvest\Reverter Reverter
     */
    public function newReverter($sourceId, Storage $hash_storage) {
        return new Reverter($sourceId, $hash_storage);
    }

    /**
     *
     * @return File
     */
    public function getPlanStorage(): Storage {
        $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
        return new File("{$path}/dkan_harvest/plans");
    }

    /**
     *
     * @param type $id
     * @param type $type
     * @return File
     */
    public function getStorage($id, $type) {
        $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
        return new File("{$path}/dkan_harvest/{$id}-{$type}");
    }

/**
 * Get Harvestter from id and harvest plan.
 * @param string $id
 * @param \stdClass $harvestPlan
 * @return Harvester
 */
  public function getHarvester(string $id, \stdClass $harvestPlan=null): Harvester {

      if(empty($harvestPlan)){
          $harvestPlan = json_decode(
        $this->getPlanStorage()
            ->retrieve($id)
    );
      }

    return new Harvester(new EtlFactory($harvestPlan,
      $this->getStorage($id, "item"),
      $this->getStorage($id,"hash")));
  }
}
