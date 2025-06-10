<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Unit;

use Drupal\datastore\Drush;
use Drupal\datastore\DatastoreLookupInterface;
use Drupal\datastore\DatastoreLookup;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\metastore\ResourceMapper;
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

    // // Instantiate the DatastoreLookup with the mocked database connection.
    // $this->datastoreLookup = new DatastoreLookup($this->database);

    // // Instantiate the Drush class with the mocked dependencies.
    // $this->drush = new Drush(
    //   $this->createMock(\Drupal\metastore\MetastoreService::class),
    //   $this->createMock(\Drupal\datastore\DatastoreService::class),
    //   $this->createMock(\Drupal\datastore\Service\ResourceLocalizer::class),
    //   $this->createMock(\Drupal\metastore\ResourceMapper::class),
    //   $this->createMock(\Drupal\datastore\Service\Info\ImportInfoList::class),
    //   $this->createMock(\Drupal\datastore\PostImportResultFactory::class),
    //   $this->datastoreLookupInterface
    // );

    // // Set the output property.
    // $this->drush->setOutput($this->output);
  }

  /**
   * Tests the tableToResourceLookup method.
   *
   * @covers ::tableToResourceLookup
   */
  public function testDatatableToResourceLookup(): void {
    $table_name = 'datatable-name';
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
        'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :table_name',
        [':table_name' => $table_name]
      )
      ->willReturnSelf();

    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    // Call the method and assert the result.
    $referenceLookup = $this->createMock(ReferenceLookup::class);
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);
    $result = $datastoreLookup->tableToResourceLookup($table_name);
    $this->assertEquals($expected_identifier, $result);
  }

    /**
   * Tests the tableToResourceLookup method.
   *
   * @covers ::tableToResourceLookup
   */
  public function testDatatableToResourceLookupNoMapping(): void {
    $table_name = 'datatable-name';

    // Mock the SelectInterface.
    $select = $this->createMock(SelectInterface::class);

    // Mock the query result.
    $query_result = [];

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
        'CONCAT(\'datastore_\', MD5(CONCAT(identifier, \'__\', version, \'__\', perspective))) = :table_name',
        [':table_name' => $table_name]
      )
      ->willReturnSelf();

    $select->expects($this->once())
      ->method('execute')
      ->willReturn($statement);

    // Call the method and assert the result.
    $referenceLookup = $this->createMock(ReferenceLookup::class);
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);

    $this->expectExceptionMessage("Resource lookup: Can not map datastore table name");
    $datastoreLookup->tableToResourceLookup($table_name);
  }

  /**
   * Tests the resourceToDistribution method.
   *
   * @covers ::resourceToDistribution
   */
  public function testResourceToDistribution(): void {
    $resource_id = 'resource-id';
    $expected_distribution_identifier = 'distribution-uuid';

    // Mock the referenceLookup service.
    $referenceLookup = $this->createMock(ReferenceLookup::class);

    // Set expectations for getReferencers to use correct arguements and return distribution ID.
    $referenceLookup->expects($this->once())
      ->method('getReferencers')
      ->with('distribution', $resource_id, 'downloadURL')
      ->willReturn([$expected_distribution_identifier]);

    // Perform the lookup.
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);

    // Call the method and assert the result.
    $result = $datastoreLookup->resourceToDistribution($resource_id);
    $this->assertEquals($expected_distribution_identifier, $result);
  }

  /**
   * Tests  resourceToDistribution method for empty result.
   *
   * @covers ::resourceToDistribution
   */
  public function testResourceToDistributionForEmptyResult(): void {
    $resource_id = 'resource-id';

    //Mock the referenceLookup service.
    $referenceLookup = $this->createMock(ReferenceLookup::class);

    // Do the lookup.
    $referenceLookup->expects($this->once())
      ->method('getReferencers')
      ->with('distribution', $resource_id, 'downloadURL')
      ->willReturn([]);

    // Perform the lookup.
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);
    $this->expectExceptionMessage("Distribution lookup: Can not map resource ID");

    // Call the method and assert the result.
    $datastoreLookup->resourceToDistribution($resource_id);

  }

  /**
   * Tests the distributionToDataset method.
   *
   * @covers ::distributionToDataset
   */
  public function testDistributionToDataset() {
    $distribution_id = '550e8400-e29b-41d4-a716-446655440000';
    $expected_dataset_id = 'dataset-uuid';

    // Mock the referenceLookup service.
    $referenceLookup = $this->createMock(ReferenceLookup::class);

    // Set expectations for getReferencers to use correct arguments and return dataset ID.
    $referenceLookup->expects($this->once())
      ->method('getReferencers')
      ->with('dataset', $distribution_id, 'distribution')
      ->willReturn([$expected_dataset_id]);

    // Perform the lookup.
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);

    // Call the method and assert the result.
    $result = $datastoreLookup->distributionToDataset($distribution_id);
    $this->assertEquals($expected_dataset_id, $result);
  }


  /**
   * Tests the distributionToDataset method.
   *
   * @covers ::distributionToDataset
   */
  public function testDistributionToDatasetForInvalidDistribution() {
    $distribution_id = 'distribution-id';

    $this->expectExceptionMessage("Dataset lookup: Distribution UUID must be 36 characters.");
    // Mock the referenceLookup service.
    $referenceLookup = $this->createMock(ReferenceLookup::class);
    // Perform the lookup.
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);

    // Call the method and assert the result.
    $datastoreLookup->distributionToDataset($distribution_id);
  }

  /**
   * Tests the distributionToDataset method.
   *
   * @covers ::distributionToDataset
   */
  public function testDistributionToDatasetNonExistantDistribution() {
    $distribution_id = '550e8400-e29b-41d4-a716-446655440000';

    // Mock the referenceLookup service.
    $referenceLookup = $this->createMock(ReferenceLookup::class);

    // Set expectations for getReferencers to use correct arguments and return dataset ID.
    $referenceLookup->expects($this->once())
      ->method('getReferencers')
      ->with('dataset', $distribution_id, 'distribution')
      ->willReturn([]);

    // Perform the lookup.
    $datastoreLookup = new DatastoreLookup($this->database, $referenceLookup);

    // Call the method and assert the result.
    $this->expectExceptionMessage("No dataset found for distribution ID: {$distribution_id}");
    $datastoreLookup->distributionToDataset($distribution_id);
  }

}
