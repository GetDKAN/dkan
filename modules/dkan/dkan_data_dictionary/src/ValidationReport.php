<?php

namespace Dkan\DataDictionary;

/**
 * @class ValidationReport
 */
class ValidationReport implements \JsonSerializable {

  protected $metadata = array();
  protected $tables = array();

  /**
   *
   */
  public function __construct($metadata = NULL) {

    if (!empty($metadata)) {
      $this->updateMetadata($metadata);
    }
  }

  /**
   *
   */
  public function updateMetadata(\stdClass $metadata) {
    $metadata_props = array(
      'created',
      'uid',
      'revision_id',
      'entity_id',
      'bundle',
      'entity_type',
      'vrid',
    );

    foreach ($metadata_props as $prop) {
      if (isset($metadata->$prop)) {
        $this->metadata[$prop] = $metadata->$prop;
      }
    }
  }

  /**
   *
   */
  public function addTable($table_name, $source, $schema, $datapackage, $headers, $format = 'inline', $row_count = 1) {

    $this->tables[$table_name] = array(
      'source' => $source,
      'schema' => $schema,
      'datapackage' => $datapackage,
      'time' => 0,
      'valid' => TRUE,
      'error-count' => 0,
      'errors' => array(),
      'row-count' => $row_count,
      'headers' => $headers,
      'format' => $format,
    );

    return $this;
  }

  /**
   *
   */
  public function logTableError($table_name, $row_number, $code, $message) {
    $errorEntry = new \stdClass();
    $errorEntry->code = $code;
    $errorEntry->row_number = $row_number;
    $errorEntry->message = $message;

    $this->tables[$table_name]['errors'][] = $errorEntry;

    // Update the error count.
    $this->tables[$table_name]['error-count'] = count($this->tables[$table_name]['errors']);

    // Update the error count.
    $this->tables[$table_name]['valid'] = FALSE;
  }

  /**
   *
   */
  public function updateTableProperty($table_name, $property, $value) {
    $this->tables[$table_name][$property] = $value;
  }

  /**
   *
   */
  public function updateTableTime($table_name, $time) {
    $this->tables[$table_name]['time'] = $time;
  }

  /**
   *
   */
  public function updateTableRowCount($table_name, $row_count) {
    if (isset($this->tables[$table_name])) {
      $this->tables[$table_name]['row-count'] = $row_count;
    }
  }

  /**
   *
   */
  public function reportFormatterView() {
    $container = array(
      '#type' => 'container',
    );

    $status = array(
      '#markup' => t('Valid'),
    );

    if (!$this->getReportStatus()) {
      $status = array(
        '#markup' => t(
          'Invalid CSV (@error_count errors)',
          array('@error_count' => $this->getReportErrorCount())
        ),
      );
    }

    $container['status'] = $status;

    $tables = array(
      '#type' => 'container',
    );

    foreach ($this->tables as $table_key => $table_data) {
      $table_info = array(
        '#markup' => t('@table_name (@status, @error_count / @row_count)',
        array(
          '@table_name' => $table_key,
          '@status' => $table_data['valid'] ? "Valid" : "Invalid",
          '@error_count' => $table_data['error-count'],
          '@row_count' => $table_data['row-count'],
        )
        ),
      );

      $tables[$table_key]['info'] = $table_info;

      if ($table_data['error-count'] > 0) {
        $tables[$table_key]['details'] = array(
          '#theme' => 'table',
          '#header' => array(t('Code'), t('Row Number'), t('Message')),
          '#rows' => $table_data['errors'],
        );
      }
    }

    $container['tables'] = $tables;

    return $container;
  }

  /**
   *
   */
  public function jsonSerialize() {
    $report['time'] = $this->getReportTime();
    $report['valid'] = $this->getReportStatus();
    $report['error-count'] = $this->getReportErrorCount();
    $report['table-count'] = count($this->tables);

    $report['tables'] = $this->tables;

    return $report;
  }

  /**
   *
   */
  public function jsonUnserialize($json) {
    $json_decoded = json_decode($json, TRUE);

    if (!empty($json_decoded['tables'])) {
      $this->tables = $json_decoded['tables'];
    }
  }

  /**
   *
   */
  public function updateMetadataFromResource(Resource $resource) {
    $account = $GLOBALS['user'];

    $this->metadata = array(
      'entity_type' => 'node',
      'bundle' => $resource->getBundle(),
      'entity_id' => $resource->getIdentifier(),
      'revision_id' => $resource->value()->vid,
      'uid' => $account->uid,
      'created' => time(),
    );
  }

  /**
   *
   */
  public function getReportTime() {
    $time = 0;
    foreach ($this->tables as $table) {
      $time = $time + $table['time'];
    }

    return $time;
  }

  /**
   *
   */
  public function getReportSource() {
    return $this->source;
  }

  /**
   *
   */
  public function getReportStatus() {
    $valid = TRUE;

    foreach ($this->tables as $table) {
      $valid = $valid && $table['valid'];
    }

    return $valid;
  }

  /**
   *
   */
  public function getReportErrorCount() {
    $error_count = 0;

    foreach ($this->tables as $table) {
      $error_count = $error_count + $table['error-count'];
    }

    return $error_count;
  }

}
