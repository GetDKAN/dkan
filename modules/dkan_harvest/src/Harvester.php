<?php

namespace Drupal\harvest;

use Drupal\harvest\ETL\Factory;
use Drupal\harvest\ETL\Transform\Transform;

/**
 * Executes harvests.
 */
class Harvester {
  public const HARVEST_LOAD_NEW_ITEM = 0;
  public const HARVEST_LOAD_UPDATED_ITEM = 1;
  public const HARVEST_LOAD_UNCHANGED = 2;

  /**
   * The Factory object.
   *
   * @var \Drupal\harvest\ETL\Factory
   */
  private Factory $factory;

  /**
   * Class constructor.
   *
   * @param \Drupal\harvest\ETL\Factory $factory
   *   ETL factory.
   */
  public function __construct(Factory $factory) {
    $this->factory = $factory;
  }

  /**
   * Reverts harvests.
   *
   * @return int
   *   Number of harvests removed.
   *
   * @throws \Exception
   */
  public function revert(): int {
    $ids = $this->factory->hashStorage->retrieveAll();
    $load = $this->factory->get("load");

    if (!method_exists($load, "removeItem")) {
      throw new \Exception("Load of class " . get_class($load) . " does not implement the removeItem method.");
    }

    $counter = 0;
    foreach ($ids as $id) {
      $load->removeItem($id);
      $this->factory->hashStorage->remove($id);
      $counter++;
    }

    return $counter;
  }

  /**
   * Runs harvests.
   *
   * @return array
   *   Array of harvest result statuses and errors.
   */
  public function harvest(): array {
    $result = [];
    $transformers = NULL;
    $items = $this->extract();
    $result['plan'] = json_encode($this->factory->harvestPlan);

    if (is_string($items)) {
      $result['status']['extract'] = "FAILURE";
      $result['errors']['extract'] = $items;
      return $result;
    }

    $result['status']['extract'] = "SUCCESS";
    $result['status']['extracted_items_ids'] = array_keys($items);

    $result['status']['transform'] = [];

    try {
      $transformers = $this->factory->get("transforms");
    }
    catch (\Exception $e) {
      $result['errors']['transform']['loading'] = $e->getMessage();
    }

    $transformed_items = $transformers ? $this->executeTransformers($transformers, $items, $result) : $items;

    if (empty($transformed_items)) {
      return $result;
    }

    return $this->loadItems($transformed_items, $result);
  }

  /**
   * Load harvest items.
   *
   * @param array $items
   *   The items to load.
   * @param array $result
   *   The existing result statuses.
   *
   * @return array
   *   The updated result statuses.
   */
  private function loadItems(array $items, array $result) {
    $result['status']['load'] = [];

    foreach ($items as $identifier => $item) {
      $status = $this->load($item);
      if (!is_string($status)) {
        $result['status']['load'][$identifier] = $this->loadStatusToString($status);
      }
      else {
        $result['errors']['load'][$identifier] = $status;
        $result['status']['load'][$identifier] = "FAILURE";
      }
    }

    return $result;
  }

  /**
   * Extract harvest items.
   *
   * @return array|string
   *   The extracted items or error message if load fails.
   */
  private function extract() {
    try {
      $extract = $this->factory->get('extract');
      $items = $extract->run();
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }

    return $items;
  }

  /**
   * Run transformers on items.
   *
   * @param array $transformers
   *   Array of Transformer objects.
   * @param array $items
   *   Array of items to transform.
   * @param array $result
   *   Array of results.
   *
   * @return array
   *   Array of transformed items.
   */
  private function executeTransformers(array $transformers, array $items, array &$result) {
    $transformed_items = [];

    foreach ($items as $identifier => $item) {
      $transformed_item = $this->executeTransformersSingle($transformers, $item, $identifier, $result);

      if (!is_string($transformed_item)) {
        $transformed_items[$identifier] = $transformed_item;
      }
    }

    return $transformed_items;
  }

  /**
   * Execute transformers on a single item.
   *
   * @param array $transformers
   *   The transformers to execute.
   * @param object $item
   *   The item to transform.
   * @param string $identifier
   *   The item identifier.
   * @param array $result
   *   The result object.
   *
   * @return object
   *   The transformed item.
   */
  private function executeTransformersSingle(array $transformers, object $item, string $identifier, array &$result) {
    $transformed_item = clone $item;

    foreach ($transformers as $transformer) {
      $transformer_name = get_class($transformer);
      $transformed_item = $this->transform($transformer, $transformed_item);

      if (is_string($transformed_item)) {
        $result['status']['transform'][$transformer_name][$identifier] = "FAILURE";
        $result['errors']['transform'][$transformer_name][$identifier] = $transformed_item;
        break;
      }
      else {
        $result['status']['transform'][$transformer_name][$identifier] = "SUCCESS";
      }
    }

    return $transformed_item;
  }

  /**
   * Transform an item.
   *
   * @param \Drupal\harvest\ETL\Transform\Transform $transformer
   *   The transformer to run.
   * @param object $item
   *   The item to transform.
   *
   * @return mixed|string
   *   The transformed object.
   */
  private function transform(Transform $transformer, object $item) {
    $transformed = clone $item;

    try {
      $transformed = $transformer->run($transformed);
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }

    return $transformed;
  }

  /**
   * Load a harvest item.
   *
   * @param object $item
   *   Harvest item object.
   *
   * @return int|string
   *   The load status or error message if load fails.
   */
  private function load(object $item) {
    try {
      $load = $this->factory->get('load');
      return $load->run($item);
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * Convert load status to string.
   *
   * @param int $status
   *   The load status.
   *
   * @return string
   *   A string representing the status.
   */
  private function loadStatusToString(int $status) {
    if ($status === self::HARVEST_LOAD_NEW_ITEM) {
      return "NEW";
    }
    elseif ($status === self::HARVEST_LOAD_UPDATED_ITEM) {
      return "UPDATED";
    }
    elseif ($status === self::HARVEST_LOAD_UNCHANGED) {
      return "UNCHANGED";
    }

    return "UNKNOWN";
  }

}
