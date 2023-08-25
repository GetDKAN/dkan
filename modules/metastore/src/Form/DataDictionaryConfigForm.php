<?php

namespace Drupal\metastore\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class DataDictionaryConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_dictionary_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = NULL, $nid = NULL) {

    if ($type === 'data-dictionary') {
      // Check if this is an edit or add operation.
      if ($nid) {

        $form_state->set('nid', $nid);

        // Load the existing node.
        $node = Node::load($nid);

        $metadata = json_decode($node->get('field_json_metadata')->getString());

        // Populate form fields with existing values.
        $form['identifier'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Identifier'),
          '#default_value' => $metadata->identifier,
        ];

        $form['title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $metadata->title,
        ];
      } else {
        // For adding new nodes.
        $form['identifier'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Identifier'),
        ];

        $form['title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
        ];
      }

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ];
    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $node = NULL) {

    // Get the node ID from the URL parameters
    $nid = $form_state->get('nid');

    $field_json_metadata = [
      'identifier' => $form_state->getValue('identifier'),
      'title' => $form_state->getValue('title')
    ];

    $jsonString = json_encode($field_json_metadata);

    $node_values = [
      'type' => 'data',
      'field_json_metadata' => $jsonString,
      'field_data_type' => 'data-dictionary',
    ];

    if ($nid) {
      // If a node ID is present, update an existing node.
      $node = Node::load($nid);
      $node->set('field_json_metadata', $jsonString);
      $node->save();
    } else {
      // If no node ID is present, create a new node.
      $node = Node::create($node_values);
      $node->save();
    }

      // Redirect after saving.
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);

  }
}
