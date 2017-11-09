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
   * @Then I :should see a valid data.json
   */
  public function iSeeAValidDataJson($should)
  {
    // Validate POD.
    $results = _open_data_schema_map_process_validate('PodValidator', TRUE);
    if ($results['errors'] && $should === 'should') {
      throw new \Exception(sprintf('Data.json is not valid.'));
    }

    if (!$results['errors'] && $should === 'should not') {
      throw new \Exception(sprintf('Data.json is valid.'));
    }
  }

  /**
   * @Then I should see a valid catalog xml
   */
  public function iShouldSeeAValidCatalogXml() {
    // Validate DCAT.
    $results = _open_data_schema_map_process_validate('DcatValidator', TRUE);

    if ($results['errors']) {
      throw new \Exception(sprintf('catalog.xml is not valid.'));
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
   * Step to validate license values.
   *
   * @Then I should see :option license values
   */
  public function iShouldSeeLicenseValues($option) {
    // Get the list of licenses provided by DKAN.
    $licenses = dkan_dataset_content_types_license_allowed_values();

    // Clean the array values and remove all non POD valid licenses if required.
    foreach ($licenses as $key => $value) {
      if (($option != 'all') && (is_array($value) && !isset($value['uri']))) {
        unset($licenses[$key]);
      }
      else {
        if (is_array($value) && isset($value['label'])) {
          $licenses[$key] = $value['label'];
        }
      }
    }

    // Append the 'None' values.
    if ($option === 'all') {
      $licenses[] = '- Select a value -';
      $licenses[] = 'Other';
    }

    // Get the list of licenses that were displayed.
    $available_licenses = array();
    $session = $this->getSession();
    $xpath = "//select[@name='field_license[und][select]']//option";
    $elements = $session->getPage()->findAll('xpath', $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath));
    foreach ($elements as $element) {
      $available_licenses[] = $element->getText();
    }

    $result = array_diff($available_licenses, $licenses);
    if (!empty($result)) {
      throw new \Exception(sprintf('The list of available licenses differs from the
      list of expected licenses by the following values: %s', implode(',', $result)));
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
