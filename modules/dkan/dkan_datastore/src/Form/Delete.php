<?php

namespace Dkan\Datastore\Form;

use Dkan\Datastore\DatastoreInterface;

module_load_include("php", "dkan_datastore", "src/DatastoreInterface");

/**
 * Class Delete.
 */
class Delete {

  private $datastore;

  /**
   * Delete constructor.
   */
  public function __construct(DatastoreInterface $datastore) {
    $this->datastore = $datastore;
  }

  /**
   * Array version of the form.
   */
  public function toArray(&$form_state) {
    module_load_include('inc', 'feeds', 'feeds.pages');

    $node = $this->datastore->getNode();

    $form = array();
    $datastore_id = $this->datastore->getId();
    $form['#redirect'] = 'node/' . $node->nid;

    if ($datastore_id) {
      $form[$datastore_id]['status'] = array(
        '#type' => 'item',
        '#title' => t('@datastore_name: Status', array('@datastore_name' => $this->datastore->getLabel())),
        '#tree' => TRUE,
        '#markup' => $this->datastore->getStatusMessage(),
      );
      $progress = $this->datastore->getImportProgress();

      $form = confirm_form($form, t('Delete all items from source?'), $form['#redirect'], '', t('Delete'), t('Cancel'), 'confirm update');

      if ($progress !== 1.0) {
        $form['actions']['submit']['#disabled'] = TRUE;
        $form['actions']['submit']['#value'] = t('Deleting (@progress %)', array('@progress' => number_format(100 * $progress, 0)));
      }
    }
    else {
      $form['no_source'] = array(
        '#markup' => t('No feeds sources added to node.'),
      );
    }
    return $form;
  }

  /**
   * Submit handler.
   */
  public function submitHandler(&$form_state) {
    $this->datastore->delete();
  }

}
