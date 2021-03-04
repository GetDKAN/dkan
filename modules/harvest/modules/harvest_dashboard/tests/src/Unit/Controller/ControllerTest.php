<?php

namespace Drupal\Tests\dashboard\Unit\Controller;

use Drupal\Core\DependencyInjection\Container;
use Drupal\common\DatasetInfo;
use Drupal\harvest\Service as Harvest;
use Drupal\harvest_dashboard\Controller\Controller;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {

  public function testNoHarvests() {
    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestIds', [])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new Controller();
    $response = $controller->harvests();

    $json = json_encode($response);
    $strings = array_merge(Controller::HARVEST_HEADERS, ['No harvests found']);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testRegisteredHarvest() {
    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new Controller();
    $response = $controller->harvests();

    $json = json_encode($response);
    $strings = array_merge(Controller::HARVEST_HEADERS, ['No harvests found']);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testGoodHarvestRun() {
    $time = time();

    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [$time])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new Controller();
    $response = $controller->harvests();

    $json = json_encode((array)$response);
    $strings = array_merge(Controller::HARVEST_HEADERS, [
      'harvest_link',
      'SUCCESS',
      json_encode(date('m/d/y H:m:s T', $time)),
      '1',
    ]);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testNoDatasets() {
    $time = time();

    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [$time])
      ->add(DatasetInfo::class, 'gather', ['notice' => 'Not found'])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new Controller();
    $response = $controller->harvestDatasets('test');

    $json = json_encode($response);
    $strings = array_merge(Controller::DATASET_HEADERS,);

    $this->assertEmpty($response['#rows']);
    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testDataset() {
    $time = time();

    $info = [
      'uuid' => 'dataset-1',
      'title' => 'Title',
      'revision_id' => '2',
      'moderation_state' => 'published',
      'modified_date_metadata' => '2020-01-15',
      'modified_date_dkan' => '2021-02-11',
    ];

    $distribution = [
      'distribution_uuid' => 'dist-1',
      'fetcher_status' => 'done',
      'fetcher_percent_done' => 100,
      'importer_status' => 'done',
      'importer_percent_done' => 100,
    ];

    $infoWithDist = array_merge($info, ['distributions' => [$distribution]]);

    $datasetInfo = [
      'latest_revision' => $infoWithDist,
    ];

    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [$time])
      ->add(DatasetInfo::class, 'gather', $datasetInfo)
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new Controller();
    $response = $controller->harvestDatasets('test');

    $json = json_encode($response);
    $strings = array_merge(
      Controller::DATASET_HEADERS,
      Controller::DISTRIBUTION_HEADERS,
      $info,
      $distribution
    );

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  private function getCommonMockChain() : Chain {
    $options = (new Options())
      ->add('dkan.harvest.service', Harvest::class)
      ->add('dkan.common.dataset_info', DatasetInfo::class)
      ->index(0);

    $runInfo = (object) [
      'status' => (object) [
        'extract' => 'SUCCESS',
        'load' => [
          'dataset-1' => "NEW"
        ]
      ]
    ];

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Harvest::class, 'getAllHarvestIds', ['test'])
      ->add(Harvest::class,'getHarvestRunInfo', json_encode($runInfo));
  }

}
