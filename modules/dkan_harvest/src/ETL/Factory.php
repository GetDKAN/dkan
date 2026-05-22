<?php

namespace Drupal\dkan_harvest\ETL;

use GuzzleHttp\ClientInterface;
use Opis\JsonSchema\Validator;

/**
 * ETL factory class.
 */
class Factory {

  /**
   * Harvest plan, decoded JSON object.
   *
   * @var object
   */
  public $harvestPlan;

  /**
   * The hash storage object.
   *
   * @var object
   */
  public $itemStorage;

  /**
   * The hash storage object.
   *
   * @var object
   */
  public $hashStorage;

  /**
   * Inject the guzzle client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * Factory constructor.
   *
   * @param object|string $harvest_plan
   *   The harvest.
   * @param object $item_storage
   *   The item storage.
   * @param object $hash_storage
   *   The item storage.
   * @param \GuzzleHttp\ClientInterface|null $client
   *   The http client.
   *
   * @throws \Exception
   */
  public function __construct(
    object|string $harvest_plan,
    object $item_storage,
    object $hash_storage,
    ?ClientInterface $client = NULL,
  ) {
    if (self::validateHarvestPlan($harvest_plan)) {
      $this->harvestPlan = $harvest_plan;
    }
    $this->itemStorage = $item_storage;
    $this->hashStorage = $hash_storage;
    $this->client = $client;
  }

  /**
   * Get the requested ETL type.
   *
   * @param string $type
   *   The ETL type to get.
   *
   * @return array|mixed|void
   *   The requested object.
   *
   * @throws \Exception
   */
  public function get(string $type) {
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

  /**
   * Get an object of the requested class.
   *
   * @param string $class
   *   The name of the class.
   * @param object|null $config
   *   Optional class config.
   *
   * @return mixed
   *   The requested object.
   */
  private function getOne(string $class, ?object $config = NULL) {
    if (!$config) {
      $config = $this->harvestPlan;
    }
    return new $class($config);
  }

  /**
   * Validate harvest plan against schema.
   *
   * @param object|string|null $harvest_plan
   *   The harvest plan object to test.
   *
   * @return bool
   *   Return TRUE if plan validates.
   *
   * @throws \Exception
   */
  public static function validateHarvestPlan(object|string|NULL $harvest_plan): bool {
    if (!is_object($harvest_plan)) {
      throw new \Exception("Harvest plan must be a php object.");
    }

    $path_to_schema = __DIR__ . "/../../schema/schema.json";
    $json_schema = file_get_contents($path_to_schema);
    if ($json_schema === FALSE) {
      throw new \Exception("Harvest plan schema not readable at: {$path_to_schema}");
    }

    $schema = json_decode($json_schema, FALSE, 512, JSON_THROW_ON_ERROR);
    $validator = new Validator();
    $result = $validator->validate($harvest_plan, $schema);

    if (!$result->isValid()) {
      $error = $result->error();
      while (!empty($subs = $error->subErrors())) {
        $error = $subs[0];
      }
      throw new \Exception(
            "Invalid harvest plan. " . implode("->", $error->data()->fullPath()) .
            " " . json_encode($error->args())
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
