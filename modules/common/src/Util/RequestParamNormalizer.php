<?php

namespace Drupal\common\Util;

use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestParamNormalizer.
 *
 * This class provides public static functions to normalize different HTTP
 * request types into valid JSON data. Provides type casting from a JSON Schema
 * to avoid data loss from conversion between query string and JSON.
 *
 * @package Drupal\common\Util
 */
class RequestParamNormalizer {

  /**
   * Get the JSON string from the request, with type coercion applied.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   * @param string $schema
   *   Optional JSON schema string, used to cast data types.
   *
   * @return string
   *   Normalized and type-casted JSON string.
   */
  public static function getFixedJson(Request $request, string $schema = NULL) {
    $json = self::getJson($request);
    if ($schema) {
      $json = self::fixTypes($json, $schema);
    }
    return $json;
  }

  /**
   * Just get the JSON string from the request.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   Symfony HTTP request object.
   *
   * @return string
   *   JSON string.
   *
   * @throws UnexpectedValueException
   *   When an unsupported HTTP method is passed.
   */
  public static function getJson(Request $request) {
    $method = $request->getRealMethod();
    switch ($method) {
      case "POST":
      case "PUT":
      case "PATCH":
        return $request->getContent();

      case "GET":
        return json_encode((object) $request->query->all());

      default:
        throw new \UnexpectedValueException("Only POST, PUT, PATCH and GET requests can be normalized.");
    }
  }

  /**
   * Cast data types in the JSON object according to a schema.
   *
   * @param string $json
   *   JSON string.
   * @param string $schema
   *   JSON Schema string.
   *
   * @return string
   *   JSON string with type coercion applied.
   */
  public static function fixTypes($json, $schema) {
    $data = json_decode($json);
    $validator = new Validator();
    $validator->coerce($data, json_decode($schema));
    return json_encode($data, JSON_PRETTY_PRINT);
  }

}
