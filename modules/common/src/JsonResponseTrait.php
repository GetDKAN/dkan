<?php

namespace Drupal\common;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Json Response Trait.
 */
trait JsonResponseTrait {

  /**
   * Private.
   */
  private function getResponse($message, int $code = 200): JsonResponse {
    return new JsonResponse($message, $code, []);
  }

  /**
   * Private.
   */
  private function getResponseFromException(\Exception $e, int $code = 400):JsonResponse {
    return $this->getResponse((object) ['message' => $e->getMessage()], $code);
  }

}
