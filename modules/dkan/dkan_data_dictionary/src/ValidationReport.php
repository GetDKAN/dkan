<?php

namespace Dkan\DataDictionary;

define(VALIDATIONREPORT_DIR, 'public://validation_report');

/**
 *
 */
class ValidationReport implements \JsonSerializable {

  protected $source;
  protected $schema;
  protected $datapackage;
  protected $tables = array();

  /**
   *
   */
  public function __construct($source, $schema, $datapackage=NULL) {
    // Source required.
    if (empty($source)) {
      throw new \Exception(format_string("Empty param: !s", array('!s' => 'source')));
    }
    $this->source = $source;

    // Schema required.
    if (empty($schema)) {
      throw new \Exception(format_string("Empty param: !s", array('!s' => 'schema')));
    }
    $this->schema = $schema;

    $this->datapackage = $datapackage;
  }

  /**
   *
   */
  public function addTable($table_name, $headers, $format='inline', $row_count=1) {

    $this->tables[$table_name] = array(
      'source' => $this->source,
      'schema' => $this->schema,
      'datapackage' => $this->datapackage,
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
    $this->tables[$table_name]['row-count'] = $row_count;
  }

  /**
   *
   */
  public function write(Resource $resource) {
    // TODO update to write to DB.
    $destination_dir = VALIDATIONREPORT_DIR;

    if (!file_prepare_directory($destination_dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      // TODO change this to exception.
      watchdog('ValidationReport', 'Unable to prepare cache directory @dir', array('@dir' => $destination_dir), WATCHDOG_ERROR);
      return;
    }

    file_unmanaged_save_data(json_encode($this), self::getReportDestination($resource), FILE_EXISTS_REPLACE);
  }

  /**
   *
   */
  public function jsonSerialize() {
    $time = 0;
    $valid = TRUE;
    $error_count = 0;
    $table_count = count($this->tables);

    foreach($this->tables as $table) {
      $time = $time + $table['time'];
      $error_count = $error_count + $table['error-count'];
      $valid = $valid && $table['valid'];
    }

    $report['valid'] = $valid;
    $report['error-count'] = $error_count;
    $report['table-count'] = $table_count;

    $report['tables'] = $this->tables;

    return $report;
  }

  /**
   *
   */
  public static function loadJson(Resource $resource) {
    $dest = self::getReportDestination($resource);

    return @file_get_contents($dest);
  }

  /**
   *
   */
  public static function reportFormatterView($json) {
    $report = json_decode($json, TRUE);
    dpm($report);
    $container = array(
      '#type' => 'container',
    );

    $status = array(
      '#markup' => t('Valid')
    );

    if (!$report['valid']) {
      $status = array(
        '#markup' => t('Invalid CSV (@error_count errors)', array('@error_count' => $report['error-count'])),
      );
    }

    $container['status'] = $status;

    $tables = array(
      '#type' => 'container',
    );

    foreach ($report['tables'] as $table_key => $table_data) {
      $table_info = array(
        '#markup' => t('@table_name (@status, @error_count / @row_count)',
        array('@table_name' => $table_key,
        '@status' => $table_data['valid'] ? "Valid" : "Invalid",
        '@error_count' => $table_data['error-count'],
        '@row_count' => $table_data['row-count'])
      ),
      );

      $tables[$table_key]['info'] = $table_info;

      if ($table_data['error-count'] > 0) {
        $tables[$table_key]['details'] = array(
          '#theme' => 'table',
          '#header' => array(t('Code'), t('Row Number'), t('Message'),),
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
  public static function getReportDestination(Resource $resource) {
    $destination_dir = VALIDATIONREPORT_DIR;
    return $destination_dir . '/' . $resource->getVUUID() . '.json';
  }
}
