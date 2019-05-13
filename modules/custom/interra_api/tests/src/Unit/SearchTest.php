<?php

namespace Drupal\Tests\interra_api\Unit;

use Drupal\interra_api\Search;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\interra_api\Service\DatasetModifier;
use Sae\Sae;
use Drupal\dkan_api\Controller\Dataset as DatasetController;

/**
 * Tests Drupal\interra_api\Search.
 *
 * @coversDefaultClass Drupal\interra_api\Searchn
 * @group interra_api
 */
class SearchTest extends DkanTestBase {

  /**
   *
   */
  public function testFormatDocs() {
    // Setup.
    $mock = $this->getMockBuilder(Search::class)
      ->setMethods(['formatSearchDoc'])
      ->disableOriginalConstructor()
      ->getMock();

    $formatted1 = (object) ['foo' => 'bar-1'];
    $formatted2 = (object) ['foo' => 'bar-2'];

    $docs      = [
      'id-1' => 'doc-1',
      'id-2' => 'doc-2',
    ];
    $loopCount = count($docs);

    $expected = [
      $formatted1,
      $formatted2,
    ];

    // Expect.
    $mock->expects($this->exactly($loopCount))
      ->method('formatSearchDoc')
      ->withConsecutive(
                    [$docs['id-1']],
                    [$docs['id-2']]
            )
      ->willReturn(
                    $formatted1,
                    $formatted2
    );

    // Assert.
    $actual = $mock->formatDocs($docs);
    $this->assertEquals($expected, $actual);
  }

  /**
   *
   */
  public function testFormatSearchDoc() {
    // Setup.
    $mock = $this->getMockBuilder(Search::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $value = uniqid('value');

    // Assert.
    $actual = $mock->formatSearchDoc($value);
    $this->assertInstanceOf('stdClass', $actual);
    $this->assertEquals($actual->doc, $value);
    $this->assertEquals($actual->ref, '');
  }

  /**
   *
   */
  public function testIndex() {
    // Setup.
    $mock = $this->getMockBuilder(Search::class)
      ->setMethods([
        'getDatasets',
        'formatDocs',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDatasetModifier = $this->getMockBuilder(DatasetModifier::class)
      ->setMethods(['modifyDataset'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer([
      'interra_api.service.dataset_modifier' => $mockDatasetModifier,
    ]);

    $item1 = (object) ['foo' => 'foo'];
    $item2 = (object) ['foo' => 'bat'];
    $item3 = (object) ['foo' => 'barbrr'];
    $datasets = [
      $item1,
      $item2,
      $item3,
    ];

    $loopCount = count($datasets);

    $modifiedDatasets = [
      'does not',
      'really',
      'matter',
    ];

    $expected = [uniqid('could be anything at this point')];

    // Expect.
    $mock->expects($this->once())
      ->method('getDatasets')
      ->willReturn($datasets);

    $mockDatasetModifier->expects($this->exactly($loopCount))
      ->method('modifyDataset')
      ->withConsecutive(
                    [$item1],
                    [$item2],
                    [$item3]
            )
      ->willReturnOnConsecutiveCalls(
                    $modifiedDatasets[0],
                    $modifiedDatasets[1],
                    $modifiedDatasets[2]
    );

    $mock->expects($this->once())
      ->method('formatDocs')
      ->with($modifiedDatasets)
      ->willReturn($expected);
    // Assert.
    $actual = $mock->index();
    $this->assertEquals($expected, $actual);
  }

  /**
   *
   */
  public function testGetDatasets() {
    // Setup.
    $mock = $this->getMockBuilder(Search::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockDatasetController = $this->getMockBuilder(DatasetController::class)
      ->setMethods(['getEngine'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer([
      'dkan_api.controller.dataset' => $mockDatasetController,
    ]);

    $mockEngine = $this->getMockBuilder(Sae::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();

    $item1 = (object) ['foo' => 'foo'];
    $item2 = (object) ['foo' => 'bat'];
    $item3 = (object) ['foo' => 'barbrr'];

    $engineOutput = [
      json_encode($item1),
      json_encode($item2),
      json_encode($item3),
    ];

    $expected = [
      $item1,
      $item2,
      $item3,
    ];

    // Expect.
    $mockDatasetController->expects($this->once())
      ->method('getEngine')
      ->willReturn($mockEngine);

    $mockEngine->expects($this->once())
      ->method('get')
      ->willReturn($engineOutput);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getDatasets');
    $this->assertEquals($expected, $actual);
  }

}
