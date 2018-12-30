<?php

namespace Dkan\DataDictionary;

/**
 *
 */
abstract class DataDictionaryManagerBase implements DataDictionaryManagerInterface {

  protected $dataDictionaryInfo;
  protected $schema;
  protected $data;

  /**
   * @param DataDictionaryInfo dataDictionaryInfo Validator struct.
   */
  public function __construct(DataDictionaryInfo $dataDictionaryInfo) {
    $this->dataDictionaryInfo = $dataDictionaryInfo;
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

    // Create associated validation report.
    $this->initValidationReport($resource);
  }

  /**
   *
   */
  abstract protected function initValidationReport(Resource $resource);

}
