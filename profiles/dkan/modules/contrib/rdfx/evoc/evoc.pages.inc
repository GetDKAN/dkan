<?php

/**
 * @file
 * Page callbacks for importing a vocabulary.
 */

/**
 * Present a node submission form or a set of links to such forms.
 */
function evoc_import() {
  return drupal_get_form('evoc_import_form');
}


function evoc_import_form($form_state) {
  // Retrieves all the imported namespaces from the database.
  $query = db_select('rdfx_vocabulary_graphs', 'g');
  $query->fields('n', array('prefix', 'uri'));
  $query->join('rdfx_namespaces', 'n', 'g.main_ns = n.nsid');
  $query->orderBy('n.prefix');
  $namespaces = $query->execute()->fetchAll();

  if (count($namespaces) == 0) {
    $namespace_msg = '<p>No RDF vocabularies have been imported. It is recommended that you ' . l('import the core RDF vocabularies', 'evoc/import_core') . '.</p>';
  }
  else {
    $table_variables = array();
    foreach ($namespaces as $namespace) {
      $table_variables['rows'][] = array($namespace->prefix, $namespace->uri);
    }
    $namespace_msg = theme('table', $table_variables);
  }

  $form['help'] = array(
    '#type' => 'item',
    '#markup' => '<p>This form allows you to import external RDF vocabularies into your site.
    These can later be used by other modules such as <a href="http://drupal.org/project/rdf">RDF UI</a> or <a href="http://drupal.org/project/neologism">Neologism</a>.</p>',
  );
  $form['namespace_box'] = array(
    '#type' => 'fieldset',
    '#title' => 'Imported vocabularies',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['namespace_box']['namespaces'] = array(
    '#type' => 'item',
    '#markup' => $namespace_msg,
  );
  $form['prefix'] = array(
    '#type' => 'textfield',
    '#title' => t('Prefix'),
    '#required' => TRUE,
    '#default_value' => isset($form_state['values']['prefix']) ? $form_state['values']['prefix'] : NULL,
    '#description' => "Choose a prefix for this vocabulary. Example: dc, foaf, sioc etc. This prefix will later be used in the system to refer to this vocabulary.",
  );
  $form['ns_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('Vocabulary URI'),
    '#required' => TRUE,
    '#default_value' => isset($form_state['values']['ns_uri']) ? $form_state['values']['ns_uri'] : NULL,
    '#description' => "Enter the URI of the vocabulary to import. Make sure it finishes by either / or #.",
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Submit',
  );
  return $form;
}

function evoc_import_form_validate($form, &$form_state) {
  // Load the XML Namespace regular expression patterns.
  module_load_include('inc', 'rdfx');

  $prefix = $form_state['values']['prefix'];
  $ns_uri = $form_state['values']['ns_uri'];

  // Ensure the that namespace is a valid URI.
  if (!valid_url($ns_uri, $absolute = TRUE)) {
    form_set_error('ns_uri', t('The Vocabulary URI must be a valid URI.'));
  }
  // Ensure the namespace URI ends in either a / or a #.
  if (!preg_match('/(\/|\#)$/', $ns_uri)) {
    form_set_error('ns_uri', t('The Vocabulary URI must end in either a / or a #.'));
  }
  // Ensure the prefix is well formed according to the specification.
  if (!preg_match('/^' . PREFIX .'$/', $prefix)) {
    form_set_error('prefix', t('The prefix must follow the !link.', array('!link' => '<a href="http://www.w3.org/TR/xml-names11/#NT-NCName">XML Namespace Specification</a>')));
  }
}

function evoc_import_form_submit($form, &$form_state) {
// @todo is that still the best way to keep the value when the form is reloaded?
  $form_state['storage']['values'] = $form_state['values'];
  $form_state['rebuild'] = true;

  evoc_import_vocabulary($form_state['values']['ns_uri'], $form_state['values']['prefix']);

  drupal_set_message(t('The @voc vocabulary has been imported.', array('@voc' => $form_state['values']['prefix'])));
}

/**
 * Provide a callback for batch importing the vocabularies used in core.
 */
function evoc_import_core() {
  $vocabularies = rdf_rdf_namespaces();
  $operations = array();
  foreach ($vocabularies as $prefix => $namespace_uri) {
    $operations[] = array(
      '_evoc_install_batch_process',
      array(
        $namespace_uri,
        $prefix,
      ),
    );
  }
  $batch = array(
    'title' => t('Downloading RDF vocabularies'),
    'init_message' => t('Preparing to download the core RDF vocabularies'),
    'operations' => $operations,
    'finished' => '_evoc_install_batch_finished',
    'file' => drupal_get_path('module', 'evoc') . '/evoc.pages.inc',
  );
  batch_set($batch);
  batch_process('evoc/import');
}

/*
 * Evoc Install batch process.
 */
function _evoc_install_batch_process($namespace_uri, $prefix, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['message'] = t('Downloading %prefix', array('%prefix' => $prefix));
    $context['finished'] = 0;
    return;
  }

  evoc_import_vocabulary($namespace_uri, $prefix);

  // Store result for post-processing in the finished callback.
  $context['results'][] = $prefix;

  // Update our progress information.
  $context['sandbox']['progress']++;

  $context['finished'] = 1;
}

/**
 * Evoc Install batch 'finished' callback.
 */
function _evoc_install_batch_finished($success, $results, $operations) {
  if ($success) {
    drupal_set_message(t('The RDF vocabularies ' . implode(', ', $results) . ' have been imported.'));
    return;
  }
  else {
    drupal_set_message(t('An error occurred and processing did not complete.'), 'error');
    $message = format_plural(count($results), '1 item successfully processed:', '@count items successfully processed:');
    $message .= theme('item_list', array('items' => $results));
    drupal_set_message($message);
    return;
  }
}

