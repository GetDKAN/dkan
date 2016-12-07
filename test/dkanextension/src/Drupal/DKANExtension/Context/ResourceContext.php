<?php

namespace Drupal\DKANExtension\Context;


use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class ResourceContext extends RawDKANEntityContext{

    public function __construct(){
        parent::__construct(
          'node',
          'resource',
          // note that this field is called "Groups" not "publisher" in the form, should the field name be updated?
          array('publisher' => 'og_group_ref', 'published' => 'status')
        );
    }

    /**
     * Creates resources from a table.
     *
     * @Given resources:
     */
    public function addResources(TableNode $resourcesTable){
        parent::addMultipleFromTable($resourcesTable);
    }

  /**
   * @Given I am on (the) :title resource embed page
   */
  public function iAmOnResourceEmbedPage($title) {
    if (empty($title)) {
      throw new \Exception("Missing title argument");
    }
    if ($nodes = $this->getNodeByTitle($title)) {
      $nid = array_values($nodes)[0]->nid;
    } else {
      throw new \Exception("Resource with the title '$title' doesn't exist.");
    }
    $this->visit("/node/" . $nid . "/recline-embed");
  }

  /**
   * @Given :provider previews are :setting for :format_name resources
   *
   * Changes variables in the database to enable or disable external previews
   */
  public function externalPreviewsAreEnabledForFormat($provider, $setting, $format_name)
  {
    $format = current(taxonomy_get_term_by_name($format_name, 'format'));
    $preview_settings = variable_get("dkan_dataset_format_previews_tid{$format->tid}", array());
    // If $setting was "enabled," the preview is turned on. Otherwise, it's
    // turned off.
    $preview_settings[$provider] = ($setting == 'enabled') ? $provider : 0;
    variable_set("dkan_dataset_format_previews_tid{$format->tid}", $preview_settings);
  }

}
