<?php

namespace Drupal\DKANExtension\Context;

use Drupal\DKANExtension\Context\RawDKANEntityContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\AfterFeatureScope;

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
   * @BeforeFeature
   */
  public static function setupFeature(BeforeFeatureScope $scope)
  {
    $feature = $scope->getFeature();
    if($feature->getTitle() == 'Dkan Harvest') {
      module_enable(array('dkan_harvest_test'));
    }
  }

  /** 
   * @AfterFeature
   */
  public static function teardownFeature(AfterFeatureScope $scope)
  {
    $feature = $scope->getFeature();
    if($feature->getTitle() == 'Dkan Harvest') {
      module_disable(array('dkan_harvest_test'));
    }
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
    $harvest_source = new \HarvestSource($machine_name);
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
        $migration = \Migration::getInstance($name);
        $migration->processRollback();
      }
    }
  }

  /**
   * @Then the content :content_title should be :status
   */
  public function theContentShouldBe($content_title, $status)
  {
    // Get content by title.
    $query = new \EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'node')
      ->propertyCondition('title', $content_title)
      ->propertyOrderBy('changed', $direction = 'DESC')
      ->execute();

    // Load content if any and generate wrapper.
    if (!empty($result['node'])) {
      if ($status === 'deleted') {
        throw new \Exception("Content with title '$content_title' was found.");
      }
      $content_ids = array_keys($result['node']);
      $content_id = current($content_ids);
      $content = node_load($content_id, NULL, TRUE);
      $content_wrapper = entity_metadata_wrapper('node', $content);
    } else {
      if ($status != 'deleted') {
        throw new \Exception("Content with title '$content_title' was not found.");
      }
      return TRUE;
    }

    // Check content status.
    switch ($status) {
      case 'published':
        if ($content_wrapper->status->value() != NODE_PUBLISHED) {
          throw new \Exception("The status of the content is not '$status'");
        }
        break;
      case 'unpublished':
        if ($content_wrapper->status->value() != NODE_NOT_PUBLISHED) {
          throw new \Exception("The status of the content is not '$status'");
        }
        break;
      case 'orphaned':
        if (!$content_wrapper->field_orphan->value()) {
          throw new \Exception("The status of the content is not '$status'");
        }
        break;
      default:
        break;
    }
  }
}
