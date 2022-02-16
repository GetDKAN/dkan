<?php

namespace Drupal\common\Util;

/**
 * Helper class to modify the API docs' path.
 */
class ApiDocsPathModifier {

  /**
   * Prepend a path fragment to paths in an API docs spec.
   *
   * @param array $spec
   *   Original spec.
   * @param string $pathFragment
   *   What we want to prepend to the urls.
   *
   * @return array
   *   Spec with modified paths.
   */
  public static function prepend(array $spec, string $pathFragment): array {

    $newPaths = [];
    foreach ($spec['paths'] as $oldPath => $value) {
      $newPaths[$pathFragment . $oldPath] = $value;
    }
    $spec['paths'] = $newPaths;

    return $spec;
  }

}
