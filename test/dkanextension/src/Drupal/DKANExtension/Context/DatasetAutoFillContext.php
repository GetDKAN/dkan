<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;

/**
 * This context defines an autofill step function.
 *
 * I am creating this class because I need a step function that has access to
 * the DKANContext (with it's methods for manipulating the session page via the
 * MinkContext) as well as the ServicesContext.
 *
 * I need an insstance of the ServicesContext because that context has knowledge
 * of how to generate required field values in a format we can pass to the
 * MinkContext.
 *
 * I could potentially just do all this from DKANContext itself, but that would
 * be at the cost of making that class even more complex than it already is.
 */
class DatasetAutofillContext implements Context {

  /**
   * Compose with instances of ServicesContext and DKANContext.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
    $this->servicesContext = $environment->getContext('Drupal\DKANExtension\Context\ServicesContext');
    $this->dkanContext = $environment->getContext('Drupal\DKANExtension\Context\DKANContext');
  }

  /**
   * Use this step method to make a form filling scenario less brittle.
   *
   * @When I fill-in the following:
   */
  public function iAutoFillInRequiredFields(TableNode $tableNode) {
    $data = $this->mapTableNodeToServicesData($tableNode);
    $processed_data = $this->servicesContext->build_node_data($data);
    $tableRows = $this->mapServicesDataToTableRows($processed_data);

    $tableNode = new TableNode($tableRows);
    $this->dkanContext->getMink()->fillFields($tableNode);
  }

  /**
   * Transform tableNode into a form ServicesContext::build_node_data can take.
   */
  private function mapTableNodeToServicesData(TableNode $tableNode) {
    $data = array();
    $data['type'] = 'dataset';
    foreach ($tableNode->getRows() as $row) {
      list($key, $value) = $row;
      $data[$key] = $value;
    }

    return $data;
  }

  /**
   * Transform data into a form TableNode::_constructor can accept.
   */
  private function mapServicesDataToTableRows($data) {
    unset($data['type']);

    $table = array();
    foreach ($data as $key => $value) {
      if (!empty($value)) {
        array_push($table, array($key, $value));
      }
    }
    return $table;
  }

}
