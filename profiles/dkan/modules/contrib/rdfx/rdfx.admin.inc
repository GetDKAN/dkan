<?php

/**
 * Callback function for viewing all bundles' RDF mappings.
 */
function rdfx_mapping_overview() {
  $render = array();
  $entities = entity_get_info();
  $fields = field_info_instances();
  $render['tabs'] = array(
    '#type' => 'vertical_tabs',
  );

  // Create a tab for each entity.
  foreach ($entities as $entity_type => $entity) {
    $render['tabs'][$entity_type] = array(
      '#type' => 'fieldset',
      '#title' => $entity['label'],
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    // The bundle's RDF mapping array may contain mappings for entity attributes
    // that are not fields. The bundle's field array may contain fields that are
    // not in the RDF mapping array. In order to ensure we get all the available
    // fields and all the mapped entity attributes, we compare the arrays.
    foreach ($entity['bundles'] as $bundle_name => $bundle) {
      $rows = array();
      $real_fields = array();
      $fake_fields = array();
      $bundle_fields = $fields[$entity_type][$bundle_name];
      $bundle['edit_path'] = NULL;
      $rdf_mapping = $bundle['rdf_mapping'];
      if (isset($bundle['admin']['real path'])) {
        $bundle['edit_path'] = $bundle['admin']['real path'] . '/rdf';
      }

      // Set RDF type.
      if (isset($rdf_mapping['rdftype'])) {
        $rdftype = implode(', ', $rdf_mapping['rdftype']);
        unset($rdf_mapping['rdftype']);
      }
      foreach ($rdf_mapping as $field_name => $mapping_info) {
        // Gather Field API fields.
        if (isset($bundle_fields[$field_name])) {
          $real_fields[$field_name]['rdf_mapping'] = $mapping_info;
          $real_fields[$field_name]['label'] = $fields[$entity_type][$bundle_name][$field_name]['label'];
          $real_fields[$field_name]['edit_path'] = $bundle['edit_path'];
          unset($bundle_fields[$field_name]);
        }
        // Gather non-field content variables.
        else {
          $fake_fields[$field_name]['rdf_mapping'] = $mapping_info;
          $fake_fields[$field_name]['label'] = $field_name;
          $fake_fields[$field_name]['edit_path'] = ($field_name == 'title') ? $bundle['edit_path'] : NULL;
        }
      }
      // Theme this bundle's table and add it to it's parent entity's tab.
      $variables = array(
        'bundle' => $bundle,
        'rdftype' => $rdftype,
        'real_fields' => $real_fields,
        'fake_fields' => $fake_fields,
      );
      $render['tabs'][$entity_type][$bundle_name] = theme('rdfx_mapping_admin_overview', $variables);
    }
  }
  return $render;
}

/**
 * Theme function to output the table for a bundle's mappings.
 */
function theme_rdfx_mapping_admin_overview($variables) {
  $bundle_label = $variables['bundle']['label'];
  $bundle = $variables['bundle'];
  $real_fields = $variables['real_fields'];
  $fake_fields = $variables['fake_fields'];
  $rows = array();

  // Add the table header for this bundle.
  $header = array(t('Fields'), t('RDF predicates'), t('Mapping type'), t('Datatype'));
  if (module_exists('rdfui')) {
    $header[] = t('Operations');
  }

  // Display a title for each bundle above the mappings table. If RDF UI is
  // enabled, also add an 'edit' link.
  $title = "<h3>$bundle_label</h3>";
  $edit_link = (module_exists('rdfui') && !empty($bundle['edit_path'])) ? ' (' . l(t('edit'), $bundle['edit_path']) . ')' : '';
  $title .= "<p>" . t('RDF Types:') . ' ' . $variables['rdftype'] . $edit_link . '</p>';

  // List all of the Field API fields and their mappings.
  foreach ($real_fields as $name => $field) {
    $rows[] = array(
      'data' => theme('rdfx_mapping_admin_overview_row', array('field' => $field, 'field_name' => $name, 'edit_path' => $field['edit_path'])),
    );
  }

  // Add any non-Field API entity attributes.
  foreach ($fake_fields as $name => $field) {
    $rows[] = array(
      'data' => theme('rdfx_mapping_admin_overview_row', array('field' => $field, 'field_name' => $name, 'edit_path' => $field['edit_path'])),
    );
  }

  // If there are no mappings, display a message inside the table.
  if (!count($rows)) {
    $rows[] = array(
      'data' => array(array('data' => t('No mappings have been configured for this bundle.'), 'colspan' => 5))
    );
  }

  // Return the table for this bundle.
  return array(
    '#prefix' => $title,
    '#theme' => 'table',
    '#rows' => $rows,
    '#header' => $header,
  );
}

/**
 * Theme function to output a field's row in the bundle mapping table.
 */
function theme_rdfx_mapping_admin_overview_row($variables) {
  $field = $variables['field'];

  $field_label = '<strong>' . t($field['label']) . '</strong>';
  $predicates = isset($field['rdf_mapping']['predicates']) ? t(implode(', ', $field['rdf_mapping']['predicates'])) : '';
  $predicate_type = isset($field['rdf_mapping']['type']) ? check_plain($field['rdf_mapping']['type']) : 'property';
  $datatype = isset($field['rdf_mapping']['datatype']) ? $field['rdf_mapping']['datatype'] : '';

  $row = array($field_label, $predicates, $predicate_type, $datatype);

  // Add operations links only if RDF UI is enabled.
  if (module_exists('rdfui')) {
    $operations = '';
    if (isset($variables['edit_path'])) {
      // By adding the appropriate url fragment, we can open the corresponding
      // vertical tab on the RDF mapping UI page. The fragment should correspond
      // to the html id for each fieldset, which means certain characters need
      // to be replaced. These replacement patterns are from drupal_html_id().
      $id = ($variables['field_name'] == 'title') ? 'rdf-title' : $variables['field_name'];
      $id = strtr(drupal_strtolower($id), array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
      $id = preg_replace('/[^A-Za-z0-9\-_]/', '', $id);
      $id = preg_replace('/\-+/', '-', $id);
      $operations = l(t('edit'), $variables['edit_path'], array('fragment' => "edit-$id"));
    }
    $row[] = $operations;
  }

  return $row;
}

/**
 * Menu callback for viewing all declared namespaces (conflicting and non-conflicting)
 * and their prefixes.
 */
function rdfx_admin_namespaces() {
  $output = '';

  // List conflicting namespaces.
  $conflicting_namespaces = rdfx_get_conflicting_namespaces();
  if ($conflicting_namespaces) {
    $table_conflicting_namespaces = array();
    $table_conflicting_namespaces['header'] = array('Prefix', 'Conflicting Namespaces');
    foreach ($conflicting_namespaces as $prefix => $uris) {
      $table_conflicting_namespaces['rows'][] = array($prefix, implode(", ", $uris));
    }
    $output .= '<div class="messages warning">' . t("Warning: The following namespaces have conflicts") . '</div>';
    $output .= theme('table', $table_conflicting_namespaces);
  }

  // List non-conflicting namespaces.
  $table_namespaces = array();
  $table_namespaces['header'] = array('Prefix', 'Namespace');
  foreach (rdf_get_namespaces() as $prefix => $namespace) {
    $table_namespaces['rows'][] = array($prefix, $namespace);
  }
  // Only show label if there were conflicting namespaces.
  if ($conflicting_namespaces) {
    $output .= '<div class="messages status">' . t("The following namespaces do not have conflicts") . '</div>';
  }
  $output .= theme('table', $table_namespaces);

  // Form to add namespaces.
  $form = drupal_get_form('rdfx_admin_namespaces_form');
  $output .= drupal_render($form);

  return $output;
}

function rdfx_admin_namespaces_form($form, &$form_state) {
  $form['prefix'] = array(
    '#type' => 'textfield',
    '#title' => t('Prefix'),
    '#required' => TRUE,
    '#description' => t('Choose a prefix for this namespace, e.g. dc, foaf, sioc. This prefix will be used as an abbreviation for the namespace URI.'),
  );
  $form['ns_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('Namespace URI'),
    '#required' => TRUE,
    '#default_value' => isset($form_state['values']['ns_uri']) ? $form_state['values']['ns_uri'] : NULL,
    '#description' => t("Enter the URI of the namespace. Make sure it ends with either / or #."),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

function rdfx_admin_namespaces_form_validate($form, &$form_state) {
  // Loads the XML Namespace regular expression patterns.
  module_load_include('inc', 'rdfx');

  $prefix = $form_state['values']['prefix'];
  $ns_uri = $form_state['values']['ns_uri'];

  // Ensures that the namespace is a valid URI.
  if (!valid_url($ns_uri, $absolute = TRUE)) {
    form_set_error('ns_uri', t('The namespace URI must be a valid URI.'));
  }
  // Ensures the namespace URI ends in either / or #.
  if (!preg_match('/(\/|\#)$/', $ns_uri)) {
    form_set_error('ns_uri', t('The namespace URI must end in either a / or a #.'));
  }
  // Ensures the prefix is well formed according to the specification.
  if (!preg_match('/^' . PREFIX .'$/', $prefix)) {
    form_set_error('prefix', t('The prefix must follow the !link.', array('!link' => '<a href="http://www.w3.org/TR/xml-names11/#NT-NCName">XML Namespace Specification</a>')));
  }
}

function rdfx_admin_namespaces_form_submit($form, &$form_state) {
  $prefix = $form_state['values']['prefix'];
  $ns_uri = $form_state['values']['ns_uri'];
  // Prepares a fake empty vocabulary for _rdfx_save_vocabulary() to save the
  // namespace and prefix.
  // @todo use API when http://drupal.org/node/1117646 is fixed.
  $vocabulary = array(
    'title' => array(),
    'description' => array(),
    'namespaces' => array(),
  );
  _rdfx_save_vocabulary($ns_uri, $prefix, $vocabulary);
  drupal_set_message(t('The namespace @namespace has been saved with the prefix @prefix.', array('@namespace' => $ns_uri, '@prefix' => $prefix)));
}
