<?php

namespace Dkan\DataDictionary;

/**
 *
 */
abstract class ValidationManagerBase implements ValidationManagerInterface {

  private $validatorInfo;
  protected $schema;
  protected $data;

  /**
   * Constructor.
   *
   * @param ValidatorInfo validatorInfo Validator struct.
   */
  public function __construct(ValidatorInfo $validatorInfo) {
    $this->validatorInfo = $validatorInfo;
  }

  /**
   *
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

  abstract protected function initValidationReport(Resource $resource);
}
