<?php
namespace Drupal\DKANExtension\Context;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\DrupalExtension\Context\DrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\UnsupportedDriverActionException as UnsupportedDriverActionException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\DriverException;
use Behat\Behat\Tester\Exception\PendingException;
use \stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Defines application features from the specific context.
 */
class SearchAPIContext extends RawDrupalContext implements SnippetAcceptingContext {

  protected $search_indexes = array();
  protected $search_forms = array();
  protected $active_form = '';

  /**
   * Initializes context.
   *
   * Initializes all the search api indexes.
   */
  public function __construct($search_indexes = array(), $search_forms = array()) {
    if (!empty($search_indexes)) {
      // If set, only use the specified indexes.
      foreach ($search_indexes as $index) {
        $this->search_indexes[$index] = search_api_index_load($index);
      }
    }
    else {
      // Load ALL the indexes.
      $this->search_indexes = search_api_index_load_multiple(false);
    }
    $this->search_forms = $search_forms;
  }

  /****************************
   * HELPER FUNCTIONS
   ****************************/

  public function process($indexes = array())
  {
    if (empty($indexes)) {
      $indexes = array_keys($this->search_indexes);
    }

    foreach ($indexes as $index) {
      $items = search_api_get_items_to_index($this->search_indexes[$index], 10);
      search_api_index_specific_items($this->search_indexes[$index], $items);
    }
  }

  /**
   * Update the active search form from step.
   */
  public function updateSearchForm($form) {

    if ($this->active_form == $form) {
      // Nothing needed here
      return;
    }

    if (!isset($this->search_forms[$form])) {
      throw new \Exception("Search form isn't configured: $form");
    }

    $this->active_form = $form;
  }

  /*****************************
   * CUSTOM STEPS
   *****************************/

  /**
   * @When I search for :term
   * @When I search for :term in the :form search form
   */
   public function iSearchFor($term, $form = 'default') {

    $this->updateSearchForm($form);


    $form_data = $this->search_forms[$this->active_form];
    $search_form = $this->getSession()->getPage()->findAll('css', $form_data['form_css']);

    if(count($search_form) > 1) {
      throw new \Exception("More than one search form found on the page.");
    }
    else if(count($search_form) < 1) {
      throw new \Exception("No search form found on the page.");
    }

    $search_form = array_pop($search_form);
    $search_form->fillField($form_data['form_field'], $term);
    $search_form->pressButton($form_data['form_button']);
    $results = $this->getSession()->getPage()->find("css", $form_data['results_css']);
    if (!isset($results)) {
      throw new \Exception("Search results region not found on the page.");
    }
  }

  /**
   * @Then I should see :number search results shown on the page
   * @Then I should see :number search results shown on the page in the :form search form
   */
  public function iShouldSeeSearchResults($number, $form = 'default') {

    $this->updateSearchForm($form);

    $results = $this->findResults();
    $count = count($results);
    if ($count != (int) $number) {
      throw new \Exception("$number search results expected, but $count found. ");
    }
  }

  /**
   * @Then I should see at least :number search results shown on the page
   * @Then I should see at least :number search results shown on the page in the :form search form
   */
  public function iShouldSeeAtLeastSearchResults($number, $form = 'default') {

    $this->updateSearchForm($form);

    $results = $this->findResults();
    $count = count($results);
    if ($count < (int) $number) {
      throw new \Exception("$number search results expected, but $count found. ");
    }
  }

  /**
   * @Then I should not see :text in the search results
   * @Then I should not see :text in the search results in the :form search form
   */
  public function iShouldNotSeeTextInSearchResults($text, $form = 'default') {

    $this->updateSearchForm($form);

    $results = $this->findInResults('named', array('content', $text));
    foreach ($results as $key => $result) {
      if (!empty($result)) {
        throw new \Exception("$text found in search result: $key.");
      }
    }
  }

  /**
   * @Then I should see :text in the search results
   * @Then I should see :text in the search results in the :form search form
   */
  public function iShouldSeeTextInSearchResults($text, $form = 'default') {

    $this->updateSearchForm($form);

    $results = $this->findInResults('named', array('content', $text));
    $found = false;
    $count = count($results);
    foreach ($results as $result) {
      if (!empty($result)) {
        $found = true;
      }

    }
    if (!$found) {
      throw new \Exception("$text not found in $count search results.");
    }
  }


  public function findInResults($arg1, $arg2) {
    $results = $this->findResults();
    foreach ($results as $key => $result) {
      $results[$key] = $result->find($arg1, $arg2);
    }
    return $results;
  }

  public function findResults() {
    if (empty($this->active_form)) {
      throw new \Exception("No active search form has been set.");
    }

    $form_data = $this->search_forms[$this->active_form];
    $search_results_node = $this->getSession()->getPage()->find('css', $form_data['results_css']);
    if (empty($search_results_node)) {
      throw new \Exception("Can't find the search results region.");
    }
    $results = $search_results_node->findAll('css', $form_data['result_item_css']);
    return $results;
  }
}
