<?php

namespace Drupal\dkan_common;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Json Response Trait.
 */
trait JsonResponseTrait {

  /**
   * Private.
   */
  private function getResponse($message, int $code = 200): JsonResponse {
    return new JsonResponse($message, $code, ["Access-Control-Allow-Origin" => "*"]);
  }

  /**
   * Private.
   */
  private function getResponseFromException(\Exception $e, int $code = 400):JsonResponse {
    return $this->getResponse((object) ['message' => $e->getMessage()], $code);
  }

}
