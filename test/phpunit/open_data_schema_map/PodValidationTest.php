<?php

/**
 * @file
 * Test alteration of data.json results for POD validation.
 */

/**
 * POD Validation check.
 */
class PodValidationTest extends PHPUnit_Framework_TestCase {

  /**
   * Set up dataset json.
   */
  public function sampleDataset() {
    return array(
      '@type' => 'dcat:Dataset',
      'accessLevel' => 'public',
      'accrualPeriodicity' => 'R/P1Y',
      'contactPoint' => array(
        'fn' => 'Pete Moss',
        'hasEmail' => 'mailto:pete@test.com',
      ),
      'description' => '<p>Data on bike lanes in Florida.</p>',
      'distribution' => array(
        '@type' => 'dcat:Distribution',
        'downloadURL' => 'http://192.168.99.100:32770/sites/default/files/Bike_Lane.csv',
        'mediaType' => 'text/csv',
        'format' => 'csv',
        'title' => 'Florida Bike Lanes',
      ),
      'identifier' => 'cedcd327-4e5d-43f9-8eb1-c11850fa7c55',
      'issued' => '2016-06-22',
      'language' => array(
        'English',
      ),
      'license' => 'http://opendefinition.org/licenses/odc-odbl/',
      'modified' => '2017-04-05',
      'publisher' => array(
        '@type' => 'org:Organization',
        'name' => '192.168.99.100',
      ),
      'theme' => array(
        'Transportation',
        'City Planning',
      ),
      'title' => 'Florida Bike Lanes',
    );
  }

  /**
   * Test language value is valid.
   */
  public function testOpenDataSchemaPodOpenDataSchemaMapResultsAlterLanguage() {
    $dataset = new PodValidationTest();
    $dataset = $dataset->sampleDataset();

    // Test the result alter function: converts language label to the key value.
    $result = array($dataset);
    $machine_name = 'test';
    $api_schema = 'pod_v1_1';
    $expected = array('en');
    open_data_schema_pod_open_data_schema_map_results_alter($result, $machine_name, $api_schema);
    $this->assertEquals($expected, $result['dataset'][0]['language'], 'Languages do not match');
  }

}
