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
   * Constructor.
   */
  public function __construct() {
    parent::__construct(
      'node',
      'dataset',
      // ToDo: load this from custom context.https://github.com/NuCivic/dkan_starter/issues/332.
      array(
        'title' => 'title',
        'description' => 'body',
        'published' => 'status',
        'resource' => 'field_resources',
        'access level' => 'field_public_access_level',
        'contact name' => 'field_contact_name',
        'contact email' => 'field_contact_email',
        'attest name' => 'field_hhs_attestation_name',
        'attest date' => 'field_hhs_attestation_date',
        'verification status' => 'field_hhs_attestation_negative',
        'attest privacy' => 'field_hhs_attestation_privacy',
        'attest quality' => 'field_hhs_attestation_quality',
        'bureau code' => 'field_odfe_bureau_code',
        'license' => 'field_license',
      ),
      array(
        'moderation', 
        'moderation_date',
      )
    );
  }

  /**
   * Gather all needed contexts.
   *
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
  public function iShouldSeeDatasetCalled($text) {
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
   * Confirm that a dataset is on the specified moderation state.
   *
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
   * Function executed before the node is saved.
   */
  public function preSave($wrapper, $fields) {
    $this->preSaveModerate($wrapper, $fields);
    parent::preSave($wrapper, $fields);
  }

  /**
   * Function executed after the node is saved.
   */
  public function postSave($wrapper, $fields) {
    parent::postSave($wrapper, $fields);
    $this->moderate($wrapper, $fields);
  }

  /**
   * Confirm that the local preview link is visible.
   *
   * @Then I should see the local preview link
   */
  public function iShouldSeeTheLocalPreviewLink() {
    $this->assertSession()->pageTextContains(variable_get('dkan_dataset_teaser_preview_label', '') . ' ' . t('Preview'));
  }

  /**
   * Confirm that the first number of datasets is on the specified order.
   *
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
      // Drupal removes extra spacing on titles somehow so reproducing here.
      $title = preg_replace('/\s+/', ' ', $dataset->title);
      if ($found_title !== $title) {
        throw new \Exception("Does not match order of list, $found_title was next on page but expected $dataset->title");
      }
    }
  }

  /**
   * Add a dataset filtered list panel.
   *
   * @Given /^I add a Dataset Filtered List$/
   */
  public function iAddDatasetFilteredList() {
    $add_button = $this->getXPathElement("//fieldset[@class='widget-preview panel panel-default'][3]//a");
    $add_button->click();
  }

  /**
   * Empty the 'Resources' autocomplete field on a Dataset form.
   *
   * @When I empty the resources field :locator
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
   * Confirm that all published datasets are visible.
   *
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
   * Confirm that all dataset form fields are visible.
   *
   * @Then I should see all the dataset fields in the form
   */
  public function iShouldSeeAllTheDatasetFieldsInTheForm() {
    $form_css_selector = '.node-dataset-form';

    // We could use field_info_instances() to get the list of fields for the 'dataset' content
    // type but that would not cover the case where a field is removed accidentally.
    $dataset_fields = array(
      'title' => 'Title',
      'body' => 'Description',
      'field_tags' => 'Tags',
      'field_topics' => 'Topics',
      'field_license' => 'License',
      'field_author' => 'Author',
      'field_spatial_geographical_cover' => 'Spatial / Geographical Coverage Location',
      'field_frequency' => 'Frequency',
      'field_granularity' => 'Granularity',
      'field_data_dictionary_type' => 'Data Dictionary Type',
      'field_data_dictionary' => 'Data Dictionary',
      'field_contact_name' => 'Contact Name',
      'field_contact_email' => 'Contact Email',
      'field_public_access_level' => 'Public Access Level',
      'field_additional_info' => 'Additional Info',
      'field_resources' => 'Resources',
      'field_related_content' => 'Related Content',
      'field_landing_page' => 'Homepage URL',
      'field_conforms_to' => 'Data Standard',
      'field_language' => 'Language',
      'og_group_ref' => 'Groups',
    );

    $dataset_fieldsets = array(
      'field_spatial' => 'Spatial / Geographical Coverage Area',
      'field_temporal_coverage' => 'Temporal Coverage',
    );

    // Get all available form fields.
    // Searching by the Label as a text on the page is not enough since a text like 'Resources'
    // could appear because other reasons.
    $session = $this->getSession();
    $page = $session->getPage();
    $form_region = $page->find('css', $form_css_selector);
    $form_field_elements = $form_region->findAll('css', '.form-item label');
    $form_fieldset_elements = $form_region->findAll('css', 'fieldset div.fieldset-legend');

    // Clean found fields. Some of them are empty values.
    $available_form_fields = array();
    foreach ($form_field_elements as $form_field_element) {
      if (!empty($form_field_element)) {
        $available_form_fields[] = $form_field_element->getText();
      }
    }

    // Clean found fieldsets. Some of them are empty values.
    $available_form_fieldsets = array();
    foreach ($form_fieldset_elements as $form_fieldset_element) {
      if (!empty($form_fieldset_element)) {
        $available_form_fieldsets[] = $form_fieldset_element->getText();
      }
    }

    // Check that all form fiels are present.
    foreach ($dataset_fields as $key => $field_name) {
      if (!in_array($field_name, $available_form_fields)) {
        throw new \Exception("$field_name was not found in the form with CSS selector '$form_css_selector'");
      }
    }

    // Check that all form fielsets are present.
    foreach ($dataset_fieldsets as $key => $fieldset_name) {
      if (!in_array($fieldset_name, $available_form_fieldsets)) {
        throw new \Exception("$fieldset_name was not found in the form with CSS selector '$form_css_selector'");
      }
    }
  }

  /**
   * Choose an option on DKAN dataset forms section.
   *
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
   * Confirm that a group option is visible.
   *
   * @Then I should see the :option groups option
   */
  public function iShouldSeeTheGroupsOption($option) {
    $element = $this->findSelectOption('og_group_ref[und][]', $option);
    if (!$element) {
      throw new \Exception(sprintf('The %s option could not be found.', $option));
    }
  }

  /**
   * Confirm that a group option is not visible.
   *
   * @Then I should not see the :option groups option
   */
  public function iShouldNotSeeTheGroupsOption($option) {
    $element = $this->findSelectOption('og_group_ref[und][]', $option);
    if ($element) {
      throw new \Exception(sprintf('The %s option was found.', $option));
    }
  }

  /**
   * Helper function to search for an option element inside a select element.
   */
  private function findSelectOption($select_name, $option) {
    $session = $this->getSession();
    $xpath = "//select[@name='" . $select_name . "']//option[text()='" . $option . "']";
    return $session->getPage()->find('xpath', $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath));
  }

}
