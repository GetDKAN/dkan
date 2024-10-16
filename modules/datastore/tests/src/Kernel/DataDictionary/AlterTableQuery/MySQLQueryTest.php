<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class MySQLQueryTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * @covers ::executeFulltextAlter
   */
  public function testExecuteFullTextAlterWithException(): void {
    $exception_message = 'Test exception message.';
    $comment_message = 'comment message';

    // Throw an exception from the connection object.
    $connection = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['prepareStatement'])
      ->getMockForAbstractClass();
    $connection->expects($this->once())
      ->method('prepareStatement')
      ->willThrowException(new \Exception($exception_message));

    $this->container->set('database', $connection);

    // Set up a logger channel to expect our log message.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['error'])
      ->getMockForAbstractClass();
    // Error() must be called once, with our special message.
    $logger->expects($this->once())
      ->method('error')
      ->with('Error applying fulltext index to dataset ' . $comment_message);

    // Mock a logger factory to return our special mocked logger.
    $logger_factory = $this->getMockBuilder(LoggerChannelFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $logger_factory->expects($this->once())
      ->method('get')
      ->willReturn($logger);

    $this->container->set('logger.factory', $logger_factory);

    // Get a MySQLQuery object to test.
    $table = 'foo';
    $mysql_query = new MySQLQuery(
      $connection,
      $this->container->get('pdlt.converter.strptime_to_mysql'),
      $table,
      [],
      []
    );

    // Set executeFulltextAlter() to be public and run it.
    $ref_full_text_alter = new \ReflectionMethod($mysql_query, 'executeFulltextAlter');
    $ref_full_text_alter->setAccessible(TRUE);
    $ref_full_text_alter->invokeArgs($mysql_query, ['', '', '', $comment_message]);
  }

}
