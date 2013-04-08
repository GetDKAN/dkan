<?php
/**
 * @file
 * features_test.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function features_test_taxonomy_default_vocabularies() {
  return array(
    'taxonomy_features_test' => array(
      'name' => 'Taxonomy Features Test',
      'machine_name' => 'taxonomy_features_test',
      'description' => 'Taxonomy vocabulary',
      'hierarchy' => '0',
      'module' => 'taxonomy',
      'weight' => '0',
      'rdf_mapping' => array(
        'rdftype' => array(
          0 => 'skos:ConceptScheme',
        ),
        'name' => array(
          'predicates' => array(
            0 => 'dc:title',
          ),
        ),
        'description' => array(
          'predicates' => array(
            0 => 'rdfs:comment',
          ),
        ),
      ),
    ),
  );
}
