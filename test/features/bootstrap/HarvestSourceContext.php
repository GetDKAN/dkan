<?php

use Drupal\DKANExtension\Context\RawDKANEntityContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

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
    $harvest_source = HarvestSource::getSourceByMachineName($machine_name);
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
   * Check a table with the given class name exists in the page
   *
   * @Given I should see a table with a class name :class_name
   *
   * @return \Behat\Mink\Element\NodeElement
   *
   * @throws \Exception
   */
  public function assertTableByClassName($class_name) {
    $page = $this->getSession()->getPage();
    $table = $page->findAll('css', 'table.'.$class_name);
    if (empty($table)) {
      throw new \Exception(sprintf('No table found on the page %s', $this->getSession()->getCurrentUrl()));
    }

// The current use case
    return array_pop($table);
  }

  /**
   * Check on the number of rows a table with the class name :class_name.
   *
   * @Then the table with the class name :class_name should have :number row(s)
   *
   * @throws \Exception
   */
  public function assertTableRowNumber($class_name, $number) {
    if (!is_numeric($number)) {
      throw new \Exception(sprintf('Expected "number" to be numeric'));
    }

    $table = $this->assertTableByClassName($class_name);
    $rows = $table->findAll('css', 'tr');

    // The first row is for the header. bump.
    array_pop($rows);

    if (count($rows) != $number) {
          throw new \Exception(sprintf('Found %s rows in the harvest event log table instead of the expected %s.', count($rows), $number));
    }
  }
}
