<?php

namespace Drupal\Tests\datastore\Unit\Controller;

use Drupal\Core\DependencyInjection\Container;
use Drupal\common\DatasetInfo;
use Drupal\Core\Pager\Pager;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\datastore\Controller\DashboardController;
use Drupal\harvest\Service as Harvest;
use Drupal\metastore\Service as MetastoreService;
use Drupal\Tests\metastore\Unit\ServiceTest;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class DashboardControllerTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  public function setUp() {
    parent::setUp();
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  public function testNoDatasets() {
    $time = time();

    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [$time])
      ->add(DatasetInfo::class, 'gather', ['notice' => 'Not found']);

    \Drupal::setContainer($container->getMock());

    $controller = DashboardController::create($container->getMock());

    $response = $controller->buildDatasetsImportStatusTable('test');

    $json = json_encode($response);
    $strings = array_merge(DashboardController::DATASET_HEADERS,);

    $this->assertEmpty($response['table']['#rows']);
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
      ->add(DatasetInfo::class, 'gather', $datasetInfo);

    \Drupal::setContainer($container->getMock());

    $controller = DashboardController::create($container->getMock());

    $response = $controller->buildDatasetsImportStatusTable('test');

    $json = json_encode($response);
    $strings = array_merge(
      DashboardController::DATASET_HEADERS,
      DashboardController::DISTRIBUTION_HEADERS,
      $info,
      $distribution
    );

    foreach ($strings as $string) {
      $this->assertStringContainsString($string, $json);
    }

    $title = (string) $controller->buildDatasetsImportStatusTitle('test');
    $this->assertEquals('Datastore Import Status. Harvest <em class="placeholder">test</em>', $title);
  }

  public function testAllDatasets() {
    $time = time();

    $metastoreGetAllDatasets = [
      $this->validMetadataFactory->get(json_encode(["identifier" => "dataset-1"]), 'blah'),
      $this->validMetadataFactory->get(json_encode(["identifier" => "non-harvest-dataset"]), 'blah'),
    ];

    $dataset1Info = [
      'latest_revision' => [
        'uuid' => 'dataset-1',
        'revision_id' => '1',
        'moderation_state' => 'published',
        'title' => 'Dataset 1',
        'modified_date_metadata' => '2019-08-12',
        'modified_date_dkan' => '2021-07-08',
        'distributions' => [
          [
            'distribution_uuid' => 'dist-1',
            'fetcher_status' => 'waiting',
            'fetcher_percent_done' => 0,
            'importer_status' => 'waiting',
            'importer_percent_done' => 0,
          ],
        ],
      ],
    ];

    $nonHarvestDatasetInfo = [
      'latest_revision' => [
        'uuid' => 'non-harvest-dataset',
        'revision_id' => '1',
        'moderation_state' => 'published',
        'title' => 'Non-Harvest Dataset',
        'modified_date_metadata' => '2019-08-12',
        'modified_date_dkan' => '2021-07-08',
        'distributions' => [
          [
            'distribution_uuid' => 'dist-2',
            'fetcher_status' => 'done',
            'fetcher_percent_done' => 100,
            'importer_status' => 'done',
            'importer_percent_done' => 100,
          ],
        ],
      ],
    ];

    $datasetInfoOptions = (new Options())
      ->add('dataset-1', $dataset1Info)
      ->add('non-harvest-dataset', $nonHarvestDatasetInfo);

    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [$time])
      ->add(MetastoreService::class, 'count', 2)
      ->add(MetastoreService::class, 'getRangeUuids', [$dataset1Info['latest_revision']['uuid'], $nonHarvestDatasetInfo['latest_revision']['uuid']])
      ->add(DatasetInfo::class, 'gather', $datasetInfoOptions);

    \Drupal::setContainer($container->getMock());

    $controller = DashboardController::create($container->getMock());

    $response = $controller->buildDatasetsImportStatusTable(NULL);

    // Assert that there are both datasets: harvested and non-harvested.
    $this->assertEquals(2, count($response["table"]['#rows']));

    $this->assertEquals('dataset-1', $response["table"]["#rows"][0][0]["data"]);
    $this->assertEquals('Dataset 1', $response["table"]["#rows"][0][1]);
    $this->assertEquals('NEW', $response["table"]["#rows"][0][4]["data"]);

    $this->assertEquals('non-harvest-dataset', $response["table"]["#rows"][1][0]["data"]);
    $this->assertEquals('Non-Harvest Dataset', $response["table"]["#rows"][1][1]);
    $this->assertEquals('N/A', $response["table"]["#rows"][1][4]["data"]);

    $title = (string) $controller->buildDatasetsImportStatusTitle(NULL);
    $this->assertEquals('Datastore Import Status', $title);
  }

  public function testDatasetNoDistribution() {
    $time = time();

    $dataset1Info = [
      'latest_revision' => [
        'uuid' => 'dataset-1',
        'revision_id' => '1',
        'moderation_state' => 'published',
        'title' => 'Dataset 1',
        'modified_date_metadata' => '2019-08-12',
        'modified_date_dkan' => '2021-07-08',
        'distributions' => [
          'Not found'
        ],
      ],
    ];

    $container = $this->getCommonMockChain()
      ->add(Harvest::class, 'getAllHarvestRunInfo', [$time])
      ->add(DatasetInfo::class, 'gather', $dataset1Info);

    \Drupal::setContainer($container->getMock());

    $controller = DashboardController::create($container->getMock());

    $response = $controller->buildDatasetsImportStatusTable('test');

    $this->assertEmpty($response["table"]["#rows"][0][7]["data"]["#rows"]);
  }

  private function getCommonMockChain() : Chain {
    $options = (new Options())
      ->add('dkan.harvest.service', Harvest::class)
      ->add('dkan.common.dataset_info', DatasetInfo::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('pager.manager', PagerManagerInterface::class)
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
      ->add(Harvest::class, 'getAllHarvestIds', ['test'])
      ->add(Harvest::class,'getHarvestRunInfo', json_encode($runInfo))
      ->add(PagerManagerInterface::class,'createPager', Pager::class)
      ->add(Pager::class,'getCurrentPage', 0);
  }

}
