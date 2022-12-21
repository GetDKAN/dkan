<?php

namespace Drupal\Tests\datastore\Unit\Service\Info;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\datastore\Service\Info\ImportInfoList;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\Container;

class ImportInfoListTest extends TestCase {
  public function test() {

    $ff = FileFetcher::hydrate('{}');

    $result = Result::hydrate('{"status":"error","data":"","error":"File import error"}');

    $imp = (new Chain($this))
      ->add(ImportJob::class, "getResult", $result)
      ->getMock();

    $services = (new Options())
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_info', ImportInfo::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $services)
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'retrieveAll', ["1_1"])
      ->add(ImportInfo::class, 'getFileFetcherAndImporter', [$ff, $imp])
      ->add(ImportInfo::class, 'getBytesProcessed', 1500)
      ->getMock();

    $listService = ImportInfoList::create($container);
    $list = $listService->buildList();
    $this->assertStringContainsString('File import error', $list['1_1']->importerError);
    $this->assertStringContainsString('error', $list['1_1']->importerStatus);
    $this->assertEquals(1500, $list['1_1']->importerBytes);
  }

}
