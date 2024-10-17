<?php

namespace Drupal\Tests\datastore\Kernel\Service\Info;

use Drupal\common\DataResource;
use Drupal\common\Storage\FileFetcherJobStoreFactory;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\JobStore;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use Procrastinator\Result;

/**
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ImportInfoListTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  public function test() {
    $result = Result::hydrate('{"status":"error","data":"","error":"File import error"}');
    $ff = FileFetcher::hydrate('{}');

    $resource_mapper = (new Chain($this))
      ->add(ResourceMapper::class, 'get', DataResource::class)
      ->getMock();
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    $import_job = (new Chain($this))
      ->add(ImportJob::class, 'getResult', $result)
      ->getMock();

    $import_info = $this->getMockBuilder(ImportInfo::class)
      ->onlyMethods([
        'getImporter',
        'getFileFetcher',
        'getBytesProcessed',
      ])
      ->setConstructorArgs([
        $this->container->get('dkan.datastore.service.resource_localizer'),
        $this->container->get('dkan.datastore.service.factory.import'),
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('dkan.datastore.service'),
      ])
      ->getMock();
    $import_info->method('getImporter')->willReturn($import_job);
    $import_info->method('getFileFetcher')->willReturn($ff);
    $import_info->method('getBytesProcessed')->willReturn(1500);

    $this->container->set('dkan.datastore.import_info', $import_info);

    $job_store = (new Chain($this))
      ->add(JobStore::class, 'retrieveAll', ['1_1'])
      ->getMock();

    $job_store_factory = (new Chain($this))
      ->add(FileFetcherJobStoreFactory::class, 'getInstance', $job_store)
      ->getMock();

    $this->container->set('dkan.common.filefetcher_job_store_factory', $job_store_factory);

    // Build the list.
    /** @var \Drupal\datastore\Service\Info\ImportInfoList $import_info_list */
    $import_info_list = $this->container->get('dkan.datastore.import_info_list');
    $list = $import_info_list->buildList();

    // Assert the output.
    $this->assertStringContainsString('File import error', $list['1_1']->importerError);
    $this->assertStringContainsString('error', $list['1_1']->importerStatus);
    $this->assertEquals(1500, $list['1_1']->importerBytes);
  }

}
