<?php

namespace Dkan\DataDictionary;

/**
 * Interface ManagerInterface.
 */
interface ValidationManagerInterface {

  /**
   * Constructor.
   */
  public function __construct(ValidatorInfo $validatorInfo);

  /**
   * Get Validator Label.
   */
  public function getValidatorInfo();

  /**
   * Validate schema.
   *
   * @param string $schema
   *   schema content or file path.
   */
  public static function validateSchema($schema);

  /**
   *
   */
  public function init($schema, $data);

  /**
   * Get Validator Label.
   */
  public function validate();

  /**
   * Get Validator Label.
   */
  public static function render($schema);

}
