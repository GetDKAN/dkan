<?php

namespace Drupal\common;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

trait ResourceSchemaDetectionTrait {

  /**
   * Resource file columns.
   *
   * @var string[]
   */
  protected $columns;

  /**
   * First line from resource file.
   *
   * @var string
   */
  protected $headerLine;

  /**
   * Resource data-dictionary.
   *
   * @var array
   */
  protected $dataDictionary;

  /**
   * Read the first line from the given file.
   *
   * @param string $file_path
   *   File path.
   *
   * @return string
   *   First line from file.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
   *   On failure to open the file;
   *   on failure to read the first line from the file.
   */
  public function getFirstLineFromFile(): string {
    // If the header line has already been determined (and stored), return it.
    if (isset($this->headerLine)) {
      return $this->headerLine;
    }

    // Attempt to resolve resource file name from file path.
    $file_path = \Drupal::service('file_system')->realpath($this->getFilePath());
    if ($file_path === FALSE) {
      throw new FileException(sprintf('Unable to resolve file path for resource with identifier "%s".', $this->getUniqueIdentifier()));
    }

    // Ensure the "auto_detect_line_endings" ini setting is enabled before
    // openning the file to ensure Mac style EOL characters are detected.
    $old_ini = ini_set('auto_detect_line_endings', '1');
    // Read the first (header) line from the CSV file.
    $f = fopen($file_path, 'r');
    // Revert ini setting once the file has been opened.
    if ($old_ini !== FALSE) {
      ini_set('auto_detect_line_endings', $old_ini);
    }
    // Ensure the file could be successfully opened.
    if (!isset($f) || $f === FALSE) {
      throw new FileException(sprintf('Failed to open resource file "%s".', $file_path));
    }
    // Attempt to retrieve the first line from the resource file.
    $header_line = fgets($f);
    // Close the resource file since it is no longer necessary.
    fclose($f);
    // Ensure the first line of the resource file was successfully read.
    if (!isset($header_line) || $header_line === FALSE) {
      throw new FileException(sprintf('Failed to read header line from resource file "%s".', $file_path));
    }

    // Cache the header line for later reference.
    $this->headerLine = $header_line;

    return $header_line;
  }

  /**
   * Accessor for schema property.
   *
   * @return array[]
   *  Schema property value.
   */
  public function getSchema(): array {
    if (!isset($this->schema)) {
      $dict_schema = $this->getDataDictionary() ?? [];
      \Drupal::logger('common')->notice(json_encode($dict_schema, JSON_PRETTY_PRINT));
      $file_schema = $this->buildSchemaFromFile();
      $columns = array_column($dict_schema['fields'], 'name');
      foreach ($file_schema as $item) {
        if (!in_array($item['name'], $columns)) {
          $dict_schema['fields'][] = $item;
        }
      }
      $this->schema = self::convertFrictionlessToSqlSchema($dict_schema);
      \Drupal::logger('common')->notice(json_encode($this->schema, JSON_PRETTY_PRINT));
    }

    return $this->schema;
  }

  /**
   * Fetch data-dictionary for this resource.
   *
   * @return array|null
   *   Frictionless Schema array, or `null` if one was not found.
   */
  protected function getDataDictionary(): ?array {
    if (isset($this->dataDictionary)) {
      return $this->dataDictionary;
    }

    $distribution_metadata = json_decode($this->getDistributionMetadata());

    // Extract and validate a data-dictionary's mime-type.
    $data_dictionary_type = $distribution_metadata->data->describedByType ?? '';
    try {
      $mime_type = MimeType::fromString($data_dictionary_type);
    }
    catch (\UnexpectedValueException $e) {
      return NULL;
    }
    if ($mime_type->getType() !== 'application/schema+json' || !$mime_type->hasParameter('type', 'frictionless')) {
      return NULL;
    }

    // Ensure this a data-dictionary URL was found and that it implements the
    // Frictionless Schema.
    $data_dictionary_ref = $distribution_metadata->data->describedBy ?? NULL;
    if (!isset($data_dictionary_ref) || !preg_match(self::UUID_REGEX, $data_dictionary_ref)) {
      return NULL;
    }
    // Fetch the data-dictionary from it's remote URL.
    $data_dictionary_json = $this->fetchDataDictionaryFromUuid($data_dictionary_ref);

    // Validate the retrieved data-dictionary object.
    if (!\Drupal::service('dkan.metastore.valid_metadata')->validate($data_dictionary_json, 'data-dictionary')) {
      \Drupal::logger('dkan')->error('The data-dictionary found at the supplied reference, "@reference", failed validation.', [
        '@reference' => $data_dictionary_ref,
      ]);
      return NULL;
    }
    // Convert the data-dictionary JSON string into an array.
    $this->dataDictionary = json_decode($data_dictionary_json, TRUE)['data'];
    return $this->dataDictionary;
  }

