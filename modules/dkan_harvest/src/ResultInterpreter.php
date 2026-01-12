<?php

namespace Drupal\harvest;

/**
 * Extracts information from an arroy of harvest result statuses.
 */
class ResultInterpreter {
  /**
   * The result statuses of actions performed by a harvest.
   *
   * @var array
   */
  private array $result;

  /**
   * ResultInterpreter constructor.
   *
   * @param array $result
   *   Harvest result data.
   */
  public function __construct(array $result) {
    $this->result = $result;
  }

  /**
   * Get the number of items created by harvest.
   *
   * @return int
   *   Number of items created.
   */
  public function countCreated(): int {
    return $this->loadCount("NEW");
  }

  /**
   * Get the number of items updated by harvest.
   *
   * @return int
   *   Number of items updated.
   */
  public function countUpdated(): int {
    return $this->loadCount("UPDATED");
  }

  /**
   * Get the harvest failures.
   *
   * @return int
   *   Number of failures.
   */
  public function countFailed(): int {
    $load_failures = $this->loadCount("FAILURE");
    $transform_failures = $this->transformFailures();
    return $load_failures + $transform_failures;
  }

  /**
   * Get the number of items processed by harvest.
   *
   * @return int
   *   Number of items processed.
   */
  public function countProcessed(): int {

    $ids = [];

    if (isset($this->result['status']['load'])) {
      $ids = array_merge($ids, array_keys($this->result['status']['load']));
    }

    if (isset($this->result['status']['transform'])) {
      foreach (array_keys($this->result['status']['transform']) as $transformer) {
        $ids = [
          ...$ids,
          ...array_keys($this->result['status']['transform'][$transformer]),
        ];
      }
    }

    $ids = array_unique($ids);

    return count($ids);
  }

  /**
   * Calculate number of results.
   *
   * @return int
   *   Number of results.
   */
  private function loadCount(string $status): int {
    $count = 0;
    if (!isset($this->result['status']['load'])) {
      return $count;
    }

    foreach ($this->result['status']['load'] as $stat) {
      if ($stat == $status) {
        $count++;
      }
    }

    return $count;
  }

  /**
   * Calculate number of failures.
   *
   * @return int
   *   Number of failures.
   */
  private function transformFailures(): int {
    $count = 0;

    if (!isset($this->result['status']['transform'])) {
      return $count;
    }

    foreach ($this->result['status']['transform'] as $transform) {
      $count += array_sum(array_map(function ($transform_status) {
        return $transform_status == "FAILURE" ? 1 : 0;
      }, $transform));
    }

    return $count;
  }

}
