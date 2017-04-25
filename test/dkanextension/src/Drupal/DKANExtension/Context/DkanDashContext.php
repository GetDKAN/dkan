<?php

namespace Drupal\DKANExtension\Context;

use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class DkanDashContext extends RawDKANEntityContext{

  public function __construct(){
    parent::__construct(
      'node',
      'react_dashboard',
      array('field_dash_settings', 'settings')
    );
  }

  /**
   * @Given react dashboards:
   */
  public function addDkanDash(TableNode $dashboardtable){
    parent::addMultipleFromTable($dashboardtable);
  }
}
