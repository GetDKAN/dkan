<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Unit;

use Drupal\datastore\Drush;
use Drupal\datastore\DatastoreLookupInterface;
use Drupal\datastore\DatastoreLookup;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Core\Database\StatementInterface;
use Drush\Commands\DrushCommands;

/**
 * @coversDefaultClass \Drupal\datastore\DatastoreLookup
 */
class DatastoreLookupTest extends TestCase {

  /**
   * @var \Drupal\datastore\DatastoreLookupInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $datastoreLookupInterface;

  /**
   * @var \Symfony\Component\Console\Output\OutputInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $output;

  /**
   * @var \Drupal\datastore\Drush
   */
  protected $drush;

  /**
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * @var \Drupal\datastore\DatastoreLookup
   */
  protected $datastoreLookup;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Mock the DatastoreLookupInterface.
    $this->datastoreLookupInterface = $this->createMock(DatastoreLookupInterface::class);

    // Mock the OutputInterface.
    $this->output = $this->createMock(OutputInterface::class);

    // Mock the database connection.
    $this->database = $this->createMock(Connection::class);

    // Instantiate the DatastoreLookup with the mocked database connection.
    $this->datastoreLookup = new DatastoreLookup($this->database);

    // Instantiate the Drush class with the mocked dependencies.
    $this->drush = new Drush(
      $this->createMock(\Drupal\metastore\MetastoreService::class),
      $this->createMock(\Drupal\datastore\DatastoreService::class),
      $this->createMock(\Drupal\datastore\Service\ResourceLocalizer::class),
      $this->createMock(\Drupal\metastore\ResourceMapper::class),
      $this->createMock(\Drupal\datastore\Service\Info\ImportInfoList::class),
      $this->createMock(\Drupal\datastore\PostImportResultFactory::class),
      $this->datastoreLookupInterface
    );

    // Set the output property.
    $this->drush->setOutput($this->output);
  }

  /**
   * Tests the datatableToResourceLookup method.
   *
   * @covers ::datatableToResourceLookup
   */
  public function testDatatableToResourceLookup(): void {
    $data_table_name = 'datatable-name';
    $expected_identifier = 'expected-identifier';

    // Mock the SelectInterface.
    $select = $this->createMock(SelectInterface::class);

    // Mock the query result.
    $query_result = [['identifier' => $expected_identifier]];

    // Mock the StatementInterface.
    $statement = $this->createMock(StatementInterface::class);
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn($query_result);

    // Set up the expectations for the database select query.
    $this->database->expects($this->once())
      ->method('select')
      ->with('dkan_metastore_resource_mapper', 'dm')
      ->willReturn($select);

    $select->expects($this->once())
      ->method('fields')
      ->with('dm', ['identifier'])
      ->willReturnSelf();

    $select->expects($this->once())
      ->method('where')
      ->with(
        'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :data_table_name',
        [':data_table_name' => $data_table_name]
      )
      ->willReturnSelf();

    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    // Call the method and assert the result.
    $result = $this->datastoreLookup->datatableToResourceLookup($data_table_name);
    $this->assertEquals($expected_identifier, $result);
  }

  /**
   * Tests the resourceToDistribution method.
   *
   * @covers ::resourceToDistribution
   */
  public function testResourceToDistribution(): void {
    $resource_id = 'resource-uuid';
    $expected_distribution_identifier = 'distribution-uuid';
  
    // Mock the SelectInterface.
    $select = $this->createMock(SelectInterface::class);
  
    // Mock the query result.
    $query_result = [['identifier' => $expected_distribution_identifier]];
  
    // Mock the StatementInterface.
    $statement = $this->createMock(StatementInterface::class);
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn($query_result);
  
    // Set up the expectations for the database select query.
    $this->database->expects($this->once())
      ->method('select')
      ->with('node__field_json_metadata', 'nfm')
      ->willReturn($select);

    // Add our special expression
    $select->expects($this->once())
      ->method('addExpression')
      ->with("JSON_UNQUOTE(JSON_EXTRACT(nfm.field_json_metadata_value, '$.identifier'))", 'identifier')
      ->willReturnSelf();
  
    // Add our special condition
    $select->expects($this->once())
      ->method('condition')
      ->with(
        'nfm.field_json_metadata_value',
        '%' . $this->database->escapeLike($resource_id . '_') . '%',
        'LIKE'
      )
      ->willReturnSelf();
  
    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);
  
    // Call the method and assert the result.
    $result = $this->datastoreLookup->resourceToDistribution($resource_id);
    $this->assertEquals($expected_distribution_identifier, $result);
  }

  /**
   * Tests the reverseDatasetLookup method for a successful lookup.
   */
  public function testReverseDatasetLookupSuccess(): void {
    $data_table_name = 'datatable-name';
    $resource_id = 'resource-id';
    $distribution_uuid = 'distribution-uuid';
    $dataset_uuid = 'dataset-uuid';

    // Set up the expectations for the datastore lookup methods.
    $this->datastoreLookupInterface->expects($this->once())
      ->method('datatableToResourceLookup')
      ->with($data_table_name)
      ->willReturn($resource_id);

    $this->datastoreLookupInterface->expects($this->once())
      ->method('resourceToDistribution')
      ->with($resource_id)
      ->willReturn($distribution_uuid);

    $this->datastoreLookupInterface->expects($this->once())
      ->method('distributionToDataset')
      ->with($distribution_uuid)
      ->willReturn($dataset_uuid);

    // Set up the expectation for the output.
    $this->output->expects($this->once())
      ->method('writeln')
      ->with('Dataset UUID = ' . $dataset_uuid);

    // Call the reverseDatasetLookup method and assert the result.
    $result = $this->drush->reverseDatasetLookup($data_table_name);
    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $result);
  }

 /**
   * Tests the reverseDatasetLookup method for an error scenario.
   *
   * @covers ::reverseDatasetLookup
   */
  public function testReverseDatasetLookupError(): void {
    $data_table_name = 'invalid-datatable-name';

    // Set up the expectations for the datastore lookup methods.
    $this->datastoreLookupInterface->expects($this->once())
      ->method('datatableToResourceLookup')
      ->with($data_table_name)
      ->willReturn('');

    // Set up the expectation for the output.
    $this->output->expects($this->once())
      ->method('writeln')
      ->with('Can not map data table to dataset: ' . $data_table_name);

    // Call the reverseDatasetLookup method and assert the result.
    $result = $this->drush->reverseDatasetLookup($data_table_name);
    $this->assertEquals(DrushCommands::EXIT_FAILURE, $result);
  }

}
