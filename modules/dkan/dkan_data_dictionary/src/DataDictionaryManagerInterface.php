<?php

namespace Dkan\DataDictionary;

/**
 * Interface ManagerInterface.
 */
interface DataDictionaryManagerInterface {

  /**
   * Constructor.
   */
  public function __construct(DataDictionaryInfo $dataDictionaryInfo);

  /**
   * Validate schema.
   *
   * @param string $schema
   *   schema content or file path.
   */
  public static function validateSchema($schema);

  /**
   * Setup.
   */
  public function initialize(Resource $resource);

  /**
   * Get Validator Label.
   */
  public function validate();

  /**
   * Run the validation on a chunk.
   */
  public function validateChunk($chunk_size);

  /**
   * Return render array representation of the data dictionary.
   */
  public static function schemaFormatterView($schema, $display_type);

}
