<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\Tests\common\Traits\GetDataTrait;
use Drupal\Tests\metastore\Unit\ServiceTest;
use Drupal\datastore\Controller\ImportController;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;

use Symfony\Component\HttpFoundation\Request;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * DictionaryEnforcer QueueWorker test.
 */
class DictionaryEnforcerTest extends ExistingSiteBase {
  use GetDataTrait;

  /**
   * Cron service.
   *
   * @var \Drupal\Core\Cron
   */
  protected $cron;

  /**
   * Node data storage.
   *
   * @var \Drupal\metastore\Storage\NodeData
   */
  protected $datasetStorage;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

  /**
   * Uuid service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuid;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  /**
   * Import controller.
   *
   * @var \Drupal\datastore\Controller\ImportController
   */
  protected $webServiceApi;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->cron = \Drupal::service('cron');
    $this->metastore = \Drupal::service('dkan.metastore.service');
    $this->uuid = \Drupal::service('uuid');
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
    $this->webServiceApi = ImportController::create(\Drupal::getContainer());
    $this->datasetStorage = \Drupal::service('dkan.metastore.storage')->getInstance('dataset');
  }

  /**
   * Test dictionary enforcement.
   */
  public function testDictionaryEnforcement(): void {
    // Build data-dictionary.
    $dict_id = $this->uuid->generate();
    $fields = [
      [
        'name' => 'a',
        'title' => 'A',
        'type' => 'integer',
        'format' => 'default',
      ],
      [
        'name' => 'b',
        'title' => 'B',
        'type' => 'date',
        'format' => '%m/%d/%Y',
      ],
      [
        'name' => 'c',
        'title' => 'C',
        'type' => 'number',
        'format' => 'default',
      ],
    ];
    $data_dict = $this->validMetadataFactory->get($this->getDataDictionary($fields, $dict_id), 'data-dictionary');
    // Create data-dictionary.
    $this->metastore->post('data-dictionary', $data_dict);

    // Set global data-dictinary in metastore config.
    $metastore_config = \Drupal::configFactory()->getEditable('metastore.settings');
    $metastore_config->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_SITEWIDE);
    $metastore_config->set('data_dictionary_sitewide', $dict_id);
    $metastore_config->save();

    // Build dataset.
    $dataset_id = $this->uuid->generate();
    $dataset = $this->validMetadataFactory->get($this->getDataset($dataset_id, 'Test ' . $dataset_id, ['data-dict.csv'], TRUE), 'dataset');
    // Create datset.
    $this->metastore->post('dataset', $dataset);

    // Run cron to import dataset into datastore.
    $this->cron->run();
    // Run cron to apply data-dictionary.
    $this->cron->run();

    // Retrieve dataset distribution ID.
    $dataset = $this->metastore->get('dataset', $dataset_id);
    $dist_id = $dataset->{'$["%Ref:distribution"][0].identifier'};
    // Build mock request.
    $request = Request::create('http://blah/api');
    // Retrieve schema for dataset resource.
    $response = $this->webServiceApi->summary($dist_id, $request);
    $result = json_decode($response->getContent(), true);

    // Validate schema.
    $this->assertEquals([
      'numOfColumns' => 4, 
      'columns' => [
        'record_number' => [
          'type' => 'serial', 
          'length' => 10, 
          'unsigned' => true, 
          'not null' => true, 
          'mysql_type' => 'int' 
        ], 
        'a' => [
            'type' => 'int', 
            'length' => 11, 
            'mysql_type' => 'int' 
          ], 
        'b' => [
          'type' => 'varchar', 
          'mysql_type' => 'date' 
        ], 
        'c' => [
          'type' => 'numeric', 
          'length' => 3, 
          'mysql_type' => 'decimal' 
        ],
      ], 
      'numOfRows' => 2
    ], $result);
  }

}
