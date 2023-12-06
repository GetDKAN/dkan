<?php

namespace Drupal\custom_processor_test\FileFetcher;

use FileFetcher\Processor\ProcessorInterface;
use Procrastinator\Result;

class YesProcessor implements ProcessorInterface {

  public function isServerCompatible(array $state): bool {
    return TRUE;
  }

  public function setupState(array $state): array {
    return $state;
  }

  public function copy(array $state, Result $result, int $timeLimit = PHP_INT_MAX): array {
    $result->setStatus(Result::DONE);
    return [$state, $result];
  }

  public function isTimeLimitIncompatible(): bool {
    return FALSE;
  }

}
