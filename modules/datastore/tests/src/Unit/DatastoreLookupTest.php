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
    $data_table_name = 'datastore_1cc649587284c4bf29ada1cc2e58b00d';
    $expected_identifier = 'e1f2ebcd-ee23-454f-87b5-df0306658418';

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
   * Tests the reverseDatasetLookup method for a successful lookup.
   */
  public function testReverseDatasetLookupSuccess(): void {
    $data_table_name = 'datastore_8b7a21d442d603b113f1a17beac8bcdd';
    $resource_id = 'resource-uuid';
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
    $data_table_name = 'invalid_datastore_name';

    // Mock the SelectInterface.
    $select = $this->createMock(SelectInterface::class);

    // Mock the StatementInterface.
    $statement = $this->createMock(StatementInterface::class);
    $statement->expects($this->once())
      ->method('fetchAll')
      ->willReturn([]);

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

    // Expect the exception to be thrown.
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Resource lookup: Can not map data table name invalid_datastore_name to resource ID. Please make sure your data table name exists as a table in the database.');

    // Call the method which should throw the exception.
    $this->datastoreLookup->datatableToResourceLookup($data_table_name);
  }

}