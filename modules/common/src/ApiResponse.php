<?php

namespace Drupal\common;

use Drupal\Core\Cache\CacheableJsonResponse;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\Strategies\BestMatchError;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use RootedData\Exception\ValidationException;

/**
 * Service to standardize building response objects for API requests.
 */
class ApiResponse {

  /**
   * Create a basic, cacheable JSON response.
   *
   * @param mixed $data
   *   Array or object that can be encoded as JSON.
   * @param int $code
   *   An HTTP response code.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   A response, ready to be returned to a route.
   */
  public function jsonResponse($data, int $code = 200):CacheableJsonResponse {
    $response = new CacheableJsonResponse($data, $code, []);
    return $response;
  }

  /**
   * Create JSON response from a caught exception.
   *
   * @param \Exception $e
   *   Exception object.
   * @param int $code
   *   HTTP response code.
   *
   * @return Drupal\Core\Cache\CacheableJsonResponse
   *   JSON response.
   */
  public function jsonResponseFromException(\Exception $e, int $code = 400):CacheableJsonResponse {
    $body = [
      'message' => $e->getMessage(),
      'status' => $code,
      "timestamp" => date("c"),
    ];
    if ($data = $this->getExceptionData($e)) {
      $body['data'] = $data;
    }
    return $this->jsonResponse((object) $body, $code);
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
  private function getExceptionData(\Exception $e) {
    if ($e instanceof ValidationException) {
      $errors = $e->getResult()->getErrors();
      $presenter = new ValidationErrorPresenter(
        new PresentedValidationErrorFactory(
          new MessageFormatterFactory()
        ),
        new BestMatchError()
      );
      $presented = $presenter->present(...$errors);
      return $presented[0];
    }

    return FALSE;
  }

}
