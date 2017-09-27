<?php
namespace Dkan\Datastore\Form;

use Dkan\Datastore\DatastoreInterface;

module_load_include("php", "dkan_datastore", "src/DatastoreInterface");


class Import {

  private $datastore;

  public function __construct(DatastoreInterface $datastore) {
    $this->datastore = $datastore;
  }

  public function toArray(&$form_state) {
    $id = $this->datastore->getId();

    $form = array();
    if ($id) {
      $name = $this->datastore->getLabel();
      $node = $this->datastore->getNode();

      $form = confirm_form($form, t('Import all content from source?'), 'node/' . $node->nid, '', t('Import'), t('Cancel'), 'confirm feeds update');

      $form[$id]['source_status'] = array(
        '#type' => 'item',
        '#title' => t('@datastore_name: Status', array('@datastore_name' => $name)),
        '#markup' => $this->datastore->getStatusMessage(),
      );


      $form['#redirect'] = 'node/' . $node->nid;

      // Previously, there was a sort of check for resource validity.
      // I feel like that should happen somewhere else. Maybe during
      // datastore initialization?

      $import_progress = $this->datastore->getImportProgress();
      if ($import_progress !== 1.0) {
        $form['actions']['submit']['#disabled'] = TRUE;
        $form['actions']['submit']['#value'] = t('Importing (@progress %)', array('@progress' => number_format(100 * $import_progress, 0)));
      }
    }
    else {
      $form['no_source'] = array(
        '#markup' => t('There is nothing to manage! You need to upload or link to a file in order to use the datastore.'),
      );
    }

    return $form;
  }

  public function submitHandler(&$form_state) {
    $this->datastore->import();
  }

}