<?php

declare(strict_types = 1);

namespace Drupal\dkan_api\Storage;

use Drupal\node\Entity\Node;

use stdClass;

/**
 * Replaces human-readable theme values with their corresponding uuids.
 *
 * @package Drupal\dkan_api\Storage
 */
class ThemeValueReferencer {

  /**
   * Returns the uuid references for all themes values.
   *
   * @param \stdClass $data
   *   The object from the json data string.
   *
   * @return mixed
   *   An array of uuid, or NULL.
   */
  public function reference(stdClass $data) {
    if (!isset($data->theme) || !is_array($data->theme)) {
      return NULL;
    }
    $themes = [];
    foreach ($data->theme as $theme) {
      $uuid = $this->referenceSingle($theme);
      if (!$uuid) {
        $uuid = $this->generateUuid($theme);
      }
      // Return the existing or generated uuid, if not keep the original value.
      $themes[] = $uuid ?: $theme;
    }
    return $themes;
  }

  /**
   * Returns a single uuid reference for a particular theme value.
   *
   * If a corresponding existing uuid is not found, a theme data item is saved
   * and its uuid returned.
   *
   * @param string $theme
   *   Human-readable theme value.
   *
   * @return mixed
   *   string containing uuid, or NULL.
   */
  protected function referenceSingle(string $theme) {
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'field_data_type' => "theme",
        'title' => $theme,
      ]);

    if ($node = reset($nodes)) {
      return $node->uuid->value;
    }
    return NULL;
  }

  /**
   * Generate and save a json theme item.
   *
   * @param string $theme
   *   Human-readable theme value.
   *
   * @return string
   *   The new theme data item's uuid.
   */
  protected function generateUuid(string $theme) {
    $today = date('Y-m-d');

    // Create theme json.
    $data = new stdClass();
    $data->title = $theme;
    $data->identifier = \Drupal::service('uuid')->generate();
    $data->created = $today;
    $data->modified = $today;

    // Create new data node for this theme.
    $node = NODE::create([
      'title' => $theme,
      'type' => 'data',
      'uuid' => $data->identifier,
      'field_data_type' => 'theme',
      'field_json_metadata' => json_encode($data),
    ]);
    $node->save();

    return $node->uuid();
  }

  /**
   * Returns the human-readable theme values from uuids.
   *
   * @param \stdClass $data
   *   The object from the json data string.
   *
   * @return mixed
   *   An array of theme values, or NULL.
   */
  public function dereference(stdClass $data) {
    if (!isset($data->theme) || !is_array($data->theme)) {
      return NULL;
    }
    $themes = [];
    foreach ($data->theme as $theme) {
      $themes[] = $this->dereferenceSingle($theme);
    }

    return !empty($themes) ? $themes : NULL;
  }

  /**
   * Returns the human-readable theme value from its uuid.
   *
   * @param string $str
   *   The string could either be a uuid or a human-readable theme value.
   *
   * @return string
   *   The theme value.
   */
  protected function dereferenceSingle(string $str) {
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'field_data_type' => "theme",
        'uuid' => $str,
      ]);
    if ($node = reset($nodes)) {
      return $node->title->value;
    }
    return $str;
  }

}
