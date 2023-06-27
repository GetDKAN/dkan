<?php

namespace Drupal\Tests\harvest\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\common\DatasetInfo;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\harvest\DashboardController;
use Drupal\harvest\HarvestService;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class DashboardControllerTest extends TestCase {

  public function testNoHarvests() {
    $container = $this->getCommonMockChain()
      ->add(HarvestService::class, 'getAllHarvestIds', [])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new DashboardController();
    $response = $controller->harvests();

    $json = json_encode($response);
    $strings = array_merge(DashboardController::HARVEST_HEADERS, ['No harvests found']);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testRegisteredHarvest() {
    $container = $this->getCommonMockChain()
      ->add(HarvestService::class, 'getAllHarvestRunInfo', [])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new DashboardController();
    $response = $controller->harvests();

    $json = json_encode($response);
    $strings = array_merge(DashboardController::HARVEST_HEADERS, ['No harvests found']);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testGoodHarvestRun() {
    $time = time();

    $container = $this->getCommonMockChain()
      ->add(HarvestService::class, 'getAllHarvestRunInfo', [$time])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new DashboardController();
    $response = $controller->harvests();

    $json = json_encode((array)$response);
    $strings = array_merge(DashboardController::HARVEST_HEADERS, [
      'harvest_link',
      'SUCCESS',
      json_encode(date('m/d/y H:m:s T', $time)),
      '1',
    ]);

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  private function getCommonMockChain() : Chain {
    $options = (new Options())
      ->add('dkan.harvest.service', HarvestService::class)
      ->add('dkan.common.dataset_info', DatasetInfo::class)
      ->add('string_translation', TranslationManager::class)
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
      ->add(HarvestService::class, 'getAllHarvestIds', ['test'])
      ->add(HarvestService::class,'getHarvestRunInfo', json_encode($runInfo));
  }

}
