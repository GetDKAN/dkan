<?php

namespace Drupal\Tests\datastore\Unit\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\datastore\Controller\ImportController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\datastore\Controller\ImportController
 * @coversDefaultClass \Drupal\datastore\Controller\ImportController
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ImportControllerTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
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
