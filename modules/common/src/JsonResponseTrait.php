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
  private function getResponseFromException(\Exception $e, int $code = 400, $data = []):JsonResponse {
    $body = ['message' => $e->getMessage()];
    if (!empty($data) && !empty(json_encode($data))) {
      $body['data'] = $data;
    } 
    return $this->getResponse((object) $body, $code);
  }

}
