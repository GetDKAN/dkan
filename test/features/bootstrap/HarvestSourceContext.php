<?php

use Drupal\DKANExtension\Context\RawDKANEntityContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class HarvestSourceContext extends RawDKANEntityContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    parent::__construct(
      'node',
      'harvest_source'
    );
  }

  /**
   * Creates harvest sources from a table.
   *
   * @Given harvest sources:
   */
  public function addHarvestSources(TableNode $harvestSourcesTable) {
    parent::addMultipleFromTable($harvestSourcesTable);
  }

  /**
   * Run a harvest on a harvest source.
   *
   * @param $machine_name harvest source machine name
   *
   * @Given The :machine_name source is harvested
   *
   * @throw \Exception
   */
  public function theHarvestSourceIsHarvested($machine_name) {
    $harvest_source = new HarvestSource($machine_name);
    if (!$harvest_source) {
      throw new \Exception("Harvest source '$machine_name' not found.");
    }

    global $user;
    // Save the original user to set it back later
    $global_user = $user;

    $user = user_load(1);

    // Harvest Cache
    dkan_harvest_cache_sources(array($harvest_source));
    // Harvest Migration of the test data.
    dkan_harvest_migrate_sources(array($harvest_source));

    // Make sure that we process any index items added after the harvest.
    $this->searchContext->process();

    // Back global user to the original user. Probably an anonymous.
    $user = $global_user;
  }

  /**
  * @AfterScenario @harvest_rollback
  */
  public function harvestRollback(AfterScenarioScope $event)
  {
    $migrations = migrate_migrations();
    $harvest_migrations = array();
    foreach ($migrations as $name => $migration) {
      if(strpos($name , 'dkan_harvest') === 0) {
        $migration = Migration::getInstance($name);
        $migration->processRollback();
      }
    }
  }
}
