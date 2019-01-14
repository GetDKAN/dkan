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

    $count = 0;

    while (TRUE) {
      list($output, $errors) = $this->validateChunk($step);

      $count = $count + count($output);

      if (count($output) < $step) {
        break;
      }
    }

    $this->postValidate($count);
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
