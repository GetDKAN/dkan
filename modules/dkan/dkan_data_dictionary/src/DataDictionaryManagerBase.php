<?php

namespace Dkan\DataDictionary;

/**
 *
 */
abstract class DataDictionaryManagerBase implements DataDictionaryManagerInterface {

  protected $dataDictionary;
  protected $resource;
  protected $schema;
  protected $data;
  protected $validationReport;

  /**
   * @param DataDictionary dataDictionary Validator struct.
   */
  public function __construct(DataDictionaryBase $dataDictionary, Resource $resource) {
    $this->dataDictionary = $dataDictionary;
    $this->resource = $resource;
    $this->schema = $resource->getDataDictSchema();
    $this->data = $resource->getFilePath();

    foreach (array('schema', 'data') as $param) {
      if (empty($this->$param)) {
        throw new \Exception(format_string("Empty param: !s.", array('!s' => $param)));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preValidate() {
    // Setup the validation report object.
    $this->validationReport = new ValidationReport();
  }

  /**
   * {@inheritdoc}
   */
  public function validate($step = 20) {
    $this->preValidate();

    $errors_all = array();
    $rows_checked_all = 0;

    while (TRUE) {
      list($rows_checked, $errors) = $this->validateChunk($step);

      $errors_all = array_merge($errors_all, $errors);
      $rows_checked_all = $rows_checked + $rows_checked_all;

      if ($rows_checked < $step) {
        break;
      }
    }

    $this->postValidate($rows_checked_all);
  }

  /**
   * {@inheritdoc}
   */
  public function postValidate($count) {
    // Validation done.
    $this->validationReport->updateTableRowCount(
      basename($this->resource->getFilePath()),
      $count
    );

    // TODO update time.
    // TODO better deps injection?
    $controller = new ValidationReportControllerD7();
    $controller->save($this->validationReport, $this->resource);
  }

  /**
   * {@inheritdoc}
   */
  public function getValidationReport() {
    return $this->validationReport;
  }

}
