<?php

namespace Drupal\dkan_common;

use Opis\JsonSchema\Errors\ErrorFormatter;
use RootedData\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Json Response Trait.
 */
trait JsonResponseTrait {
  use CacheableResponseTrait;

  /**
   * Get a JSON response from a message, code, and headers.
   *
   * @param string|object|array $message
   *   The message to include in the response body.
   * @param int $code
   *   The HTTP status code for the response.
   * @param array $headers
   *   An array of headers to include in the response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A Symfony JSON response.
   */
  protected function getResponse($message, int $code = 200, array $headers = []): JsonResponse {
    $response = new JsonResponse($message, $code, $headers);
    return $this->addCacheHeaders($response);
  }

  /**
   * Create JSON response from a caught exception.
   *
   * @param \Exception $e
   *   Exception object.
   * @param int $code
   *   HTTP response code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A Symfony JSON response.
   */
  protected function getResponseFromException(\Exception $e, int $code = 400):JsonResponse {
    $body = [
      'message' => $e->getMessage(),
      'status' => $code,
      "timestamp" => date("c"),
    ];
    if ($data = $this->getExceptionData($e)) {
      $body['data'] = $data;
    }
    $headers = ($e instanceof HttpException) ? $e->getHeaders() : [];
    return $this->getResponse((object) $body, $code, $headers);
  }

  /**
   * See if we can present more detail about the exception.
   *
   * Currently, only RootedJsonData validation errors supported.
   *
   * @param \Exception $e
   *   Exception object.
   *
   * @return array|false
   *   An array of data to explain the errors.
   */
  protected function getExceptionData(\Exception $e) {
    if ($e instanceof ValidationException) {
      $result = $e->getResult();
      if ($result->hasError()) {
        $error = $result->error();
        // Walk to a leaf — v2's root error is a container keyword (e.g.
        // `properties`); the actionable error lives at a leaf.
        while (!empty($subs = $error->subErrors())) {
          $error = $subs[0];
        }
        // Normalize and simplify the error shape.
        $formatter = new ErrorFormatter();
        return [
          'keyword' => $error->keyword(),
          'pointer' => implode('/', $error->data()->fullPath()),
          'message' => $formatter->formatErrorMessage($error),
        ];
      }
    }

    return FALSE;
  }

}
