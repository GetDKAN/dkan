<?php

namespace Drupal\Tests\dashboard\Unit\Controller;

use Drupal\Core\DependencyInjection\Container;
use Drupal\harvest_dashboard\Controller\Controller;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\harvest\Service as Harvest;
use Drupal\metastore\Service as Metastore;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {

  public function testNoHarvest() {

    $container = $this->containerChain('base')->getMock();

    \Drupal::setContainer($container);

    $controller = new Controller();
    $response = $controller->harvests();
    $json = json_encode($response);

    $strings = ["Harvest ID", "Last Run", "# of Datasets"];
    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  function testRegisteredHarvest() {

    \Drupal::setContainer($this->containerChain('registered')->getMock());

    $controller = new Controller();
    $response = $controller->harvests();
    $json = json_encode($response);

    $strings = ["Harvest ID", "Last Run", "# of Datasets", "test", "Never", "N\/A"];

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }

  }

  public function testGoodHarvestRun() {
    \Drupal::setContainer($this->containerChain('good_harvest')->getMock());

    $controller = new Controller();
    $response = $controller->harvests();
    $json = json_encode($response);

    date_default_timezone_set('EST');
    $strings = ["Harvest ID", "Last Run", "# of Datasets", "test", "#url", "SUCCESS",
      json_encode(date('m/d/y H:m:s T', time())), 1];

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testNoDatasets() {

    \Drupal::setContainer($this->containerChain('no_datasets')->getMock());

    $controller = new Controller();
    $response = $controller->harvestDatasets('test');
    $json = json_encode($response);

    $strings = [
      'Dataset ID',
      'Title',
      'Modified Date (Metadata)',
      'Modified Date (DKAN)',
      'Status',
      'Resources'
    ];

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  public function testDataset() {
    \Drupal::setContainer($this->containerChain('datasets')->getMock());

    $controller = new Controller();
    $response = $controller->harvestDatasets('test');
    $json = json_encode($response);

    $strings = [
      'Dataset ID',
      'Title', 'Modified Date (Metadata)',
      'Modified Date (DKAN)',
      'Status',
      'Resources',
      'dataset-1',
      'distro-1'
    ];

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }
  }

  private function containerChain(string $mode = 'base'): Chain {

    switch($mode) {
      case 'base':

        $options = (new Options())
          ->add('dkan.harvest.service', Harvest::class)
          ->add('dkan.metastore.service', Metastore::class)
          ->add('dkan.datastore.import_info', ImportInfo::class)
          ->index(0);

        $cc = (new Chain($this))
          ->add(Container::class, 'get', $options)
          ->add(Harvest::class, 'getAllHarvestIds', []);
        break;

      case 'registered':

        $cc = $this->containerChain('base');
        $cc
          ->add(Harvest::class, 'getAllHarvestIds', ['test'])
          ->addd('getAllHarvestRunInfo', []);
        break;

      case 'good_harvest':

        $runInfo = (object) [
          'status' => (object) [
            'extract' => 'SUCCESS',
            'load' => [
              'dataset-1' => "NEW"
            ]
          ]
        ];

        $cc = $this->containerChain('registered');
        $cc
          ->add(Harvest::class,'getAllHarvestRunInfo', [time()])
          ->addd('getHarvestRunInfo', json_encode($runInfo));
        break;

      case 'no_datasets':
        $cc = $this->containerChain('good_harvest');
        $cc
          ->add(Metastore::class, 'get', new \Exception(""));
        break;

      case 'datasets':
        $importInfo = (object) [
          'fileFetcherStatus' => "done",
          'fileFetcherPercentDone' => "100",
          'importerStatus' => "done",
          'importerPercentDone' => '100'
        ];

        $resourceRef = [
          '%Ref:downloadURL' =>
            [
              (object) ['identifier' => 'file-1', 'data' => [
                'identifier' => 'file-1', 'version' => 'v1'
              ]]
            ]
        ];

        $dataset = [
          'title' => "Hello World!",
          'modified' => "01/20/1985",
          '%Ref:distribution' =>
            [
              (object) ['identifier' => 'distro-1', 'data' => $resourceRef]
            ],
          '%modified' => "01/20/1985"
        ];

        $cc = $this->containerChain('no_datasets');
        $cc
          ->add(Metastore::class, 'get', json_encode($dataset))
          ->add(ImportInfo::class, 'getItem', $importInfo);
        break;

    }

    return $cc;
  }

}
