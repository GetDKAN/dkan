<?php

namespace Drupal\dkan_datastore\Drush;

use Dkan\Datastore\Manager\Factory;
use Locker\Locker;
use Dkan\Datastore\LockableBinStorage;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\InfoProvider;
use Symfony\Component\Console\Output\ConsoleOutput;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;
use Drupal\dkan_data\ValueReferencer;

use Drush\Commands\DrushCommands;

/**
 * @codeCoverageIgnore
 */
class Commands extends DrushCommands {

  protected $output;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct() {
    $this->output = new ConsoleOutput();
  }

  /**
   * Import.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   * @param bool $deferred
   *   Whether or not the process should be deferred to a queue.
   *
   * @TODO pass configurable options for csv delimiter, quite, and escape characters.
   * @command dkan-datastore:import
   */
  public function import($uuid, $deferred = FALSE) {
    try {
      // Load metadata with both identifier and data for this request.
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_BOTH);

      foreach ($this->getDistributionsFromUuid($uuid) as $distribution) {
        if (!empty($deferred)) {
          $this->queueImport($uuid, $this->getResource($distribution));
        }
        else {
          $this->processImport($distribution);
        }
      }
    }
    catch (\Exception $e) {
      $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
      $this->output->writeln($e->getMessage());
    }
  }

  /**
   * Drop.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   *
   * @command dkan-datastore:drop
   */
  public function drop($uuid) {
    try {
      // Load metadata with both identifier and data for this request.
      drupal_static('dkan_data_dereference_method', ValueReferencer::DEREFERENCE_OUTPUT_BOTH);

      foreach ($this->getDistributionsFromUuid($uuid) as $distribution) {
        $this->processDrop($distribution);
      }
    }
    catch (\Exception $e) {
      $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
      $this->output->writeln($e->getMessage());
    }
  }

  private function queueImport($uuid, $resource) {
    /** @var \Drupal\dkan_datastore\Manager\DeferredImportQueuer $deferredImporter */
    $deferredImporter = \Drupal::service('dkan_datastore.manager.deferred_import_queuer');
    $queueId = $deferredImporter->createDeferredResourceImport($uuid, $resource);
    $this->output->writeln("New queue (ID:{$queueId}) was created for `{$uuid}`");
  }

  private function processImport($distribution) {
    $datastore = $this->getDatastore($this->getResource($distribution));
    $datastore->import();
  }

  private function processDrop($distribution) {
    $datastore = $this->getDatastore($this->getResource($distribution));
    $datastore->drop();
  }

  private function getResource($distribution) {
    $distribution_node = $this->getDistributionNode($distribution);
    return new Resource($distribution_node->id(), $distribution->data->downloadURL);
  }

  private function getDistributionNode($distribution) {
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $dist_nodes = $nodeStorage->loadByProperties(['uuid' => $distribution->identifier]);
    $dist_node = reset($dist_nodes);
    if (!$dist_node) {
      throw new \Exception("Unable to find thus skipping distribution node {$distribution->identifier}.");
    }
    return $dist_node;
  }

  private function getDatastore($resource) {
    $provider = new InfoProvider();
    $provider->addInfo(new Info(SimpleImport::class, "simple_import", "SimpleImport"));
    $bin_storage = new LockableBinStorage("dkan_datastore", new Locker("dkan_datastore"), \Drupal::service('dkan_datastore.storage.variable'));
    $database = \Drupal::service('dkan_datastore.database');
    $factory = new Factory($resource, $provider, $bin_storage, $database);

    /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
    return $factory->get();
  }

  /**
   * Get one or more distributions (aka resources) from a uuid.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $nodeStorage
   * @param string $uuid
   *
   * @return array
   */
  protected function getDistributionsFromUuid($uuid) {
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $nodeStorage->loadByProperties([
      'uuid' => $uuid,
      'type' => 'data',
    ]);
    $node = reset($nodes);
    if (!$node) {
      $this->output->writeln("We were not able to load a data node with uuid {$uuid}.");
      return [];
    }
    // Verify data is of expected type.
    $expectedTypes = [
      'dataset',
      'distribution',
    ];
    if (!isset($node->field_data_type->value) || !in_array($node->field_data_type->value, $expectedTypes)) {
      $this->output->writeln("Data not among expected types: " . implode(" ", $expectedTypes));
      return [];
    }
    // Standardize whether single resource object or several in a dataset.
    $metadata = json_decode($node->field_json_metadata->value);
    $distributions = [];
    if ($node->field_data_type->value == 'dataset') {
      $distributions = $metadata->distribution;
    }
    if ($node->field_data_type->value == 'distribution') {
      $distributions[] = $metadata;
    }

    return $distributions;
  }

}
