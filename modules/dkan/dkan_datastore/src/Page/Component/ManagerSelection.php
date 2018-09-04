<?php

namespace Dkan\Datastore\Page\Component;

use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Manager\ManagerInterface;
use Dkan\Datastore\Resource;


class ManagerSelection {

  private $resource;
  private $datastoreManager;

  public function __construct(Resource $resource, ManagerInterface $datastore_manager) {
    $this->resource = $resource;
    $this->datastoreManager = $datastore_manager;
  }

  public function getForm() {
    $managers_info = dkan_datastore_managers_info();
    $form = array();

    // We only show this if there are multiple managers.
    if (count($managers_info) > 1) {
      $class = get_class($this->datastoreManager);

      $options = [];

      /* @var $manager_info \Dkan\Datastore\Manager\Info */
      foreach ($managers_info as $manager_info) {
        $options[$manager_info->getClass()] = $manager_info->getLabel();
      }
      $form['datastore_managers_selection'] = array(
        '#type' => 'select',
        '#title' => t('Change datastore importer:'),
        '#options' => $options,
        '#default_value' => "\\$class",
      );
    }

    return $form;
  }

  public function submit($values) {
    $class = $values;

    $factory = new Factory($this->resource);
    $factory->setClass($class);

    /* @var $datastore_manager \Dkan\Datastore\Manager\ManagerInterface */
    $datastore_manager = $factory->get();
    $datastore_manager->saveState();
  }

}