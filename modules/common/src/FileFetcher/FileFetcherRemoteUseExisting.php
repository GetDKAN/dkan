<?php

namespace Drupal\common\FileFetcher;

use FileFetcher\Processor\Remote;
use Procrastinator\Result;

/**
 * Custom remote processor for file-fetcher, never downloads file if it exists.
 *
 * @todo This is a blunt instrument for avoiding a file download during
 *   import. We should have a better way to check that the existing file is
 *   actually the file we want.
 *
 * @see \Drupal\common\FileFetcher\FileFetcherFactory::getInstance()
 */
class FileFetcherRemoteUseExisting extends Remote {

  /**
   * {@inheritDoc}
   */
  public function copy(array $state, Result $result, int $timeLimit = PHP_INT_MAX): array {
    // Always short-circuit if the file already exists.
    $existing_status = $this->discoverStatusForExistingFile($state, $result);
    if ($existing_status['result']->getStatus() === Result::DONE) {
      return $existing_status;
    }
    return parent::copy($state, $result, $timeLimit);
  }

  public function discoverStatusForExistingFile(array $state, Result $result): array {
    if (file_exists($state['destination'])) {
      $state['total_bytes_copied'] = $state['total_bytes'] = $this->getFilesize($state['destination']);
      $result->setStatus(Result::DONE);
    }
    return ['state' => $state, 'result' => $result];
  }

}
