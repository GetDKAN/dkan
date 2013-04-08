<?php
/**
 * @file
 * uuid_default_entities_example.features.uuid_entities.inc
 */

/**
 * Implements hook_uuid_default_entities().
 */
function uuid_default_entities_example_uuid_default_entities() {
  $entities = array();

  $entities['deploy_example_plan'][] = (object) array(
    '__metadata' => array(
      'type' => 'taxonomy_term',
      'uri' => 'taxonomy_term/bcb92ce8-2236-e264-65c8-0c163ae716d1',
      'cause' => 'node/b0558664-c94b-3674-d9df-3e1696b2e471',
    ),
    'description' => NULL,
    'format' => NULL,
    'name' => 'Foo',
    'path' => array(
      'alias' => 'term/foo',
    ),
    'rdf_mapping' => array(
      'rdftype' => array(
        0 => 'skos:Concept',
      ),
      'name' => array(
        'predicates' => array(
          0 => 'rdfs:label',
          1 => 'skos:prefLabel',
        ),
      ),
      'description' => array(
        'predicates' => array(
          0 => 'skos:definition',
        ),
      ),
      'vid' => array(
        'predicates' => array(
          0 => 'skos:inScheme',
        ),
        'type' => 'rel',
      ),
      'parent' => array(
        'predicates' => array(
          0 => 'skos:broader',
        ),
        'type' => 'rel',
      ),
    ),
    'uuid' => 'bcb92ce8-2236-e264-65c8-0c163ae716d1',
    'vocabulary_machine_name' => 'tags',
    'weight' => '0',
  );
  $entities['deploy_example_plan'][] = (object) array(
    '__metadata' => array(
      'type' => 'taxonomy_term',
      'uri' => 'taxonomy_term/4293a15c-531a-6164-7d1b-668ed019a6bd',
      'cause' => 'node/b0558664-c94b-3674-d9df-3e1696b2e471',
    ),
    'description' => NULL,
    'format' => NULL,
    'name' => 'Bar',
    'rdf_mapping' => array(
      'rdftype' => array(
        0 => 'skos:Concept',
      ),
      'name' => array(
        'predicates' => array(
          0 => 'rdfs:label',
          1 => 'skos:prefLabel',
        ),
      ),
      'description' => array(
        'predicates' => array(
          0 => 'skos:definition',
        ),
      ),
      'vid' => array(
        'predicates' => array(
          0 => 'skos:inScheme',
        ),
        'type' => 'rel',
      ),
      'parent' => array(
        'predicates' => array(
          0 => 'skos:broader',
        ),
        'type' => 'rel',
      ),
    ),
    'uuid' => '4293a15c-531a-6164-7d1b-668ed019a6bd',
    'vocabulary_machine_name' => 'tags',
    'weight' => '0',
  );
  $entities['deploy_example_plan'][] = (object) array(
    '__metadata' => array(
      'type' => 'node',
      'uri' => 'node/b0558664-c94b-3674-d9df-3e1696b2e471',
      'cause' => FALSE,
    ),
    'body' => array(
      'und' => array(
        0 => array(
          'format' => 'filtered_html',
          'summary' => '',
          'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ),
      ),
    ),
    'cid' => '0',
    'comment' => '2',
    'comment_count' => '0',
    'field_image' => array(),
    'field_tags' => array(
      'und' => array(
        0 => array(
          'tid' => 'bcb92ce8-2236-e264-65c8-0c163ae716d1',
        ),
        1 => array(
          'tid' => '4293a15c-531a-6164-7d1b-668ed019a6bd',
        ),
      ),
    ),
    'language' => 'und',
    'last_comment_name' => NULL,
    'last_comment_uid' => '1',
    'log' => '',
    'path' => array(
      'alias' => 'lorem-ipsum',
    ),
    'promote' => '1',
    'rdf_mapping' => array(
      'field_image' => array(
        'predicates' => array(
          0 => 'og:image',
          1 => 'rdfs:seeAlso',
        ),
        'type' => 'rel',
      ),
      'field_tags' => array(
        'predicates' => array(
          0 => 'dc:subject',
        ),
        'type' => 'rel',
      ),
      'rdftype' => array(
        0 => 'sioc:Item',
        1 => 'foaf:Document',
      ),
      'title' => array(
        'predicates' => array(
          0 => 'dc:title',
        ),
      ),
      'created' => array(
        'predicates' => array(
          0 => 'dc:date',
          1 => 'dc:created',
        ),
        'datatype' => 'xsd:dateTime',
        'callback' => 'date_iso8601',
      ),
      'changed' => array(
        'predicates' => array(
          0 => 'dc:modified',
        ),
        'datatype' => 'xsd:dateTime',
        'callback' => 'date_iso8601',
      ),
      'body' => array(
        'predicates' => array(
          0 => 'content:encoded',
        ),
      ),
      'uid' => array(
        'predicates' => array(
          0 => 'sioc:has_creator',
        ),
        'type' => 'rel',
      ),
      'name' => array(
        'predicates' => array(
          0 => 'foaf:name',
        ),
      ),
      'comment_count' => array(
        'predicates' => array(
          0 => 'sioc:num_replies',
        ),
        'datatype' => 'xsd:integer',
      ),
      'last_activity' => array(
        'predicates' => array(
          0 => 'sioc:last_activity_date',
        ),
        'datatype' => 'xsd:dateTime',
        'callback' => 'date_iso8601',
      ),
    ),
    'status' => '1',
    'sticky' => '0',
    'title' => 'Lorem ipsum',
    'tnid' => '0',
    'translate' => '0',
    'type' => 'article',
    'uid' => '1',
    'uuid' => 'b0558664-c94b-3674-d9df-3e1696b2e471',
  );
  $entities['deploy_example_plan'][] = (object) array(
    '__metadata' => array(
      'type' => 'taxonomy_term',
      'uri' => 'taxonomy_term/af738a46-f278-cf84-d94d-9e03879fd71e',
      'cause' => 'node/5e3d8bbe-a1f2-f2d4-fdc0-71e6c23aa837',
    ),
    'description' => NULL,
    'format' => NULL,
    'name' => 'Baz',
    'rdf_mapping' => array(
      'rdftype' => array(
        0 => 'skos:Concept',
      ),
      'name' => array(
        'predicates' => array(
          0 => 'rdfs:label',
          1 => 'skos:prefLabel',
        ),
      ),
      'description' => array(
        'predicates' => array(
          0 => 'skos:definition',
        ),
      ),
      'vid' => array(
        'predicates' => array(
          0 => 'skos:inScheme',
        ),
        'type' => 'rel',
      ),
      'parent' => array(
        'predicates' => array(
          0 => 'skos:broader',
        ),
        'type' => 'rel',
      ),
    ),
    'uuid' => 'af738a46-f278-cf84-d94d-9e03879fd71e',
    'vocabulary_machine_name' => 'tags',
    'weight' => '0',
  );
  $entities['deploy_example_plan'][] = (object) array(
    '__metadata' => array(
      'type' => 'user',
      'uri' => 'user/7cf875e6-dc15-4404-f190-5a7c3e91d14c',
      'cause' => 'node/5e3d8bbe-a1f2-f2d4-fdc0-71e6c23aa837',
    ),
    'init' => 'no@example.com',
    'language' => '',
    'mail' => 'no@example.com',
    'name' => 'mohamed',
    'pass' => '$S$DtyVr4YQkvCpofZdLT4.L23Xb6E8HUkmEgZikN919RTZXZSePwso',
    'picture' => NULL,
    'rdf_mapping' => array(
      'rdftype' => array(
        0 => 'sioc:UserAccount',
      ),
      'name' => array(
        'predicates' => array(
          0 => 'foaf:name',
        ),
      ),
      'homepage' => array(
        'predicates' => array(
          0 => 'foaf:page',
        ),
        'type' => 'rel',
      ),
    ),
    'roles' => array(
      2 => 'authenticated user',
      3 => 'administrator',
    ),
    'signature' => '',
    'signature_format' => 'filtered_html',
    'status' => '1',
    'theme' => '',
    'timezone' => 'Asia/Riyadh',
    'uuid' => '7cf875e6-dc15-4404-f190-5a7c3e91d14c',
  );
  $entities['deploy_example_plan'][] = (object) array(
    '__metadata' => array(
      'type' => 'node',
      'uri' => 'node/5e3d8bbe-a1f2-f2d4-fdc0-71e6c23aa837',
      'cause' => FALSE,
    ),
    'body' => array(
      'und' => array(
        0 => array(
          'format' => 'filtered_html',
          'summary' => '',
          'value' => 'Nunc sollicitudin justo ut augue egestas et varius quam consectetur.',
        ),
      ),
    ),
    'cid' => '0',
    'comment' => '2',
    'comment_count' => '0',
    'field_image' => array(),
    'field_tags' => array(
      'und' => array(
        0 => array(
          'tid' => 'af738a46-f278-cf84-d94d-9e03879fd71e',
        ),
      ),
    ),
    'language' => 'und',
    'last_comment_name' => NULL,
    'last_comment_uid' => '7cf875e6-dc15-4404-f190-5a7c3e91d14c',
    'log' => '',
    'promote' => '1',
    'rdf_mapping' => array(
      'field_image' => array(
        'predicates' => array(
          0 => 'og:image',
          1 => 'rdfs:seeAlso',
        ),
        'type' => 'rel',
      ),
      'field_tags' => array(
        'predicates' => array(
          0 => 'dc:subject',
        ),
        'type' => 'rel',
      ),
      'rdftype' => array(
        0 => 'sioc:Item',
        1 => 'foaf:Document',
      ),
      'title' => array(
        'predicates' => array(
          0 => 'dc:title',
        ),
      ),
      'created' => array(
        'predicates' => array(
          0 => 'dc:date',
          1 => 'dc:created',
        ),
        'datatype' => 'xsd:dateTime',
        'callback' => 'date_iso8601',
      ),
      'changed' => array(
        'predicates' => array(
          0 => 'dc:modified',
        ),
        'datatype' => 'xsd:dateTime',
        'callback' => 'date_iso8601',
      ),
      'body' => array(
        'predicates' => array(
          0 => 'content:encoded',
        ),
      ),
      'uid' => array(
        'predicates' => array(
          0 => 'sioc:has_creator',
        ),
        'type' => 'rel',
      ),
      'name' => array(
        'predicates' => array(
          0 => 'foaf:name',
        ),
      ),
      'comment_count' => array(
        'predicates' => array(
          0 => 'sioc:num_replies',
        ),
        'datatype' => 'xsd:integer',
      ),
      'last_activity' => array(
        'predicates' => array(
          0 => 'sioc:last_activity_date',
        ),
        'datatype' => 'xsd:dateTime',
        'callback' => 'date_iso8601',
      ),
    ),
    'status' => '1',
    'sticky' => '0',
    'title' => 'Nunc sollicitudin',
    'tnid' => '0',
    'translate' => '0',
    'type' => 'article',
    'uid' => '7cf875e6-dc15-4404-f190-5a7c3e91d14c',
    'uuid' => '5e3d8bbe-a1f2-f2d4-fdc0-71e6c23aa837',
  );

  return $entities;
}
