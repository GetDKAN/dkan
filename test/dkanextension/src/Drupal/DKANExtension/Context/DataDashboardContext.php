<?php

namespace Drupal\DKANExtension\Context;

use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class DataDashboardContext extends RawDKANEntityContext{

  public function __construct(){
    parent::__construct(
      'node',
      'data_dashboard'
    );
  }

  /**
   * @Given data dashboards:
   */
  public function addDataDashboard(TableNode $dashboardtable){
    parent::addMultipleFromTable($dashboardtable);
  }
}
