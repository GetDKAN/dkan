<?php

namespace Dkan\DataDictionary;

/**
 * Interface ManagerInterface.
 */
interface DataDictionaryManagerInterface {

  /**
   * Constructor.
   */
  public function __construct(DataDictionaryBase $dataDictionary, Resource $resource);

  /**
   * Validate the resource againts it's associated schema.
   */
  public function validate();

  /**
   * Run the validation on limited number of records (chunk) from the data.
   */
  public function validateChunk($chunk_size);

  /**
   * Getter for the schema label to be used in the report.
   */
  public static function getSchema();

}
