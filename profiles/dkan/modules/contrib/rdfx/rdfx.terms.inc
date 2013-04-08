<?php

/**
 * @file
 * Functions for managing RDF Terms.
 */

function rdfx_get_properties() {
  $property_tids = _rdfx_get_terms('property');
  return $property_tids;
}

function rdfx_get_classes() {
  $class_tids = _rdfx_get_terms('class');
  return $class_tids;
}

function _rdfx_get_terms($term_type) {
  $term_types = rdfx_term_types();
  switch ($term_type) {
    case 'property':
      $types = array_keys($term_types['properties']['term_types']);
      break;
    case 'class':
      $types = array_keys($term_types['classes']['term_types']);
      break;
    default:
      $types = array_merge(array_keys($term_types['properties']['term_types']), array_keys($term_types['classes']['term_types']));
  }
  $query = db_select('rdfx_term_types', 'rdftt')
    ->fields('rdft', array('tid'))
    ->condition('rdftt.type', $types, 'IN');
  $query->join('rdfx_terms', 'rdft', 'rdftt.tid = rdft.tid');
  $query->join('rdfx_namespaces', 'rdfns', 'rdfns.nsid = rdft.nsid');
  $query->join('rdfx_vocabulary_graphs', 'rdfvg', 'rdfvg.main_ns = rdfns.nsid');
  $terms = $query->execute()->fetchCol();
  return $terms;
}

/**
 * Gets a list of all defined namespaces.
 */
function rdfx_get_namespaces() {
  $rdf_namespaces = &drupal_static(__FUNCTION__);
  if (empty($rdf_namespaces)) {
    $rdf_namespaces = rdf_get_namespaces();
  }
  return $rdf_namespaces;
}

/**
 * Implements hook_rdf_namespaces.
 */
function rdfx_rdf_namespaces() {
  // Starts with some additionnal common namespaces which core does not include.
  $ns_mappings = array(
    'owl'      => 'http://www.w3.org/2002/07/owl#',
    'rdf'      => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rss'      => 'http://purl.org/rss/1.0/',
    // url() does not support empty fragment.
    'site'     => url('ns', array('absolute' => TRUE)) . '#',
  );

  // Gets the custom namespaces stored in the database.
  $query = db_select('rdfx_vocabulary_graphs', 'g');
  $query->fields('n', array('prefix', 'uri'));
  $query->join('rdfx_namespaces', 'n', 'g.main_ns = n.nsid');
  $query->orderBy('n.prefix');
  $namespaces = $query->execute()->fetchAllKeyed();
  foreach ($namespaces as $prefix => $uri) {
    $ns_mappings[$prefix] = $uri;
  }

  return $ns_mappings;
}

/**
 * Saves the main namespace mapping for a vocabulary graph and the additional
 * namespace mappings as defined in the document.
 */
