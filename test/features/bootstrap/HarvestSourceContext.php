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

}
