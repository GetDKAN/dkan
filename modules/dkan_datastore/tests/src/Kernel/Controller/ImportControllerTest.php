<?php

namespace Drupal\Tests\dkan_datastore\Unit\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\dkan_datastore\Controller\ImportController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\dkan_datastore\Controller\ImportController
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\ImportController
 *
 * @group dkan
 * @group dkan_datastore
 * @group kernel
 */
class ImportControllerTest extends KernelTestBase {

  protected static $modules = [
    'dkan_common',
    'dkan_datastore',
    'dkan_metastore',
  ];

  /**
   * @covers ::import
   *
   * @todo The actual response here is a 400 status. Make more tests.
   */
  public function testMultipleImports() {
    $webServiceApi = ImportController::create($this->container);
    $request = Request::create('http://blah/api');
    $result = $webServiceApi->import($request);

    $this->assertInstanceOf(JsonResponse::class, $result);
  }

}
