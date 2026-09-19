<?php

namespace Drupal\dkan_datastore_preview;

/**
 * Builds user-facing messages for previews whose data is not queryable yet.
 */
interface ImportStatusMessageInterface {

  /**
   * Build a status message render array for a resource without a table.
   *
   * @param string $resource_id
   *   Resource id in "identifier__version" format.
   *
   * @return array
   *   Render array with a message describing the import state.
   */
  public function build(string $resource_id): array;

}
