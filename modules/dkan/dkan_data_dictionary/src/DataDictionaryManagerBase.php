<?php

namespace Dkan\DataDictionary;

/**
 *
 */
abstract class DataDictionaryManagerBase implements DataDictionaryManagerInterface {

  protected $dataDictionary;
  protected $schema;
  protected $data;
  protected $validationReport;

  /**
   * @param DataDictionary dataDictionary Validator struct.
   */
  public function __construct(DataDictionaryBase $dataDictionary) {
    $this->dataDictionary = $dataDictionary;
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(Resource $resource) {
    $this->resource = $resource;
    $this->schema = $resource->getDataDictSchema();
    $this->data = $resource->getFilePath();

    foreach (array('schema', 'data') as $param) {
      if (empty($this->$param)) {
        throw new \Exception(format_string("Empty param: !s", array('!s' => $param)));
      }
    }

    // Create the associated validation report.
    $this->initValidationReport($resource);
  }

  /**
   *
   */
  abstract protected function initValidationReport(Resource $resource);

  /**
   * {@inheritdoc}
   */
  public function getValidationReport() {
    return $this->validationReport;
  }

}
