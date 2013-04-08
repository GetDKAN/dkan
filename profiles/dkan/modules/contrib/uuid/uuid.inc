<?php

/**
 * @file
 * Handling of universally unique identifiers.
 */

/**
 * Pattern for detecting a valid UUID.
 */
define('UUID_PATTERN', '[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}');

/**
 * Generates an universally unique identifier.
 *
 * This function first checks for the PECL alternative for generating
 * universally unique identifiers. If that doesn't exist, then it falls back on
 * PHP for generating that.
 *
 * @return
 *   An UUID, made up of 32 hex digits and 4 hyphens.
 */
function uuid_generate() {
  $callback = drupal_static(__FUNCTION__);

  if (empty($callback)) {
    if (function_exists('uuid_create') && !function_exists('uuid_make')) {
      $callback = '_uuid_generate_pecl';
    }
    elseif (function_exists('com_create_guid')) {
      $callback = '_uuid_generate_com';
    }
    else {
      $callback = '_uuid_generate_php';
    }
  }
  return $callback();
}

/**
 * Generate all missing UUIDs.
 */
function uuid_sync_all() {
  module_invoke_all('uuid_sync');
}

/**
 * Generates a UUID URI for an entity.
 *
 * @param object $entity
 *   The entity to use for generating the UUID URI.
 * @param string $entity_type
 *   The type of entity being used.
 *
 * @return string
 *  The generated UUID URI or normal URI if entity doesn't support UUIDs.
 */
function uuid_entity_uuid_uri($entity, $entity_type) {
  $entity_info = entity_get_info($entity_type);

  if (empty($entity_info['uuid'])) {
    $uri = $entity_info['uri callback']($entity);
    return $uri['path'];
  }

  if (isset($entity_info['uuid uri callback'])) {
    return $entity_info['uuid uri callback']($entity);
  }

  return "uuid/{$entity_type}/" . $entity->{$entity_info['entity keys']['uuid']};
}

/**
 * Converts an ID URI string to an entity data array.
 *
 * @see uuid_id_uri_array_to_data()
 *
 * @param string $uri
 *  The URI to convert.
 *
 * @return array
 *  The entity data.
 */
function uuid_id_uri_to_data($uri) {
  $parts = explode('/', $uri);
  return uuid_id_uri_array_to_data($parts);
}

/**
 * Converts a URI array to entity data array.
 *
 * @param array $uri
 *  The URI parts, often taken from arg().
 *
 * @return array
 *  The entity data.
 */
function uuid_id_uri_array_to_data($uri) {
  $data = array(
    'request' => $uri,
    'entity_type' => $uri[0],
    'id' => $uri[1],
  );

  drupal_alter('uuid_id_uri_data', $data);

  return $data;
}

/**
 * Converts a UUID URI string to an entity data array.
 *
 * @see uuid_uri_array_to_data()
 *
 * @param string $uri
 *  The URI to convert.
 *
 * @return array
 *  The entity data.
 */
function uuid_uri_to_data($uri, $strip_uuid = TRUE) {
  return uuid_uri_array_to_data(explode('/', $uri));
}

/**
 * Converts a URI array to entity data array.
 *
 * @param array $uri
 *  The URI parts, often taken from arg().
 *
 * @return array
 *  The entity data.
 */
function uuid_uri_array_to_data($uri, $strip_uuid = TRUE) {
  if ($strip_uuid) {
    array_shift($uri);
  }

  $data = array(
    'request' => $uri,
    'entity_type' => $uri[0],
    'uuid' => $uri[1],
  );

  drupal_alter('uuid_uri_data', $data);

  return $data;
}

/**
 * Generates a UUID using the Windows internal GUID generator.
 *
 * @see http://php.net/com_create_guid
 */
function _uuid_generate_com() {
  // Remove {} wrapper and make lower case to keep result consistent.
  return drupal_strtolower(trim(com_create_guid(), '{}'));
}

/**
 * Generates an universally unique identifier using the PECL extension.
 */
function _uuid_generate_pecl() {
  return uuid_create(UUID_TYPE_DEFAULT);
}

/**
 * Generates a UUID v4 using PHP code.
 * 
 * Based on code from @see http://php.net/uniqid#65879 , but corrected.
 */
function _uuid_generate_php() {
  // The field names refer to RFC 4122 section 4.1.2.
  return sprintf('%04x%04x-%04x-4%03x-%04x-%04x%04x%04x',
    // 32 bits for "time_low".
    mt_rand(0, 65535), mt_rand(0, 65535),
    // 16 bits for "time_mid".
    mt_rand(0, 65535),
    // 12 bits after the 0100 of (version) 4 for "time_hi_and_version".
    mt_rand(0, 4095),
    bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '10', 0, 2)),
    // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
    // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
    // 8 bits for "clk_seq_low" 48 bits for "node".
    mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
  );
}


// This is wrapped in an if block to avoid conflicts with PECL's uuid_is_valid().
/**
 * Check that a string appears to be in the format of a UUID.
 *
 * @param $uuid
 *  The string to test.
 *
 * @return
 *   TRUE if the string is well formed.
 */
if (!function_exists('uuid_is_valid')) {
  function uuid_is_valid($uuid) {
    return preg_match('/^' . UUID_PATTERN . '$/', $uuid);
  }
}
