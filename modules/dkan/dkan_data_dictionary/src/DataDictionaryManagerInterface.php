<?php

namespace Dkan\DataDictionary;

/**
 * Interface ManagerInterface.
 */
interface DataDictionaryManagerInterface {

  /**
   * Constructor.
   */
  public function __construct(DataDictionaryBase $dataDictionary);

  /**
   * Setup.
   */
  public function initialize(Resource $resource);

  /**
   * Get Validator Label.
   */
  public function validate();

  /**
   * Run the validation on limited number of records (chunk) from the data.
   */
  public function validateChunk($chunk_size);

}
