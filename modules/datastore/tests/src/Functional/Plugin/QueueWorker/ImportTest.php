<?php

namespace Drupal\Tests\datastore\Functional\Service;

use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\metastore\Unit\ServiceTest;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test ResourcePurger service.
 *
 * @package Drupal\Tests\datastore\Functional
 * @group datastore
 */
class ImportTest extends ExistingSiteBase {
  use GetDataTrait;
  use CleanUp;

  public function setUp(): void {
    parent::setUp();

    // Initialize services.
    $this->datasetStorage = \Drupal::service('dkan.metastore.storage')->getInstance('dataset');
    $this->datastore = \Drupal::service('dkan.datastore.service');
    $this->metastore = \Drupal::service('dkan.metastore.service');
    $this->queue = \Drupal::service('queue');
    $this->queueWorkerManager = \Drupal::service('plugin.manager.queue_worker');
    $this->resourcePurger = \Drupal::service('dkan.datastore.service.resource_purger');
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test that setInnodbMode() turns strict mode off for datastore but not
   * the rest of DKAN/Drupal.
   */
  public function testSetInnodbMode() {
    $connection = \Drupal::service('dkan.datastore.database');
    $result = $connection->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "ON");

    $importWorker = $this->queueWorkerManager->createInstance("datastore_import");
    $importWorker->setInnodbMode($connection);

    $result = $connection->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "OFF");

    $connection2 = \Drupal::service('database');
    $result = $connection2->query("SHOW SESSION VARIABLES LIKE 'innodb_strict_mode'")->fetchObject();
    $this->assertEquals($result->Value, "ON");
  }

}
