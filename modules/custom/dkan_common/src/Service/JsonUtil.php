<?php

namespace Drupal\dkan_common\Service;

/**
 * Unclassified json utilities.
 *
 * @codeCoverageIgnore
 */
class JsonUtil {

  /**
   * Used primarily for decoding multi-row results from dkan storage.
   *
   * @param array $arrayOfJson
   *   Array of JSON.
   *
   * @return array
   *   Array.
   */
  public function decodeArrayOfJson(array $arrayOfJson) {

    return array_map(
          function ($row) {
              return json_decode($row);
          }, $arrayOfJson
      );

  }

}
