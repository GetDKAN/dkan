<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

use PDLT\ConverterInterface;
use RootedData\RootedJsonData;

/**
 * Alter table query builder.
 */
class Builder implements AlterTableQueryBuilderInterface {

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
   * Alter query table name.
   *
   * @var string
   */
  protected string $table;

  /**
   * Alter query field names, types, and formats.
   *
   * @code
   * [
   *   [
   *     'name' => 'some_date_field',
   *     'type' => 'date',
   *     'format' => '%Y-%m-%d' // optional
   *   ],
   * ]
   * @endcode
   *
   * @var array[]
   */
  protected array $fields;

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
  public function setTable(string $table): self {
    $this->table = $table;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addDataDictionary(RootedJsonData $dictionary): self {
    $this->addFields($dictionary->{'$.data.fields'});
    // @TODO: Uncomment once index support has been added to data-dictionaries.
    // $this->addIndexes($dictionary->{'$.data.indexes'});

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addFields(array $fields): self {
    $this->fields = $fields;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addIndexes(array $indexes): self {
    $this->indexes = $indexes;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(): AlterTableQueryInterface {
    return new $this->queryClass(
      $this->databaseConnectionFactory->getConnection(),
      $this->dateFormatConverter,
      $this->table,
      $this->fields,
      $this->indexes,
    );
  }

}