function _rdfx_save_vocabulary($main_ns, $main_ns_prefix, $vocabulary) {
  $current_time = REQUEST_TIME;
  // If the vocabulary URI matches the main_ns of a vocabulary source, then
  // this is an update to that record. Otherwise, this is a newly imported
  // source.
  $gid = rdfx_get_gid($main_ns);

  // If there is an existing vocabulary, make sure that the main_ns is in the
  // namespaces array and that the user defined mapping is the last in the
  // array so the prefix reflects the user definition. Also change the
  // vocabulary graph updated date.
  if ($gid) {
    $vocabulary['namespaces'][$main_ns_prefix] = $main_ns;
    db_update('rdfx_vocabulary_graphs')
      ->fields(array('date_updated' => $current_time,))
      ->execute();
  }

  // If this is a new vocabulary, create a graph with a main namespace and
  // add the additional namespaces.
  else {
    // @todo If the vocab URI isn't used in any terms, don't add it to ns table.
    // This may happen where multiple files are defining a vocabulary.

    // @todo This should be handled as a transaction in case there is an error
    // in the middle. If there is an error, then there will be an SQL error
    // when the user retries the import.

    // Insert this namespace to get the nsid. The vocabulary_source entry will
    // point to this nsid for the main_ns. We temporarily insert 0 for the gid,
    // then update when we have the real gid.
    $nsid = db_insert('rdfx_namespaces')
      ->fields(array('uri' => $main_ns, 'prefix' => $main_ns_prefix, 'gid' => '0'))
      ->execute();
    $gid = db_insert('rdfx_vocabulary_graphs')
      ->fields(array(
          'main_ns' => $nsid,
          'date_created' => $current_time,
          'date_updated' => $current_time,))
      ->execute();
    db_update('rdfx_namespaces')
      ->condition('nsid', $nsid)
      ->fields(array('gid' => $gid))
      ->execute();
  }
  // Insert/update the vocabulary title.
  if (count($vocabulary['title']) > 0) {
    foreach ($vocabulary['title'] as $langcode => $title) {
      $query = db_merge('rdfx_vocabulary_details')
        ->key(array('gid' => $gid, 'language' => $langcode))
        ->fields(array('language' => $langcode, 'label' => $title));
      $status = $query->execute();
    }
  }

  // Insert/update the vocabulary description.
  if (count($vocabulary['description']) > 0) {
    foreach ($vocabulary['description'] as $langcode => $description) {
      $query = db_merge('rdfx_vocabulary_details')
        ->key(array('gid' => $gid, 'language' => $langcode))
        ->fields(array('language' => $langcode, 'description' => $description));
      $status = $query->execute();
    }
  }

  // Insert/update the other namespace mappings used in this vocabulary graph.
  if (count($vocabulary['namespaces']) > 0) {
    foreach ($vocabulary['namespaces'] as $prefix => $namespace) {
      if ($namespace != $main_ns) {
        $query = db_merge('rdfx_namespaces')
          ->key(array('gid' => $gid, 'uri' => $namespace))
          ->fields(array('uri' => $namespace, 'prefix' => $prefix, 'gid' => $gid))
          ->updateFields(array('prefix' => $prefix));
        $status = $query->execute();
      }
    }
  }

  $nsids = rdfx_get_nsids($main_ns);
  return $nsids;
}

/**
 * Saves vocabulary terms.
 */
function rdfx_save_terms($vocabulary_uri, $prefix, $vocabulary) {
  $nsids = _rdfx_save_vocabulary($vocabulary_uri, $prefix, $vocabulary);
  foreach ($vocabulary['terms'] as $term_type => $terms) {
    foreach ($terms as $term_uri => $term_description) {
      list($term_ns, $term_local_name) = rdfx_split_uri($term_uri);
      if (isset($nsids[$term_ns])) {
        $nsid = $nsids[$term_ns];
      }
      else {
        // If the namespace wasn't mapped to a prefix in the source graph, we
        // didn't save it to the namespaces table, so we need to add an entry.

        // @todo For the prefix value, we save the URI... should this be changed?
        $gid = rdfx_get_gid($vocabulary_uri);
        $nsid = db_insert('rdfx_namespaces')
          ->fields(array('uri' => $term_ns, 'prefix' => $term_ns, 'gid' => $gid))
          ->execute();
        $nsids[$term_ns] = $nsid;
      }

      // Get the tid of this term, saving to {rdfx_terms} if not already there.
      $tid = db_query("SELECT tid FROM {rdfx_terms} WHERE nsid = :nsid AND local_name = :localname", array(':nsid' => $nsid, ':localname' => $term_local_name))->fetchField();
      if ($tid == NULL) {
        $tid = db_insert('rdfx_terms')
          ->fields(array('nsid', 'local_name'))
          ->values(array(
            'nsid' => $nsid,
            'local_name' => $term_local_name,
          ))
          ->execute();
      }

      // Add the current type to this term in {rdfx_term_types}.
      db_merge('rdfx_term_types')
        ->key(array('tid' => $tid, 'type' => $term_type))
        ->fields(array(
            'tid' => $tid,
            'type' => $term_type,
        ))
        ->execute();

      // Add label and comment to {rdfx_term_details}.
      $term_details = array();
      if (isset($term_description['label'])) {
        foreach ($term_description['label'] as $lang => $text) {
          $term_details[$lang]['label'] = $text;
        }
      }
      if (isset($term_description['comment'])) {
        foreach ($term_description['comment'] as $lang => $text) {
          $term_details[$lang]['comment'] = $text;
        }
      }
      if (!empty($term_details)) {
        foreach ($term_details as $lang => $details) {
          db_merge('rdfx_term_details')
            ->key(array('tid' => $tid, 'language' => $lang))
            ->fields(array(
                'tid' => $tid,
                'language' => $lang,
                'label' => isset($details['label']) ? $details['label'] : NULL,
                'comment' => isset($details['comment']) ? $details['comment'] :  NULL,
            ))
            ->execute();
        }
      }

      // Add relationships to their respective tables. This is handled as a
      // complicated set of loops to reduce code duplication. To add a new
      // relationship, just add the array key that is used in
      // $types['properties']['description'] to define the relationship, and
      // then add the name of the table that stores the relationship.
      $relationships = array(
        'domain' => 'rdfx_term_domains',
        'range' => 'rdfx_term_ranges',
      );
      foreach ($relationships as $relationship => $table_name) {
        if (isset($term_description[$relationship])) {
          foreach ($term_description[$relationship] as $related_term) {
            $related_term_tid = rdfx_get_tid($related_term, $vocabulary_uri);
            if ($related_term_tid) {
              db_merge($table_name)
                ->key(array('tid' => $tid, $relationship . '_tid' => $related_term_tid))
                ->fields(array(
                    'tid' => $tid,
                    $relationship . '_tid' => $related_term_tid,
                ))
                ->execute();
            }
          }
        }
      }
    }
  }
  // @todo Add a hook that passes the $vocabulary and the $model.
}

