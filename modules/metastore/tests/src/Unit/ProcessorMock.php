<?php

namespace Drupal\Tests\metastore\Unit;

use FileFetcher\Processor\ProcessorInterface;
use FileFetcher\TemporaryFilePathFromUrl;
use Procrastinator\Result;

/**
 *
 */
class ProcessorMock implements ProcessorInterface {

  use TemporaryFilePathFromUrl;

  /**
   *
   */
  public function isServerCompatible(array $state): bool {
    return TRUE;
  }

  /**
   * Setter.
   */
  public function setupState(array $state): array {
    $state['destination'] = $this->getTemporaryFilePath($state);
    $state['temporary'] = TRUE;
    return $state;
  }

  /**
   *
   */
  public function copy(
    array $state,
    Result $result,
    int $timeLimit = PHP_INT_MAX
  ): array {
    $result->setStatus(Result::DONE);
    return [
      'state' => $state,
      'result' => $result,
    ];
  }

  /**
   *
   */
  public function isTimeLimitIncompatible(): bool {
    return TRUE;
  }

}
