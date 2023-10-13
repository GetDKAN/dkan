<?php

namespace Drupal\common\FileFetcher;

use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use FileFetcher\FileFetcher;

/**
 * A file fetcher that prevents itself from being stored.
 *
 * Since we require the Result status information for various status displays,
 * and since we might require that before there's anything to report, we should
 * not store the status of fetching the file before the file has even started
 * being fetched.
 */
class FileFetcherStatus extends FileFetcher {

  /**
   * {@inheritDoc}
   */
  public static function get(string $identifier, $storage, array $config = NULL) {
    if ($storage instanceof StorerInterface && $storage instanceof RetrieverInterface) {
      $new = new static($identifier, $storage, $config);

      $json = $storage->retrieve($identifier);
      if ($json) {
        return static::hydrate($json, $new);
      }
      // The difference here from parent::get() is that we don't store the newly
      // hydrated object back to the jobstore.
      return $new;
    }
    return FALSE;
  }

  protected function runIt() {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

  protected function setError($message) {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

  // protected function setProcessors($config) {
  //    throw new \BadMethodCallException('This file fetcher is read-only. ' . print_r(debug_backtrace()));
  //  }

  /**
   * @param $state
   * @return mixed
   */
  protected function setState($state) {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

  protected function setStatus($status) {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

  public function setTimeLimit(int $seconds) : bool {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

  public function setStateProperty($property, $value) {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

  protected function selfStore() {
    throw new \BadMethodCallException('This file fetcher is read-only.');
  }

}
