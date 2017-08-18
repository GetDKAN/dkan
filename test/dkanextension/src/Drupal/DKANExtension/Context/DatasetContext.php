<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use SearchApiQuery;

/**
 * Defines application features from the specific context.
 */
class DatasetContext extends RawDKANEntityContext {

  use ModeratorTrait;

  /**
   *
   */
  public function __construct($fields, $labels = array(), $sets = array(), $defaults = array()) {
    $this->datasetFieldLabels = $labels['labels'];
    $this->datasetFieldSets = $sets['sets'];
    $this->datasetFieldDefaults = $defaults['defaults'];

    parent::__construct(
      'node',
      'dataset',
      $fields['fields'],
      array(
        'moderation',
        'moderation_date',
      )
    );
  }

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    parent::gatherContexts($scope);
    $environment = $scope->getEnvironment();
    $this->groupContext = $environment->getContext('Drupal\DKANExtension\Context\GroupContext');
    $this->dkanContext = $environment->getContext('Drupal\DKANExtension\Context\DKANContext');
  }

  /**
   * Creates datasets from a table.
   *
   * @Given datasets:
   */
  public function addDatasets(TableNode $datasetsTable) {
    parent::addMultipleFromTable($datasetsTable);
  }

  /**
   * Looks for a dataset in the dataset view with the given name on the current page.
   *
   * @Then I should see a dataset called :text
   *
   * @throws \Exception
   *   If region or text within it cannot be found.
   */
  public function iShouldSeeADatasetCalled($text) {
    $session = $this->getSession();
    $page = $session->getPage();
    $search_region = $page->find('css', '.view-dkan-datasets');
    $search_results = $search_region->findAll('css', '.views-row');
    $found = FALSE;
    foreach ($search_results as $search_result) {
      $title = $search_result->find('css', 'h2');
      if ($title->getText() === $text) {
        $found = TRUE;
      }
    }
    if (!$found) {
      throw new \Exception(sprintf("The text '%s' was not found", $text));
    }
  }

  /**
   * @Then The dataset :title is in :state moderation state
   */
  public function theDatasetIsInModerationState($title, $state) {
    $node = reset($this->getNodeByTitle($title));
    if (!$node) {
      throw new \Exception(sprintf($title . " node not found."));
    }
    $this->isNodeInModerationState($node, $state);
  }

  /**
   *
   */
  public function pre_save($wrapper, $fields) {
    $this->preSaveModerate($wrapper, $fields);
    parent::pre_save($wrapper, $fields);
  }

  /**
   *
   */
  public function post_save($wrapper, $fields) {
    parent::post_save($wrapper, $fields);
    $this->moderate($wrapper, $fields);
  }

  /**
   * @Then I should see the local preview link
   */
  public function iShouldSeeTheLocalPreviewLink() {
    $this->assertSession()->pageTextContains(variable_get('dkan_dataset_teaser_preview_label', '') . ' ' . t('Preview'));
  }

  /**
   * @Given I should see the first :number dataset items in :orderby :sortdirection order.
   */
  public function iShouldSeeTheFirstDatasetListInOrder($number, $orderby, $sortdirection) {
    $number = (int) $number;
    // Search the list of datasets actually on the page (up to $number items)
    $dataset_list = array();
    $count = 0;
    while (($count < $number) && ($row = $this->getSession()->getPage()->find('css', '.views-row-' . ($count + 1))) !== NULL) {
      $row = $row->find('css', 'h2');
      $dataset_list[] = $row->getText();
      $count++;
    }

    if ($count !== $number) {
      throw new \Exception("Couldn't find $number datasets on the page. Found $count.");
    }

    switch ($orderby) {
      case 'Date changed':
        $orderby = 'changed';
        break;

      case 'Title':
        $orderby = 'title';
        break;

      default:
        throw new \Exception("Ordering by '$orderby' is not supported by this step.");
    }

    $index = search_api_index_load('datasets');
    $query = new SearchApiQuery($index);

    $results = $query->condition('type', 'dataset')
      ->condition('status', '1')
      ->sort($orderby, strtoupper($sortdirection))
      ->range(0, $number)
      ->execute();
    $count = count($results['results']);
    if (count($results['results']) !== $number) {
      throw new \Exception("Couldn't find $number datasets in the database. Found $count.");
    }

    foreach ($results['results'] as $nid => $result) {
      $dataset = node_load($nid);
      $found_title = array_shift($dataset_list);
      if ($found_title !== $dataset->title) {
        throw new \Exception("Does not match order of list, $found_title was next on page but expected $dataset->title");
      }
    }
  }

  /**
   * @Given /^I add a Dataset Filtered List$/
   */
  public function iAddADatasetFilteredList() {
    $add_button = $this->getXPathElement("//fieldset[@class='widget-preview panel panel-default'][3]//a");
    $add_button->click();
  }

  /**
   * @When I empty the resources field :locator
   *
   * Empty the 'Resources' autocomplete field on a Dataset form.
   */
  public function iEmptyTheResourcesField($locator) {
    $session = $this->getSession();
    $page = $session->getPage();

    $field = $page->find('xpath', '//div[@id="' . $locator . '"]');
    if ($field === NULL) {
      throw new \InvalidArgumentException(sprintf('Cannot find chosen field: "%s"', $locator));
    }

    $field_choices = $field->findAll('css', '.chosen-choices .search-choice');
    foreach ($field_choices as $field_choice) {
      $remove_button = $field_choice->find('css', '.search-choice-close');
      if ($remove_button) {
        $remove_button->click();
      }
    }
  }

  /**
   * @Then I should see all published datasets
   */
  public function iShouldSeeAllPublishedDatasets() {
    $session = $this->getSession();
    $page = $session->getPage();
    $search_region = $page->find('css', '.view-dkan-datasets');
    $search_results = $search_region->findAll('css', '.view-header');

    $index = search_api_index_load('datasets');
    $query = new SearchApiQuery($index);

    $results = $query->condition('type', 'dataset')
      ->condition('status', '1')
      ->execute();
    $total = count($results['results']);
    $text = $total . " results";

    foreach ($search_results as $search_result) {
      $found = $search_result->getText();
    }

    if ($found !== $text) {
      throw new \Exception("Found $found in the page but total is $total.");
    }
  }

  /**
   * @Then I should see all the dataset fields in the form
   */
  public function iShouldSeeAllTheDatasetFieldsInTheForm() {
    $form_css_selector = 'form#dataset-node-form';

    // We could use field_info_instances() to get the list of fields for the 'dataset' content
    // type but that would not cover the case where a field is removed accidentally.
    $dataset_fields = $this->datasetFieldLabels;
    $dataset_fieldsets = $this->datasetFieldSets;
    // Get all available form fields.
    // Searching by the Label as a text on the page is not enough since a text like 'Resources'
    // could appear because other reasons.
    $session = $this->getSession();
    $page = $session->getPage();
    $form_region = $page->find('css', $form_css_selector);
    $form_fieldset_elements = $form_region->findAll('css', 'fieldset div.fieldset-legend');

    // Clean found fieldsets. Some of them are empty values.
    $available_form_fieldsets = array();
    foreach ($form_fieldset_elements as $form_fieldset_element) {
      $label = $form_fieldset_element->getText();
      if (!empty($label)) {
        $available_form_fieldsets[] = $label;
      }
    }

    $query_script = "jQuery('.form-item label', jQuery('$form_css_selector'))
      .map(function(){ return jQuery(this).text().trim(); })";

    $available_form_fields = $session->evaluateScript($query_script);

    foreach ($dataset_fields as $key => $field_name) {
      // Add way for sites to skip specific fields.
      if (empty($field_name)) {
        continue;
      }
      if (!in_array($field_name, $available_form_fields)) {
        throw new \Exception("Field $field_name was not found in the form with CSS selector '$form_css_selector'");
      }
    }

    // Check that all form fieldsets are present.
    foreach ($dataset_fieldsets as $key => $fieldset_name) {
      if (empty($fieldset_name)) {
        continue;
      }
      if (!in_array($fieldset_name, $available_form_fieldsets)) {
        throw new \Exception("Field set $fieldset_name was not found in the form with CSS selector '$form_css_selector'");
      }
    }
  }

  /**
   * @Given I :operation the :option on DKAN Dataset Forms
   */
  public function iTheOnDkanDatasetForms($operation, $option) {
    $enabled = 0;
    if ($operation === "enable") {
      $enabled = 1;
    }

    switch ($option) {
      case 'Strict POD validation':
        variable_set('dkan_dataset_form_pod_validation', $enabled);
        break;

      case 'Groups validation':
        variable_set('dkan_dataset_form_group_validation', $enabled);
        break;

      default:
        break;
    }
  }

  /**
   * @Then I should see the :option groups option
   */
  public function iShouldSeeTheGroupsOption($option) {
    $element = $this->find_select_option('og_group_ref[und][]', $option);
    if (!$element) {
      throw new \Exception(sprintf('The %s option could not be found.', $option));
    }
  }

  /**
   * @Then I should not see the :option groups option
   */
  public function iShouldNotSeeTheGroupsOption($option) {
    $element = $this->find_select_option('og_group_ref[und][]', $option);
    if ($element) {
      throw new \Exception(sprintf('The %s option was found.', $option));
    }
  }

  /**
   * Helper function to search for an option element inside a select element.
   */
  private function find_select_option($select_name, $option) {
    $session = $this->getSession();
    $xpath = "//select[@name='" . $select_name . "']//option[text()='" . $option . "']";
    return $session->getPage()->find('xpath', $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath));
  }

}
