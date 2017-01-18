<?php

/**
 * @file
 * Widget_term_icons.inc.
 */

/**
 * Widget that renders facets as a list of clickable links.
 */
class FacetapiTermIcon extends FacetapiWidgetLinks {

  /**
   * Implement execute method.
   */
  public function execute() {
    $element = &$this->build[$this->facet['field alias']];

    // Sets each item's theme hook, builds item list.
    $this->setThemeHooks($element);
    $element = array(
      '#theme' => 'item_list',
      '#items' => $this->buildListItems($element),
      '#attributes' => $this->build['#attributes'],
    );
  }

  /**
   * Implement buildListItem method.
   */
  public function buildListItems($build) {
    $settings = $this->settings->settings;

    // Initializes links attributes, adds rel="nofollow" if configured.
    $attributes = ($settings['nofollow']) ? array('rel' => 'nofollow') : array();
    $attributes += array('class' => $this->getItemClasses());

    // Builds rows.
    $items = array();
    foreach ($build as $value => $item) {
      $row = array('class' => array());
      $icon = '';
      $term = taxonomy_term_load($item['#indexed_value']);
      if (is_object($term)) {
        if (!empty($term->field_image)) {
          $uri = $term->field_image['und'][0]['uri'];
          if ($uri) {
            $icon = '<img src="' . image_style_url('small_topics_icons', $uri) . '">';
          }
        }
        else {
          $font_icon = field_get_items('taxonomy_term', $term, 'field_topic_icon');
          $i = field_view_field('taxonomy_term', $term, 'field_topic_icon', $font_icon, $langcode = NULL);
          if ($i) {
            $icon = drupal_render($i);
          }
        }
      }
      // Initializes variables passed to theme hook.
      $variables = array(
        'text' => $icon . $item['#markup'],
        'path' => $item['#path'],
        'count' => $item['#count'],
        'options' => array(
          'attributes' => $attributes,
          'html' => TRUE,
          'query' => $item['#query'],
        ),
      );

      // Adds the facetapi-zero-results class to items that have no results.
      if (!$item['#count']) {
        $variables['options']['attributes']['class'][] = 'facetapi-zero-results';
      }

      // Add an ID to identify this link.
      $variables['options']['attributes']['id'] = drupal_html_id('facetapi-link');

      // If the item has no children, it is a leaf.
      if (empty($item['#item_children'])) {
        $row['class'][] = 'leaf';
      }
      else {
        // If the item is active or the "show_expanded" setting is selected,
        // show this item as expanded so we see its children.
        if ($item['#active'] || !empty($settings['show_expanded'])) {
          $row['class'][] = 'expanded';
          $row['children'] = $this->buildListItems($item['#item_children']);
        }
        else {
          $row['class'][] = 'collapsed';
        }
      }

      // Gets theme hook, adds last minute classes.
      $class = ($item['#active']) ? 'facetapi-active' : 'facetapi-inactive';
      $variables['options']['attributes']['class'][] = $class;

      // Themes the link, adds row to items.
      $row['data'] = theme($item['#theme'], $variables);
      $items[] = $row;
    }

    return $items;
  }

}
