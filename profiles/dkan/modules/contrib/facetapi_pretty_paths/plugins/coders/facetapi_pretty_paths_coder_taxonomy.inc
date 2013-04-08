<?php

/**
 * @file
 * A taxonomy specific coder for pretty paths.
 */

/**
 * Taxonomy specific implementation of FacetApiPrettyPathsCoder.
 */
class FacetApiPrettyPathsCoderTaxonomy extends FacetApiPrettyPathsCoderDefault {

  /**
   * Taxonomy special case: <alias>/<term-name>-<term-id>
   *
   * @see FacetApiPrettyPathsCoderDefault::encodePathSegment()
   */
  public function encodePathSegment(array $args) {
    if ($term = taxonomy_term_load($args['segment']['value'])) {
      $args['segment']['value'] = $this->prettyPath($term->name) . '-' . $term->tid;
    }
    return parent::encodePathSegment($args);
  }

  /**
   * Taxonomy special case: <alias>/<term-name>-<term-id>
   *
   * @see FacetApiPrettyPathsCoderDefault::decodePathSegmentValue()
   */
  public function decodePathSegmentValue(array $args) {
    $exploded = explode('-', $args['value']);
    $args['value'] = array_pop($exploded);
    return parent::decodePathSegmentValue($args);
  }
}