  /**
   * Get this resource's distribution's metadata.
   *
   * @return string
   *   JSON distribution metadata.
   *
   * @throws \RuntimeException
   *   When a distribution is not found for this resource.
   */
  protected function getDistributionMetadata(): string {
    // Retrieve this resource's distribution using it's "unique" ID.
    $identifier_parts = $this->parseUniqueIdentifier($this->getUniqueIdentifier());
    $identifier = $this->buildUniqueIdentifier($identifier_parts['identifier'], $identifier_parts['version'], Resource::DEFAULT_SOURCE_PERSPECTIVE);
    $distribution_ids = \Drupal::service('dkan.metastore.reference_lookup')->getReferencers('distribution', $identifier, 'downloadURL');
    $distribution_id = reset($distribution_ids);
    // Ensure a distribution was found for this resource.
    if ($distribution_id === FALSE) {
      throw new \RuntimeException("Distribution not found for resource with identifier of '{$identifier}'");
    }

    // Retrieve the metadata for this resource's distribution.
    $distribution_json = \Drupal::service('dkan.metastore.storage')->getInstance('distribution')->retrieve($distribution_id);
    return $distribution_json;
  }

  /**
   * Retrieve the data-dictionary belonging to the given UUID.
   *
   * @param string $dict_uuid
   *   Data-dictionary UUID.
   *
   * @return string|null
   *   Data-dictionary JSON object, or null on failure.
   */
  protected function fetchDataDictionaryFromUuid(string $dict_uuid): ?string {
    return \Drupal::service('dkan.metastore.storage')->getInstance('data-dictionary')->retrieve($dict_uuid);
  }

  /**
   * Determine the column identifiers for this resource.
   *
   * @return string[]
   *   Resource column identifiers.
   */
  public function getColumns(): array {
    if (isset($this->columns)) {
      return $this->columns;
    }

    $this->columns = array_column($this->getSchema(), 'name');
    return $this->columns;
  }

  /**
   * Build frictionless schema with only text data types for this resource.
   *
   * @return array[]
   *   Frictionless schema.
   */
  protected function buildSchemaFromFile(): array {
    $schema = [];

    foreach ($this->getFileHeaders() as $header) {
      $schema[] = [
        'name' => self::generateColumnIdentifier($schema, $header),
        'title' => $header,
        'type' => 'text',
      ];
    }

    return $schema;
  }

  /**
   * Attempt to determine table headers for the given resource file.
   *
   * @return string[]
   *   Resource file headers.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
   *   When called on a resource with a unsupported mime-type.
   */
  protected function getFileHeaders(): array {
    $mime_type = $this->getMimeType();

    if ($mime_type === 'text/csv') {
      $headers = str_getcsv($this->getFirstLineFromFile());
    } elseif ($mime_type === 'text/tab-separated-values') {
      $headers = str_getcsv($this->getFirstLineFromFile(), "\t");
    }
    else {
      throw new FileException(sprintf('Unable to determine resource file headers for resource with mime-type of "%s".', $this->getMimeType()));
    }

    return $headers;
  }

}
