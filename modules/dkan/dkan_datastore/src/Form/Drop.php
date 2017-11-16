<?php

namespace Dkan\Datastore\Form;

use Dkan\Datastore\DatastoreInterface;

module_load_include("php", "dkan_datastore", "src/DatastoreInterface");

/**
 * Class Drop.
 */
class Drop {

  private $datastore;

  /**
   * Drop constructor.
   */
  public function __construct(DatastoreInterface $datastore) {
    $this->datastore = $datastore;
  }

  /**
   * Array version of the form.
   */
  public function toArray(&$form_state) {
    $node = $this->datastore->getNode();

    $form = array();
    $form['#redirect'] = 'node/' . $node->nid;
    $form = confirm_form($form, t('Drop this datastore?'), $form['#redirect'], '', t('Drop'), t('Cancel'), 'confirm drop');

    if ($this->datastore->exists()) {
      $form['datastore_present'] = array(
        '#type' => 'markup',
        '#title' => t('Drop datastore'),
        '#tree' => TRUE,
        '#markup' => t('Are you sure you want to drop the datastore?'),
      );
      $form['actions']['submit']['#value'] = t('Drop');
    }
    else {
      $form['datastore_absent'] = array(
        '#type' => 'markup',
        '#title' => t("Can't drop the datastore"),
        '#tree' => TRUE,
        '#markup' => t('You need to have a file or link imported to the datastore in order to drop it.'),
      );
      $form['actions']['submit']['#disabled'] = TRUE;
      $form['actions']['submit']['#value'] = t('Drop (disabled)');
    }

    return $form;
  }

  /**
   * Submit handler.
   */
  public function submitHandler(&$form_state) {
    $this->datastore->drop();
    drupal_set_message(t('Datastore dropped!'));
  }

}
