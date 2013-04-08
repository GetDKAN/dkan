<?php

/**
 * @file
 * A taxonomy specific coder based on path aliases of terms.
 *
 * This handler reuses the path aliases of terms and hence avoids term ids in
 * the facet URLs. It requires the default pattern for taxonomy terms
 * ([term:vocabulary]/[term:name]), which can be configured at
 * admin/config/search/path/patterns. The alias of [term:name] will be reused
 * and combined with the facet alias ([facet:alias]/[term:name]).
 */

/**
 * Taxonomy pathauto specific implementation of FacetApiPrettyPathsCoder.
 */
class FacetApiPrettyPathsCoderTaxonomyPathauto extends FacetApiPrettyPathsCoderDefault {

  /**
   * Taxonomy pathauto special case: <facet alias>/<term-name alias>
   *
   * @see FacetApiPrettyPathsCoderDefault::encodePathSegment()
   */
  public function encodePathSegment(array $args) {
    $voc_alias = $this->getVocabularyPathAlias($args['facet'], $args['adapter']);
    if ($voc_alias) {
      if ($term = taxonomy_term_load($args['segment']['value'])) {
        // Get the alias ([term:vocabulary]/[term:name]) for this term and
        // extract the term:name part.
        $alias = drupal_lookup_path('alias', 'taxonomy/term/' . $term->tid);
        if ($alias) {
          $parts = explode('/', $alias);
          if (count($parts) == 2) {
            $args['segment']['value'] = $parts[1];
          }
        }
      }
    }
    return parent::encodePathSegment($args);
  }

  /**
   * Taxonomy pathauto special case: <facet alias>/<term-name alias>
   *
   * @see FacetApiPrettyPathsCoderDefault::decodePathSegmentValue()
   */
  public function decodePathSegmentValue(array $args) {
    $voc_alias = $this->getVocabularyPathAlias($args['facet'], $args['adapter']);
    if ($voc_alias) {
      // Rebuild the term alias, get the source (taxonomy/term/[term:tid]) and
      // extract the term id.
      $source = drupal_lookup_path('source', $voc_alias . '/' . $args['value']);
      if ($source) {
        $exploded = explode('/', $source);
        if (count($exploded) == 3) {
          $args['value'] = $exploded[2];
        }
      }
    }
    return parent::decodePathSegmentValue($args);
  }

  /**
   * Helper function that returns the path alias for a vocabulary.
   */
  private function getVocabularyPathAlias($facet_info, $adapter) {
    static $aliases = array();
    if (!isset($aliases[$facet_info['name']])) {
      $aliases[$facet_info['name']] = FALSE;
      $facet_settings = $adapter->getFacetSettingsGlobal($facet_info);
      $voc = taxonomy_vocabulary_machine_name_load($facet_settings->settings['pretty_paths_taxonomy_pathauto_vocabulary']);
      if ($voc && module_exists('pathauto')) {
        // Needed, as of http://drupal.org/node/907578#comment-5564008
        require_once drupal_get_path('module', 'pathauto') . '/pathauto.inc';
        $aliases[$facet_info['name']] = pathauto_cleanstring($voc->name);
      }
    }
    return $aliases[$facet_info['name']];
  }
}
