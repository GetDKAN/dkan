<?php

namespace Drupal\harvest\ETL;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;

/**
 * ETL factory class.
 */
class Factory {

  public $harvestPlan;
  public $itemStorage;
  public $hashStorage;
  protected $client;

  public function __construct(
    $harvest_plan,
    $item_storage,
    $hash_storage,
    $client = NULL,
  ) {
    if (self::validateHarvestPlan($harvest_plan)) {
      $this->harvestPlan = $harvest_plan;
    }
    $this->itemStorage = $item_storage;
    $this->hashStorage = $hash_storage;
    $this->client = $client;
  }

  public function get($type)
  {

    switch ($type) {
      case  "extract":
        $class = $this->harvestPlan->extract->type;
        $this->validateClass($class);

        return new $class($this->harvestPlan, $this->client);

      case "load":
        $class = $this->harvestPlan->load->type;
        $this->validateClass($class);

        return new $class($this->harvestPlan, $this->hashStorage, $this->itemStorage);

      case "transforms":
        $transforms = [];
        if (isset($this->harvestPlan->transforms)) {
          foreach ($this->harvestPlan->transforms as $info) {
            $class = $info;
            $this->validateClass($class);

            $transforms[] = $this->getOne($class, $this->harvestPlan);
          }
        }

        return $transforms;
    }
  }

  private function getOne(string $class, $config = NULL) {
    if (!$config) {
      $config = $this->harvestPlan;
    }
    return new $class($config);
  }

  /**
   * Validate harvest plan against schema.
   *
   * @param $harvest_plan
   *   The harvest plan object to test.
   *
   * @return bool
   *   Return TRUE if plan validates.
   *
   * @throws \Exception
   */
  public static function validateHarvestPlan($harvest_plan): bool {
    if (!is_object($harvest_plan)) {
      throw new \Exception("Harvest plan must be a php object.");
    }

    $path_to_schema = __DIR__ . "/../../schema/schema.json";
    $json_schema = file_get_contents($path_to_schema);

    $data = $harvest_plan;
    $schema = Schema::fromJsonString($json_schema);
    $validator = new Validator();

    /** @var ValidationResult $result */
    $result = $validator->schemaValidation($data, $schema);

    if (!$result->isValid()) {
      /** @var ValidationError $error */
      $error = $result->getFirstError();
      throw new \Exception(
            "Invalid harvest plan. " . implode("->", $error->dataPointer()) .
            " " . json_encode($error->keywordArgs())
        );
    }

    return TRUE;
  }

  /**
   * Validate that a class exists.
   *
   * @param string $class
   *   The name of the class to validate.
   *
   * @return bool
   *   Returns TRUE if class exists.
   *
   * @throws \Exception
   */
  private function validateClass(string $class) : bool {
    if (!class_exists($class)) {
      throw new \Exception("Class {$class} does not exist");
    }

    return TRUE;
  }

}