/**
 * Returns metadata about term types defined by rdf modules.
 *
 * If your module needs to determine what term types are being supplied by
 * other modules, call this function. Querying rdfx database tables directly
 * for this information is discouraged. Any additional term types should be
 * added through the corresponding alter hook.
 *
 * Three major bins of data are stored: tags, value_types, and functions. Each
 * entry in these bins is keyed by the value stored in the actual VotingAPI
 * tables, and contains an array with (minimally) 'name' and 'description' keys.
 * Modules can add extra keys to their entries if desired.
 *
 * This metadata can be modified or expanded using hook_rdfx_term_types_alter().
 *
 * @return
 *   An array of metadata defined by RDFx Terms and altered by rdf modules.
 *
 * @see hook_rdfx_term_types_alter()
 *
 * Modeled on VotingAPI votingapi_metadata.
 */
function rdfx_term_types($reset = FALSE) {
  static $types;
  if ($reset || !isset($types)) {
    $types['classes']['term_types'] = array();
    $types['properties']['term_types'] = array();

    $term_details = '';

    // @todo Should the inference consider subProp and subClass relationships
    // as well. ie. should all OWL classes also have the type RDFS Class

    // @todo Switch to drupal cache
    $types['classes']['term_types'] = array(
      'rdfs_class' => array(
        'uri' => 'http://www.w3.org/2000/01/rdf-schema#Class',
        'inference' => array(
          'http://www.w3.org/2000/01/rdf-schema#subClassOf' => array(
            'subject',
            'object',
          ),
          'http://www.w3.org/2000/01/rdf-schema#domain' => array(
            'object',
          ),
          'http://www.w3.org/2000/01/rdf-schema#range' => array(
            'object',
          ),
        ),
      ),
      'owl_class' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#Class',
        'inference' => array(
          'http://www.w3.org/2002/07/owl#equivalentClass' => array(
            'subject',
            'object',
          ),
          'http://www.w3.org/2002/07/owl#disjointWith' => array(
            'subject',
            'object',
          ),
        ),
      ),
    );

    $types['properties']['term_types'] = array (
      'rdf_property' => array(
        'uri' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Property',
        'inference' => array(
          'http://www.w3.org/2000/01/rdf-schema#domain' => array(
            'subject',
          ),
          'http://www.w3.org/2000/01/rdf-schema#range' => array(
            'subject',
          ),
          'http://www.w3.org/2000/01/rdf-schema#subPropertyOf' => array(
            'subject',
            'object',
          ),
          'http://www.w3.org/2002/07/owl#equivalentProperty' => array(
            'subject',
            'object',
          ),
          'http://www.w3.org/2002/07/owl#inverseOf' => array(
            'subject',
            'object',
          ),
        ),
      ),
      'owl_property_datatype' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#DatatypeProperty',
        'inference' => array(
        ),
      ),
      'owl_property_object' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#ObjectProperty',
        'inference' => array(
        ),
      ),
      'owl_property_functional' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#FunctionalProperty',
        'inference' => array(
        ),
      ),
      'owl_property_inverse_functional' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#InverseFunctionalProperty',
        'inference' => array(
        ),
      ),
      'owl_property_symmetric' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#SymmetricProperty',
        'inference' => array(
        ),
      ),
      'owl_property_asymmetric' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#AsymmetricProperty',
        'inference' => array(
        ),
      ),
      'owl_property_annotation' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#AnnotationProperty',
        'inference' => array(
        ),
      ),
      'owl_property_reflexive' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#ReflexiveProperty',
        'inference' => array(
        ),
      ),
      'owl_property_irreflexive' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#IrreflexiveProperty',
        'inference' => array(
        ),
      ),
      'owl_property_transitive' => array(
        'uri' => 'http://www.w3.org/2002/07/owl#TransitiveProperty',
        'inference' => array(
        ),
      ),
    );

    $types['classes']['description'] = array(
      'superclass' => array(
        'http://www.w3.org/2000/01/rdf-schema#subClassOf' => array(
          'object',
        ),
      ),
      'disjoint' => array(
        'http://www.w3.org/2002/07/owl#disjointWith' => array(
          'object',
        ),
      ),
    );

    $types['properties']['description'] = array(
      'domain' => array(
        'http://www.w3.org/2000/01/rdf-schema#domain' => array(
          'object',
        ),
      ),
      'range' => array(
        'http://www.w3.org/2000/01/rdf-schema#range' => array(
          'object',
        ),
      ),
      'superproperty' => array(
        'http://www.w3.org/2000/01/rdf-schema#subPropertyOf' => array(
          'object',
        ),
      ),
      'inverse' => array(
        'http://www.w3.org/2002/07/owl#inverseOf' => array(
          'object',
        ),
      ),
    );
    drupal_alter('rdfx_term_types', $types);
  }

  return $types;
}

