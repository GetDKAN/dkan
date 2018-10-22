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
   * Get Validator.
   */
  public function getValidatorInfo() {
    return $this->validatorInfo;
  }

  /**
   *
   */
  public function init($schema, $data) {
    $this->setSchema($schema);
    $this->setData($data);

    $this->postInit();
  }

  /**
   * @throws Exception
   */
  public function postInit() {
    foreach (array('schema', 'data') as $param) {
      if (empty($this->$param)) {
        throw new \Exception(format_string("Empty param: !s", array('!s' => $param)));
      }
    }
  }

  /**
   *
   */
  public function setSchema($schema) {
    $this->schema = $schema;
  }

  /**
   *
   */
  public function setData($data) {
    $this->data = $data;
  }

}
