<?php

namespace Drupal\dkan_datastore\Commands;

use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Output\ConsoleOutput;
use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

class DkanDatastoreCommands extends DrushCommands {

  protected $output;

  public function __construct() {
    $this->output = new ConsoleOutput();
  }

  /**
   * Import.
   *
   * @param string $uuid
   *   The uuid of a dataset.
   *
   * @command dkan-datastore:import
   */
  public function import($uuid) {
    $database = \Drupal::service('dkan_datastore.database');
    $this->output->writeln("Database instance created.");


    try {
      $entity = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);

      if (!isset($entity)) {
        $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
        return;
      }

      $this->output->writeln("Got entity {$entity->id()}.");
      if ($entity->getType() == "dataset") {
        $this->output->writeln("And it is a dataset.");
        $dataset = $entity;

        $metadata = json_decode($dataset->field_json_metadata->value);
        $this->output->writeln("Got the metadata.");

        $resource = new Resource($dataset->id(), $metadata->distribution[0]->downloadURL);
        $this->output->writeln("And created a resource.");

        $provider = new \Dkan\Datastore\Manager\InfoProvider();
        $provider->addInfo(new \Dkan\Datastore\Manager\Info(SimpleImport::class, "simple_import", "SimpleImport"));
        $this->output->writeln("Provider set.");

        $bin_storage = new \Dkan\Datastore\LockableBinStorage("dkan_datastore", new \Dkan\Datastore\Locker("dkan_datastore"), new \Drupal\dkan_datastore\Storage\Variable());
        $this->output->writeln("Bin Storage is set.");

        $factory = new \Dkan\Datastore\Manager\Factory($resource, $provider, $bin_storage, $database);
        $this->output->writeln("Factory is set.");


        /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
        $datastore = $factory->get();
        $this->output->writeln("Got a datastore.");

        $datastore->import();

        $status = $datastore->getStatus();

        $this->output->writeln(json_encode($status));
      }
      else {
        $this->output->writeln("We can not work with non-dataset entities.");
      }
    }
    catch(\Exception $e) {
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
    $database =  \Drupal::service('dkan_datastore.database');
    $this->output->writeln("Database instance created.");


    try {
      $entity = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);

      if (!isset($entity)) {
        $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
        return;
      }

      $this->output->writeln("Got entity {$entity->id()}.");
      if ($entity->getType() == "dataset") {
        $this->output->writeln("And it is a dataset.");
        $dataset = $entity;

        $metadata = json_decode($dataset->field_json_metadata->value);
        $this->output->writeln("Got the metadata.");

        $resource = new Resource($dataset->id(), $metadata->distribution[0]->downloadURL);
        $this->output->writeln("And created a resource.");

        $provider = new \Dkan\Datastore\Manager\InfoProvider();
        $provider->addInfo(new \Dkan\Datastore\Manager\Info(SimpleImport::class, "simple_import", "SimpleImport"));
        $this->output->writeln("Provider set.");

        $bin_storage = new \Dkan\Datastore\LockableBinStorage("dkan_datastore", new \Dkan\Datastore\Locker("dkan_datastore"), new \Drupal\dkan_datastore\Storage\Variable());
        $this->output->writeln("Bin Storage is set.");

        $factory = new \Dkan\Datastore\Manager\Factory($resource, $provider, $bin_storage, $database);
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
    catch(\Exception $e) {
      $this->output->writeln("We were not able to load the entity with uuid {$uuid}");
      $this->output->writeln($e->getMessage());
    }
  }

}