/**
 * Splits a URI into namespace and localpart.
 */
function rdfx_split_uri ($uri) {
  $parts = ARC2::splitURI($uri);
  return $parts;
}

function rdfx_get_tid($term_uri, $graph_main_ns) {
  $nsids = rdfx_get_nsids($graph_main_ns);
  list($term_ns, $term_local_name) = rdfx_split_uri($term_uri);
  if (isset($nsids[$term_ns])) {
    $tid = db_query("SELECT tid FROM {rdfx_terms} WHERE nsid = :nsid AND local_name = :localname", array(':nsid' => $nsids[$term_ns], ':localname' => $term_local_name))->fetchField();
    return $tid;
  }
  else {
    return NULL;
  }
}

function rdfx_get_gid($main_ns) {
  $gids = db_select('rdfx_namespaces', 'rdfns', array());
  $gids->join('rdfx_vocabulary_graphs', 'rdfvg', 'rdfvg.main_ns = rdfns.nsid');
  $gids
    ->fields('rdfns', array('gid'))
    ->condition('rdfns.uri', $main_ns);
  // @todo There should only be one result if there is a matching vocab source.
  // However, perhaps we should test to make sure and throw an error?
  $gid = $gids->execute()->fetchField();
  return $gid;
}

function rdfx_get_nsids($main_ns) {
  $gid = rdfx_get_gid($main_ns);
  $nsids = db_query("SELECT uri, nsid FROM {rdfx_namespaces} WHERE gid = :gid", array(':gid' => $gid))->fetchAllKeyed();
  return $nsids;
}

function rdfx_curie($tid) {
  $query = db_select('rdfx_terms', 'rdft')
    ->fields('rdft', array('local_name'))
    ->fields('rdfns', array('prefix'))
    ->condition('rdft.tid', $tid, '=');
  $query->join('rdfx_namespaces', 'rdfns', 'rdfns.nsid = rdft.nsid');
  $result = $query->execute()->fetch();
  $curie = $result->prefix . ':' . $result->local_name;
  return $curie;
}

