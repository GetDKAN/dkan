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
      'class' => $this->formatClassName(get_class($this->datastoreManager)),
      'records' => $this->datastoreManager->numberOfRecordsImported(),
      'import' => self::datastoreStateToString($state['data_import']),
    ];

    $statusInfo = "<dt>" . t("Importer") . "</dt><dd>{$stringSubs['class']}</dd>";
    $statusInfo .= "<dt>" . t("Records Imported") . "</dt><dd>{$stringSubs['records']}</dd>";
    $statusInfo .= "<dt>" . t("Data Importing") . "</dt><dd>{$stringSubs['import']}</dd>";

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
  public static function datastoreStateToString($state) {
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
        drupal_set_message(t("The datastore importer is currently paused. It will resume in the background the next time cron runs from drush."));
        return t("Paused");

      case ManagerInterface::DATA_IMPORT_DONE:
        return t("Done");

      case ManagerInterface::DATA_IMPORT_ERROR:
        return t("Error");
    }
  }

}
