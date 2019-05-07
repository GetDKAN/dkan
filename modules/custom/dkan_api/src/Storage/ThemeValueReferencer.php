<?php

declare(strict_types = 1);

namespace Drupal\dkan_api\Storage;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Queue\QueueFactory;
use stdClass;

/**
 * Replaces human-readable theme values with their corresponding uuids.
 *
 * @package Drupal\dkan_api\Storage
 */
class ThemeValueReferencer {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The uuid service.
   *
   * @var Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * The queue service.
   *
   * @var Drupal\Core\Queue\QueueFactory
   */
  protected $queueService;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Injected entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UuidInterface $uuidService, QueueFactory $queueService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->uuidService = $uuidService;
    $this->queueService = $queueService;
  }

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
        $uuid = $this->createThemeReference($theme);
      }
      // Return the existing or generated uuid, if not keep the original value.
      if ($uuid) {
        $themes[] = $uuid;
      }
      else {
        $themes[] = $theme;
      }
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
    $nodes = $this->entityTypeManager
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
  protected function createThemeReference(string $theme) {
    $today = date('Y-m-d');

    // Create theme json.
    $data = new stdClass();
    $data->title = $theme;
    $data->identifier = $this->uuidService->generate();
    $data->created = $today;
    $data->modified = $today;

    // Create new data node for this theme.
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->create([
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

    if (!empty($themes)) {
      return $themes;
    }
    else {
      return NULL;
    }
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
    $nodes = $this->entityTypeManager
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

  /**
   * Queue deleted themes for processing, as they may be orphans.
   *
   * @param string $old
   *   Json string of item being replaced.
   * @param string $new
   *   Json string of item doing the replacing.
   *
   * @return int
   *   The number of items queued for processing.
   */
  public function processDeletedThemes(string $old, string $new = "{}") {
    $themes_removed = $this->themesRemoved($old, $new);

    $orphan_theme_queue = $this->queueService->get('orphan_theme_processor');
    foreach ($themes_removed as $theme_removed) {
      // @Todo: Only add to the queue when uuid doesn't already exists in it.
      $orphan_theme_queue->createItem($theme_removed);
    }
  }

  /**
   * Returns an array of theme uuid(s) being removed as the data changes.
   *
   * @param string $old
   *   Json string of item being replaced.
   * @param string $new
   *   Json string of item doing the replacing.
   *
   * @return array
   *   Array of theme uuid(s).
   */
  public function themesRemoved(string $old, string $new = "{}"): array {
    $old_data = json_decode($old);
    if (!isset($old_data->theme)) {
      // No theme to potentially delete nor check for orphan.
      return [];
    }
    $old_themes = $old_data->theme;

    $new_data = json_decode($new);
    if (!isset($new_data->theme)) {
      $new_themes = [];
    }
    else {
      $new_themes = $new_data->theme;
    }

    return array_diff($old_themes, $new_themes);
  }

}
