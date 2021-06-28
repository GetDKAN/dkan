<?php

namespace Drupal\common\Controller;

use Drupal\common\Plugin\OpenApiSpec;

/**
 * Helper class to alter OpenAPI spec for only public endpoints.
 */
class AuthCleanupHelper {

  /**
   * Remove auth endpoints and cleanup unused parameters on an OpenAPI spec.
   *
   * @param Drupal\common\Plugin\OpenApiSpec $spec
   *   The original spec.
   *
   * @return \Drupal\common\Plugin\OpenApiSpec
   *   Altered spec.
   */
  public static function makePublicSpec(OpenApiSpec $spec) {
    $filteredSpec = static::removeAuthenticatedEndpoints($spec);
    $filteredSpec = static::cleanUpParameters($filteredSpec);
    $filteredSpec = static::cleanUpSchemas($filteredSpec);
    return $filteredSpec;
  }

  /**
   * Remove API spec endpoints requiring authentication.
   *
   * @param \Drupal\common\Plugin\OpenApiSpec $spec
   *   The original spec.
   *
   * @return \Drupal\common\Plugin\OpenApiSpec
   *   The modified API spec, without authenticated endpoints.
   */
  public static function removeAuthenticatedEndpoints(OpenApiSpec $spec) {
    $specArr = $spec->{"$"};
    foreach ($specArr['paths'] as $path => $methods) {
      static::removeAuthenticatedMethods($methods, $path, $specArr);
    }
    static::cleanUpEndpoints($specArr);
    unset($specArr['components']['securitySchemes']);
    if (empty($specArr['components'])) {
      unset($specArr['components']);
    }
    return new OpenApiSpec(json_encode($specArr));
  }

  /**
   * Within a path, remove methods requiring authentication.
   *
   * @param array $methods
   *   Methods for the current path.
   * @param string $path
   *   The path being processed.
   * @param array $specArr
   *   Assoc array with modified openapi spec.
   */
  private static function removeAuthenticatedMethods(array $methods, string $path, array &$specArr) {
    foreach (array_keys($methods) as $method) {
      if (isset($specArr['paths'][$path][$method]['security'])) {
        unset($specArr['paths'][$path][$method]);
      }
    }
  }

  /**
   * Clean up empty endpoints from the spec.
   *
   * @param array $specArr
   *   Assoc array with spec data, loaded by reference.
   */
  private static function cleanUpEndpoints(array &$specArr) {
    foreach ($specArr['paths'] as $path => $methods) {
      if (empty($methods)) {
        unset($specArr['paths'][$path]);
      }
    }
  }

  /**
   * Clean up unused parameters from the spec.
   *
   * @param \Drupal\common\Plugin\OpenApiSpec $spec
   *   The original spec.
   *
   * @return \Drupal\common\Plugin\OpenApiSpec
   *   The OpenAPI spec without .
   */
  public static function cleanUpParameters(OpenApiSpec $spec) {
    $specArr = $spec->{'$'};
    $usedParameters = [];
    foreach ($specArr['paths'] as $pathMethods) {
      static::getParametersFromMethods($pathMethods, $usedParameters);
    }
    if (empty($specArr["components"]) || empty($specArr["components"]["parameters"])) {
      return new OpenApiSpec(json_encode($specArr));
    }
    array_filter($specArr['components']['parameters'], function ($parameter) use ($usedParameters) {
      return in_array($parameter, $usedParameters);
    }, ARRAY_FILTER_USE_KEY);
    return new OpenApiSpec(json_encode($specArr));
  }

  /**
   * Get all used parameters from an element of the spec's paths array.
   *
   * @param array $pathMethods
   *   A single element of the paths array. Keys should be methods.
   * @param array $usedParameters
   *   Array to store the used parameters as they're found.
   */
  private static function getParametersFromMethods(array $pathMethods, array &$usedParameters) {
    foreach ($pathMethods as $method) {
      static::getParametersFromMethod($method, $usedParameters);
    }
  }

  /**
   * Get all used parameters from a method element of a single paths array.
   *
   * @param array $method
   *   A single method element (post, get etc) of the paths array.
   * @param array $usedParameters
   *   Array to store the used parameters as they're found.
   */
  private static function getParametersFromMethod(array $method, array &$usedParameters) {
    if (empty($method["parameters"])) {
      return;
    }
    foreach ($method["parameters"] as $parameter) {
      static::getUsedParameters($parameter, $usedParameters);
    }
  }

  /**
   * Figure out if a parameter is used in any refs.
   *
   * @param array $parameter
   *   A parameter array.
   * @param array $usedParameters
   *   Array of used parameter keys.
   */
  private static function getUsedParameters(array $parameter, array &$usedParameters) {
    if (isset($parameter['$ref'])) {
      $parts = explode('/', $parameter['$ref']);
      $parameterKey = end($parts);
    }
    if (isset($parameterKey) && !in_array($parameterKey, $usedParameters)) {
      $usedParameters[] = $parameterKey;
    }
  }

  /**
   * Remove unneeded schemas.
   *
   * @param Drupal\common\Plugin\OpenApiSpec $spec
   *   Full spec.
   *
   * @return Drupal\common\Plugin\OpenApiSpec
   *   Spec without unneeded schemas.
   */
  public static function cleanUpSchemas(OpenApiSpec $spec) {
    $specArr = $spec->{"$"};
    if (empty($specArr["components"]) || empty($specArr["components"]["schemas"])) {
      return $spec;
    }
    foreach (array_keys($specArr['components']['schemas']) as $schemaKey) {
      if (!static::schemaIsUsed($schemaKey, $spec)) {
        unset($specArr['components']['schemas'][$schemaKey]);
      }
    }
    return new OpenApiSpec(json_encode($specArr));
  }

  /**
   * Determine whether schema is needed.
   *
   * @param string $schemaKey
   *   Schema key from components array.
   * @param Drupal\common\Plugin\OpenApiSpec $spec
   *   Full spec.
   *
   * @return bool
   *   Whether it's used, true or false.
   */
  public static function schemaIsUsed(string $schemaKey, OpenApiSpec $spec) {
    $used = FALSE;
    $data = $spec->{'$'};
    array_walk_recursive($data, function ($value, $key) use (&$used, $schemaKey) {
      $pattern = "/^#\/components\/schemas\/$schemaKey([^a-z]|\$)/";
      if ($key == '$ref' && $used === FALSE && preg_match($pattern, $value)) {
        $used = TRUE;
      }
    });
    return $used;
  }

}
