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
   * Validate schema.
   *
   * @param string $schema
   *   schema content or file path.
   */
  public static function validateSchema($schema);

  /**
   *
   */
  public function initialize(Resource $resource);

  /**
   * Get Validator Label.
   */
  public function validate();

  /**
   * Get Validator Label.
   */
  public function validateChunk($chunk_size);

  /**
   * Return render array representation of the data dictionary.
   */
  public static function schemaFormatterView($schema, $display_type);

}
