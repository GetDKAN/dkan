<?php

namespace Dkan\Datastore\Page\Component;

use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Manager\ManagerInterface;
use Dkan\Datastore\Resource;

/**
 * Class ManagerSelection.
 *
 * Form component that manages the selection of a datastore manager.
 */
class ManagerSelection {

  private $resource;
  private $datastoreManager;

  /**
   * Constructor.
   */
  public function __construct(Resource $resource, ManagerInterface $manager) {
    $this->resource = $resource;
    $this->datastoreManager = $manager;
  }

  /**
   * Get form.
   */
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

  /**
   * Submit.
   */
  public function submit($values) {
    $class = $values;

    $factory = new Factory($this->resource);
    $factory->setClass($class);

    /* @var $manager \Dkan\Datastore\Manager\ManagerInterface */
    $manager = $factory->get();
    $manager->saveState();
  }

}
