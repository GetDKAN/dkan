<?php

/**
 * @file
 * API examples for dkan_harvest.
 */

/**
 * Allows overwriding of items during a transoform.
 *
 * Current transforms include:
 *   - filter
 *   - override
 *   - def
 *   - excldue
 *   - datajsontodkan.
 *
 * @param array &$items
 *   Array of datasets or items to override.
 */
function hook_dkan_harvest_transform_TRANSFORM_NAME(&$items) {
  foreach ($items as $item) {
    if ($item->title == 'Evil Datasets Are Good') {
      $item->title = 'Evil Datasets are Evil';
    }
  }
}
