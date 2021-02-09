<?php

/**
 * @file
 * Contains metastore_item.page.inc.
 *
 * Page callback for Metastore item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Metastore item templates.
 *
 * Default template: metastore_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_metastore_item(array &$variables) {
  // Fetch MetastoreItem Entity Object.
  $metastore_item = $variables['elements']['#metastore_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
