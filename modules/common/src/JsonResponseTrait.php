<?php

namespace Drupal\common;

use Symfony\Component\HttpFoundation\JsonResponse;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\Strategies\BestMatchError;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use RootedData\Exception\ValidationException;

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
  private function getResponseFromException(\Exception $e, int $code = 400):JsonResponse {
    $body = [
      'message' => $e->getMessage(),
      'status' => $code,
      "timestamp" => date("c"),
    ];
    if ($data = $this->getExceptionData($e)) {
      $body['data'] = $data;
    }
    return $this->getResponse((object) $body, $code);
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
