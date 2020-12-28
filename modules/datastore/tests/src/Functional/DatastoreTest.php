<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\common\Resource;
use Drupal\Tests\common\Traits\CleanUp;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class DatastoreTest extends ExistingSiteBase {
  use CleanUp;

  const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';

  /**
   * @var \Drupal\metastore\ResourceMapper
   */
  private $resourceMapper;

  /**
   * @var  Resource
   */
  private $resource;

  protected function setUp()
  {
    parent::setUp();
    $this->resourceMapper = \Drupal::service('dkan.metastore.resource_mapper');

    $fileUrl = self::S3_PREFIX . "/district_centerpoints_small.csv";
    $resource = new Resource($fileUrl, 'text/csv');
    $this->resource = $resource;
  }

  public function testAnImportIsQueuedOnResourceRegistration()
  {
    $this->GivenANewResourceWasRegisteredWithTheResourceMapper();
    $this->ThenAnImportOperationShouldExistInTheQueue();
  }

  private function GivenANewResourceWasRegisteredWithTheResourceMapper()
  {
    $this->resourceMapper->register($this->resource);
  }

  private function ThenAnImportOperationShouldExistInTheQueue()
  {
    /** @var  $queueFactory \Drupal\Core\Queue\QueueFactory */
    $queueFactory = \Drupal::service('queue');
    $queue = $queueFactory->get('datastore_import');
    $this->assertEqual(1, $queue->numberOfItems());
  }

  public function tearDown()
  {
    parent::tearDown();
    $this->resourceMapper->remove($this->resource->getIdentifier());
    $this->flushQueues();
  }

}
