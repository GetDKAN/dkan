<?php

namespace Drupal\Tests\datastore\Unit\Mock;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Schema;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\datastore\Service\Service;
use Drupal\node\NodeInterface;
use FileFetcher\Processor\Local;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class Container {

  private $testCase;

  private $jobStoreData;
  private $tableName;
  private $noNode = FALSE;

  /**
   *
   */
  public function __construct(TestCase $testCase) {
    $this->testCase = $testCase;

    $fileFetcherContent = file_get_contents(__DIR__ . '/../../../data/filefetcher.json');
    $fileFetcherObject = json_decode($fileFetcherContent);
    $data = json_decode($fileFetcherObject->result->data);
    $data->processor = Local::class;
    $fileFetcherObject->result->data = json_encode($data);

    $this->jobStoreData = (object) [
      'jid' => 1,
      'job_data' => json_encode($fileFetcherObject),
    ];
  }

  /**
   * Setter.
   */
  public function setNoNode() {
    $this->noNode = TRUE;
  }

  /**
   * Getter.
   */
  public function get() {
    $container = $this->testCase->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
    $container->method('get')
      ->with($this->testCase->logicalOr($this->testCase->equalTo('dkan.datastore.service')))
      ->will($this->testCase->returnCallback(
        function ($serviceName) {
          switch ($serviceName) {
            case 'dkan.datastore.service':
              $mockEntityRepository = $this->getEntityRepositoryMock();
              $mockConnection = $this->getConnectionMock();
              $mockQueueFactory = $this->getQueueFactoryMock();
              $mockFileSystem = $this->getFileSystemMock();

              return new Service(
                $mockEntityRepository,
                $mockConnection,
                $mockQueueFactory,
                $mockFileSystem
              );
          }
        }
      ));
    return $container;
  }

  /**
   * Private.
   */
  private function getQueueFactoryMock() {
    $mock = $this->testCase->getMockBuilder(QueueFactory::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $mock->method('get')->willReturn($this->getQueueMock());

    return $mock;

  }

  /**
   * Private.
   */
  private function getQueueMock() {
    $mock = $this->testCase->getMockBuilder("\Drupal\Core\Queue\QueueInterface")
      ->disableOriginalConstructor()
      ->setMethods(['createItem'])
      ->getMockForAbstractClass();

    $mock->method('createItem')->willReturn("1");

    return $mock;
  }

  /**
   * Private.
   */
  private function getFileSystemMock() {
    $mock = $this->testCase->getMockBuilder(FileSystem::class)
      ->disableOriginalConstructor()
      ->setMethods(['prepareDir'])
      ->getMockForAbstractClass();

    $mock->method('prepareDir')->willReturn(TRUE);

    return $mock;
  }

  /**
   * Private.
   */
  private function getConnectionMock() {
    $mock = $this->testCase->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->setMethods(['schema', 'query', 'select', 'insert', 'delete', 'update'])
      ->getMockForAbstractClass();

    $mock->method('schema')->willReturn($this->getSchemaMock());
    $mock->method('query')->willReturn($this->getStatementMock());
    $mock->method('select')->willReturnCallback(function ($tableName) {
      $this->tableName = $tableName;
      return $this->getQueryMock();
    });
    $mock->method('insert')->willReturn($this->getQueryMock());
    $mock->method('delete')->willReturn($this->getQueryMock());
    $mock->method('update')->willReturn($this->getQueryMock());

    return $mock;
  }

  /**
   * Private.
   */
  private function getQueryMock() {
    $mock = $this->testCase->getMockBuilder(SelectInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['fields', 'countQuery', 'condition', 'values', 'execute'])
      ->getMockForAbstractClass();

    $mock->method('fields')->willReturn($mock);
    $mock->method('countQuery')->willReturn($mock);
    $mock->method('condition')->willReturn($mock);
    $mock->method('values')->willReturn($mock);
    $mock->method('execute')->willReturn($this->getStatementMock());

    return $mock;
  }

  /**
   * Private.
   */
  private function getStatementMock() {
    $mock = $this->testCase->getMockBuilder(StatementInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['fetchAll', 'fetchField'])
      ->getMockForAbstractClass();
    $mock->method('fetch')->willReturnCallback(function () {
      if ($this->tableName == 'jobstore_filefetcher_filefetcher') {
        return $this->jobStoreData;
      }
      return [];
    });
    $mock->method('fetchAll')
      ->willReturn([
        (object) ['Field' => 'field_1'],
        (object) ['Field' => 'field_2'],
      ]
      );
    $mock->method('fetchField')->willReturn(2);

    return $mock;
  }

  /**
   * Private.
   */
  private function getSchemaMock() {
    $mock = $this->testCase->getMockBuilder(Schema::class)
      ->disableOriginalConstructor()
      ->setMethods(['tableExists'])
      ->getMockForAbstractClass();

    $mock->method('tableExists')->willReturn(TRUE);

    return $mock;
  }

  /**
   * Private.
   */
  private function getEntityRepositoryMock() {
    $mock = $this->testCase->getMockBuilder(EntityRepository::class)
      ->setMethods(['loadEntityByUuid'])
      ->disableOriginalConstructor()
      ->getMock();

    $node = $this->mockNodeInterface();

    if ($this->noNode) {
      $mock->method('loadEntityByUuid')->willThrowException(new EntityStorageException("You Failed"));
    }
    else {
      $mock->method('loadEntityByUuid')
        ->willReturn($node);
    }

    return $mock;
  }

  /**
   * Private.
   */
  private function mockNodeInterface() {
    $mock = $this->testCase->getMockBuilder(NodeInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $node = $this->mockFieldItemListInterface();

    $mock->method('get')
      ->willReturn($node);

    return $mock;
  }

  /**
   * Private.
   */
  private function mockFieldItemListInterface() {
    $mock = $this->testCase->getMockBuilder(FieldItemListInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $list = $this->mockTypedDataInterface();

    $mock->method('get')
      ->willReturn($list);

    return $mock;
  }

  /**
   * Private.
   */
  private function mockTypedDataInterface() {
    $mock = $this->testCase->getMockBuilder(TypedDataInterface::class)
      ->setMethods(['getValue'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $data = [
      'value' => json_encode([
        'data' => [
          'downloadURL' => __DIR__ . '/../../../data/countries.csv',
          'mediaType' => 'text/csv',
        ],
      ]),
    ];

    $mock->method('getValue')
      ->willReturn($data);

    return $mock;
  }

}
