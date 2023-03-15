<?php

namespace Drupal\datastore\DataDictionary;

use Drupal\Component\Uuid\UuidInterface;

use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

use PDLT\ConverterInterface;
use RootedData\RootedJsonData;

/**
 * Alter table query builder.
 */
abstract class AlterTableQueryBuilderBase implements AlterTableQueryBuilderInterface {

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
   * Uuid service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

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
   * @var array[]
   *
   * Example format:
   * @code
   * [
   *   [
   *     'name' => 'some_date_field',
   *     'title' => 'Some Date Field', // optional
   *     'type' => 'date',
   *     'format' => '%Y-%m-%d' // optional
   *   ],
   * ]
   * @endcode
   */
  protected array $fields = [];

  /**
   * Alter query index names, types, and formats.
   *
   * @var array[]
   *
   * Example format:
   * @code
   * [
   *   [
   *     'name' => 'some_date_field', // optional
   *     'type' => 'fulltext', // optional
   *     'fields' => [
   *       [
   *         'name' => 'field_a',
   *         'length' => 10 // optional
   *       ],
   *       [
   *         'name' => 'field_b',
   *         'length' => 8 // optional
   *       ],
   *     ]
   *   ]
   * ]
   * @endcode
   */
  protected array $indexes = [];

  /**
   * Create an alter table query factory.
   *
   * @param \Drupal\common\Storage\DatabaseConnectionFactoryInterface $database_connection_factory
   *   Database connection factory.
   * @param \PDLT\ConverterInterface $date_format_converter
   *   PHP Date Language Tool Converter.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   Uuid generator service.
   */
  public function __construct(
    DatabaseConnectionFactoryInterface $database_connection_factory,
    ConverterInterface $date_format_converter,
    UuidInterface $uuid
  ) {
    $this->databaseConnectionFactory = $database_connection_factory;
    $this->dateFormatConverter = $date_format_converter;
    $this->uuid = $uuid;
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
    $this->addFields($dictionary->{'$.data.fields'} ?? []);
    $this->addIndexes($dictionary->{'$.data.indexes'} ?? []);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addFields(array $fields): self {
    // Validate and set fields.
    $this->fields = array_map([$this, 'validateField'], $fields);

    return $this;
  }

  /**
   * Validate data-dictionary field.
   *
   * Validate required properties, and fill empty optional properties.
   *
   * @param array $field
   *   Field to validate.
   *
   * @return array
   *   Validated field.
   */
  protected function validateField(array $field): array {
    // Validate required properties.
    if (empty($field['name']) || empty($field['type'])) {
      throw new \UnexpectedValueException('"name" and "type" are required properties for data-dictionary fields.');
    }

    // Provide default values for optional properties.
    $field['title'] ??= '';
    $field['format'] ??= '';

    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function addIndexes(array $indexes): self {
    // Validate and set indexes.
    $this->indexes = array_map([$this, 'validateIndex'], $indexes);

    return $this;
  }

  /**
   * Validate data-dictionary index.
   *
   * Validate required properties, and fill empty optional properties.
   *
   * @param array $index
   *   Index to validate.
   *
   * @return array
   *   Validated index.
   */
  public function validateIndex(array $index): array {
    // Validate nested properties.
    foreach (array_keys($index['fields']) as $key) {
      // Validate required properties.
      if (empty($index['fields'][$key]['name'])) {
        throw new \UnexpectedValueException('"name" is a required property for data-dictionary index fields.');
      }
      // Provide default values for optional properties.
      $index['fields'][$key]['length'] ??= NULL;
    }

    // Provide default values for optional properties.
    $index['name'] ??= $this->generateUniqueId();
    $index['type'] ??= 'index';
    $index['description'] ??= '';

    return $index;
  }

  /**
   * Generate a unique alphanumeric identifier.
   *
   * @return string
   *   Unique identifier.
   */
  protected function generateUniqueId(): string {
    return str_replace('-', '', $this->uuid->generate());
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getQuery(): AlterTableQueryInterface;

}