function _rdfx_get_term_details($tid, $langcode = 'und') {
  $query_language = db_query("SELECT language FROM {rdfx_term_details} WHERE tid = :tid", array(':tid' => $tid));
  $languages = $query_language->fetchCol();
  if (!in_array($langcode, $languages)) {
    if (in_array('und', $languages)) {
      $langcode = 'und';
    }
    elseif (in_array('en', $languages)) {
      $langcode = 'en';
    }
    else {
      return;
    }
  }
  $query = db_select('rdfx_term_details', 'rdfd')
    ->fields('rdfd', array('label', 'comment'))
    ->condition('rdfd.tid', $tid, '=')
    ->condition('language', $langcode, '=');
  $details = $query->execute()->fetch();
  return $details;
}

/**
 * Queries a set of triples for classes and properties, and builds
 * an associative array describing the vocabulary and any
 * classes and properties found.
 *
 * @param array $model An ARC2-style array of triples an RDFS vocabulary or OWL ontology
 * @param array $namespaces Associative array of namespaces parsed from the RDF file
 * @param string $ns_prefix Namespace prefix for the vocabulary
 * @param string $ns_uri Only terms in this namespace will be considered
 * @return array Array describing the vocabulary, its classes and properties.
 */
function _rdfx_extract_schema(&$model, $namespaces, $ns_prefix, $ns_uri) {
  $vocabulary_details = _rdfx_get_vocabulary_details($model, $ns_uri);
  $terms = _rdfx_fetch_terms($model);
  $vocabulary = array(
      'uri' => $ns_uri,
      'title' => $vocabulary_details['title'],
      'description' => $vocabulary_details['description'],
      'terms' => $terms,
      'namespaces' => $namespaces,
  );
  return $vocabulary;
}

function _rdfx_fetch_terms(&$model) {
  $terms = array();
  $term_uris = array();

  // Retrieve the queries for term retrieval. This may have been modified by
  // other modules.
  $term_type_groups = rdfx_term_types();

  foreach($term_type_groups as $term_type_group => $group) {
    foreach ($group['term_types'] as $term_type => $term) {
      $query = array(
        array('?', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type', $term['uri']),
      );
      foreach ($term['inference'] as $inference_uri => $query_types) {
        foreach ($query_types as $query_type) {
          switch ($query_type) {
            case 'subject':
              $query[] = array('?', $inference_uri, null);
              break;
            case 'object':
              $query[] = array(null, $inference_uri, '?');
              break;
          }
        }
      }
      $term_uris[$term_type] = _rdfx_query_find_uris($model, $query);

      // Add term details and various relationships for each term, as defined
      // in rdfx_term_types() and altered by hook_rdfx_term_types_alter().
      $query_x = array();
      foreach ($term_uris[$term_type] as $term_uri) {

        $terms[$term_type][$term_uri] = _evoc_query_for_term_description($model, $term_uri);
        foreach ($group['description'] as $property => $queries) {
          foreach ($queries as $predicate_uri => $query_types) {
            foreach ($query_types as $query_type) {
              switch ($query_type) {
                case 'subject':
                  $query_x[$term_uri][$property][] = array('?', $predicate_uri, $term_uri);
                  break;
                case 'object':
                  $query_x[$term_uri][$property][] = array($term_uri, $predicate_uri, '?');
                  break;
              }
            }
          }
          $terms[$term_type][$term_uri][$property] = _rdfx_query_find_uris($model, $query_x[$term_uri][$property]);
        }
      }
    }
  }
  return $terms;
}

function _rdfx_get_vocabulary_details(&$model, $ns_uri) {
  $query_predicates = array(
    'title' => array(
      'http://www.w3.org/2000/01/rdf-schema#label',
      'http://purl.org/dc/elements/1.1/title',
      'http://purl.org/dc/terms/title',
    ),
    'description' => array(
      'http://www.w3.org/2000/01/rdf-schema#comment',
      'http://purl.org/dc/elements/1.1/description',
      'http://purl.org/dc/terms/description',
    ),
  );

  if (substr($ns_uri, -1) == '#') {
    $uri = substr($ns_uri, 0, -1);
  }

  foreach ($query_predicates as $query_element => $predicates) {
    foreach ($predicates as $predicate) {
      $queries[$query_element][] = array($ns_uri, $predicate, '?');
//       if ($uri !== NULL) {
//         $queries[$query_element][] = array($uri, $predicate, '?');
//       }
    }
    $details[$query_element] = _rdfx_query_find_literal($model, $queries[$query_element]);
  }
  return $details;
}
