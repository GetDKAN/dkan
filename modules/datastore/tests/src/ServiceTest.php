<?php

namespace Drupal\Tests\datastore;

use Drupal\common\Resource;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Service;
use Drupal\datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\datastore\Service\Import as ImportService;
use Drupal\metastore\ResourceMapper;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use FileFetcher\FileFetcher;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Drupal\datastore\Service\ResourceLocalizer;

/**
 *
 */
class ServiceTest extends TestCase {
  use ServiceCheckTrait;

  /**
   *
   */
  public function testImport() {

    $chain = $this->getContainerChainForService('dkan.datastore.service')
      ->add(ResourceLocalizer::class, 'get', Resource::class)
      ->add(ResourceLocalizer::class, 'getResult', Result::class)
      ->add(FileFetcher::class, 'run', Result::class)
      ->add(ResourceMapper::class, 'get', Resource::class)
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "import", NULL)
      ->add(ImportService::class, "getResult", new Result())
      ->add(QueueFactory::class, "get", NULL);

    $service = Service::create($chain->getMock());
    $result = $service->import("1");

    $this->assertTrue(is_array($result));
  }

}
