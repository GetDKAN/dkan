<?php

declare(strict_types = 1);

namespace Drupal\metastore\Service;

use Ramsey\Uuid\Uuid;

/**
 * Service to generate predictable uuid's.
 *
 * We cannot solely rely on the data value. A keyword and a theme described
 * by the same string must not end up being the same uuid, nor reference.
 * Therefore, we create the named identifier from concatenating the schema id
 * and the data value.
 */
class Uuid5 {

  /**
   * Generate a uuid version 5.
   *
   * @param string $schema_id
   *   The schema id of this value.
   * @param mixed $value
   *   The value for which we generate a uuid for.
   *
   * @return string
   *   The uuid.
   */
  public function generate($schema_id, $value) {
    if (!is_string($value)) {
      $value = json_encode($value, JSON_UNESCAPED_SLASHES);
    }
    $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $schema_id . ":" . $value);
    return $uuid->toString();
  }

  /**
   * Check if a string is a valid UUID.
   *
   * @param string $uuid
   *   The uuid being tested.
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function isValid(string $uuid) {
    return Uuid::isValid($uuid);
  }

}
