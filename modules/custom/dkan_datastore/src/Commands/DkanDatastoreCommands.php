<?php

namespace Drupal\dkan_datastore\Commands;

use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Locker;
use Dkan\Datastore\LockableBinStorage;
use Dkan\Datastore\Manager\Info;
use Dkan\Datastore\Manager\InfoProvider;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Output\ConsoleOutput;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

/**
 * @codeCoverageIgnore
 */
class DkanDatastoreCommands extends DrushCommands {

  protected $output;

  /**
   *
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
    $database = \Drupal::service('dkan_datastore.database');
    $this->output->writeln("Database instance created.");

    try {

      $entity = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);

      if (!isset($entity)) {
        $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
        return;
      }

      $this->output->writeln("Got entity {$entity->id()}.");
      if ($entity->getType() == "data" && $entity->field_data_type->value == "dataset") {

        $this->output->writeln("And it is a dataset.");
        $dataset = $entity;

        $metadata = json_decode($dataset->field_json_metadata->value);
        $this->output->writeln("Got the metadata.");

        $resource = new Resource($dataset->id(), $metadata->distribution[0]->downloadURL);
        $this->output->writeln("And created a resource.");

        // Handle the command differently if deferred.
        if (!empty($deferred)) {
          $this->output->writeln("Using deferred processing. Items will be pocessed by queue.");
          /** @var \Drupal\dkan_datastore\Manager\DeferredImportQueuer $deferredImporter */
          $deferredImporter = \Drupal::service('dkan_datastore.manager.deferred_import_queuer');
          $queueId = $deferredImporter->createDeferredResourceImport($uuid, $resource);
          $this->output->writeln("New queue (ID:{$queueId}) was created for `{$uuid}`");
        }
        else {
          $provider = new InfoProvider();
          $provider->addInfo(new Info(SimpleImport::class, "simple_import", "SimpleImport"));
          $this->output->writeln("Provider set.");

          $bin_storage = new LockableBinStorage("dkan_datastore", new Locker("dkan_datastore"), \Drupal::service('dkan_datastore.storage.variable'));
          $this->output->writeln("Bin Storage is set.");

          $factory = new Factory($resource, $provider, $bin_storage, $database);
          $this->output->writeln("Factory is set.");

          /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
          $datastore = $factory->get();
          $this->output->writeln("Got a datastore.");

          $datastore->import();

          $status = $datastore->getStatus();

          $this->output->writeln(json_encode($status));
        }
      }
      else {
        $this->output->writeln("We can not work with non-dataset entities.");
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
    $database = \Drupal::service('dkan_datastore.database');
    $this->output->writeln("Database instance created.");

    try {
      $entity = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);

      if (!isset($entity)) {
        $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
        return;
      }

      $this->output->writeln("Got entity {$entity->id()}.");

      if ($entity->getType() == "data" && $entity->field_data_type->value == "dataset") {

        $this->output->writeln("And it is a dataset.");
        $dataset = $entity;

        $metadata = json_decode($dataset->field_json_metadata->value);
        $this->output->writeln("Got the metadata.");

        $resource = new Resource($dataset->id(), $metadata->distribution[0]->downloadURL);
        $this->output->writeln("And created a resource.");

        $provider = new InfoProvider();
        $provider->addInfo(new Info(SimpleImport::class, "simple_import", "SimpleImport"));
        $this->output->writeln("Provider set.");

        $bin_storage = new LockableBinStorage("dkan_datastore", new Locker("dkan_datastore"), \Drupal::service('dkan_datastore.storage.variable'));
        $this->output->writeln("Bin Storage is set.");

        $factory = new Factory($resource, $provider, $bin_storage, $database);
        $this->output->writeln("Factory is set.");

        /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
        $datastore = $factory->get();
        $this->output->writeln("Got a datastore.");

        $datastore->drop();

        $status = $datastore->getStatus();

        $this->output->writeln(json_encode($status));
      }
      else {
        $this->output->writeln("We can not work with non-dataset entities.");
      }
    }
    catch (\Exception $e) {
      $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
      $this->output->writeln($e->getMessage());
    }
  }

}
