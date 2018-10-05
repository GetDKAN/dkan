<?php

namespace Dkan\Datastore\Page\Component;

use Dkan\Datastore\Manager\ManagerInterface;

/**
 * Class Status.
 *
 * Html componet that displays the status of a datastore manager.
 */
class Status {

  private $datastoreManager;

  /**
   * Constructor.
   */
  public function __construct(ManagerInterface $manager) {
    $this->datastoreManager = $manager;
  }

  /**
   * Get HTML.
   */
  public function getHtml() {
    $state = $this->datastoreManager->getStatus();
    $stringSubs = [
      '%class' => $this->formatClassName(get_class($this->datastoreManager)),
      '%records' => $this->datastoreManager->numberOfRecordsImported(),
      '%import' => $this->datastoreStateToString($state['data_import']),
    ];

    $statusInfo = t("<dt>Importer</dt><dd>%class</dd>", $stringSubs);
    $statusInfo .= t("<dt>Records Imported</dt><dd>%records</dd>", $stringSubs);
    $statusInfo .= t("<dt>Data Importing</dt><dd>%import</dd>", $stringSubs);

    return "<dl>{$statusInfo}</dl>";
  }

  /**
   * Format the class name to something prettier.
   */
  private function formatClassName($classname) {
    /* @var $info \Dkan\Datastore\Manager\Info */
    foreach (dkan_datastore_managers_info() as $info) {
      if ('\\' . $classname == $info->getClass()) {
        return $info->getLabel();
      }
    }
    // Fallback if this fails for some reason.
    $nameBits = explode('\\', $classname);
    return end($nameBits);
  }

  /**
   * Private method.
   */
  private function datastoreStateToString($state) {
    switch ($state) {
      case ManagerInterface::STORAGE_UNINITIALIZED:
        return t("Uninitialized");

      case ManagerInterface::STORAGE_INITIALIZED:
        return t("Initialized");

      case ManagerInterface::DATA_IMPORT_UNINITIALIZED:
        return t("Ready");

      case ManagerInterface::DATA_IMPORT_READY:
        return t("Ready");

      case ManagerInterface::DATA_IMPORT_IN_PROGRESS:
        return t("In Progress");

      case ManagerInterface::DATA_IMPORT_PAUSED:
        return t("Paused");

      case ManagerInterface::DATA_IMPORT_DONE:
        return t("Done");

      case ManagerInterface::DATA_IMPORT_ERROR:
        return t("Error");
    }
  }

}
