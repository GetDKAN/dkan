<?php
namespace Drupal\DKANExtension\Context;
use Drupal\DKANExtension\Context\PageContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

/**
 * Defines application features from the specific context.
 */
class PODContext extends RawDKANContext {

  private $required_pod_fields = array(
    'title' => 'Title',
    'description' => 'Description',
    'keyword' => 'Tags',
    'contactPoint:email' => 'Contact Email',
    'contactPoint:name' => 'Contact Name',
    'accessLevel' => 'Public Access Level',
    'bureauCodeUSG' => 'Bureau Code',
    'programCodeUSG ' => 'Program Code',
  );

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope){
    parent::gatherContexts($scope);
    $environment = $scope->getEnvironment();
    $this->pageContext = $environment->getContext('Drupal\DKANExtension\Context\PageContext');
  }
  /**
   * @When I should see a valid data.json
   */
  public function iShouldSeeAValidDatasjon() {
    // Change /data.json path to /json during tests. The '.' on the filename breaks tests on CircleCI's server.
    $data_json = open_data_schema_map_api_load('data_json_1_1');
    if ($data_json->endpoint !== 'json') {
      $data_json->endpoint = 'json';
      drupal_write_record('open_data_schema_map', $data_json, 'id');
      drupal_static_reset('open_data_schema_map_api_load_all');
      menu_rebuild();
    }
    // Get base URL.
    $url = $this->getMinkParameter('base_url') ? $this->getMinkParameter('base_url') : "http://127.0.0.1::8888";

    // Validate POD.
    $results = open_data_schema_pod_process_validate($url . '/json', TRUE);
    if ($results['errors']) {
      throw new \Exception(sprintf('Data.json is not valid.'));
    }
  }

  /**
   * @Then I should see all of the Federal Extras fields
   */
  public function iShouldSeeAllOfTheFederalExtrasFields()
  {
    $feFields = array(
      'bureauCodeUSG' => 'Bureau Code',
      'programCodeUSG ' => 'Program Code',
      'dataQualityUSG' => 'Data Quality',
      'primaryITInvestmentUIIUSG' => 'Primary IT Investment UII',
      'systemOfRecordsUSG' => 'System of Records',
    );

    foreach ($feFields as $key => $fieldName) {
      $this->assertSession()->pageTextContains($fieldName);
    }
  }

  /**
   * @Then I should see all POD required fields
   */
  public function iShouldSeeAllPodRequiredFields()
  {
    foreach ($this->required_pod_fields as $key => $fieldName) {
      $this->assertSession()->pageTextContains($fieldName);
    }
  }

  /**
   * @Then I should see an error for POD required fields
   */
  public function iShouldSeeAnErrorForPodRequiredFields()
  {
    foreach ($this->required_pod_fields as $key => $fieldName) {
      $this->assertSession()->pageTextContains($fieldName . ' field is required.');
    }
  }

  /**
   * @BeforeScenario @add_ODFE
   */
  public function addODFE(BeforeScenarioScope $event)
  {
    // Enable 'open_data_federal_extras' module.
    module_enable(array('open_data_federal_extras'));
  }

  /**
   * @AfterScenario @remove_ODFE
   */
  public function removeODFE(AfterScenarioScope $event)
  {
    // Disable 'open_data_federal_extras' module.
    module_disable(array('open_data_federal_extras'));

    // Remove ODFE fields.
    field_delete_field('field_odfe_bureau_code');
    field_delete_field('field_odfe_data_quality');
    field_delete_field('field_odfe_investment_uii');
    field_delete_field('field_odfe_program_code');
    field_delete_field('field_odfe_system_of_records');
  }
}