<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

use PDLT\ConverterInterface;

/**
 * Base alter table query factory.
 */
class Factory implements AlterTableQueryFactoryInterface {

  /**
   * Database connection factory.
   *
   * @var \Drupal\common\Storage\DatabaseConnectionFactoryInterface
   */
  protected DatabaseConnectionFactoryInterface $databaseConnectionFactory;

  /**
   * Internal to SQL date format converter.
   *
   * @var \PDLT\ConverterInterface
   */
  protected ConverterInterface $dateFormatConverter;

  /**
   * Alter query class name.
   *
   * @var string
   */
  protected string $queryClass;

  /**
   * Create an alter table query factory.
   */
  public function __construct(
    DatabaseConnectionFactoryInterface $database_connection_factory,
    ConverterInterface $date_format_converter,
    string $query_class
  ) {
    $this->databaseConnectionFactory = $database_connection_factory;
    $this->dateFormatConverter = $date_format_converter;
    $this->queryClass = $query_class;
  }

  /**
   * {@inheritdoc}
   */
  public function setConnectionTimeout(int $timeout): self {
    $this->databaseConnectionFactory->setConnectionTimeout($timeout);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(string $datastore_table, array $dictionary_fields): AlterTableQueryInterface {
    return new $this->queryClass(
      $this->databaseConnectionFactory->getConnection(),
      $this->dateFormatConverter,
      $datastore_table,
      $dictionary_fields
    );
  }

}
