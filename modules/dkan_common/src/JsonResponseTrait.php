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
   * Private.
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
      $error = $e->getResult()->error();
      if ($error) {
        // Walk to a leaf — v2's root error is a container keyword (e.g.
        // `properties`); the actionable error lives at a leaf.
        while (!empty($subs = $error->subErrors())) {
          $error = $subs[0];
        }
        // Emit the historical v1/m1x0n shape so existing downstream
        // consumers (frontend, external API clients) parse the response
        // unchanged across the opis v1 -> v2 migration. Message wording
        // comes from opis v2 templates and may differ slightly from v1.
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
