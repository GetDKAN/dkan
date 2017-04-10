<?php

class DKANMigrateBaseTestSetup
{
    public function unpublishNodes($type) {
      db_update('node')
        ->fields(array(
          'status' => 0,
        ))
        ->condition('type', $type)
        ->execute();
    }
}

class DKANMigrateBaseTest  extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
      // module_enable(array('dkan_migrate_base_example'), TRUE);
      $setup = new DKANMigrateBaseTestSetup();
      $setup->unpublishNodes('dataset');
      migrate_static_registration();
      self::setMigrationEndpointName('data_json_1_1', 'json');
    }

    public function setup() {
      $g = $this->createDummyGroup();
    }

    public static function setMigrationEndpointName($api, $name) {
      // Change /data.json path to /json during tests.
      $data_json = open_data_schema_map_api_load($api);
      $data_json->endpoint = $name;
      $result = drupal_write_record('open_data_schema_map', $data_json, 'id');
      if ($result != SAVED_UPDATED) {
        throw new \Exception('There was an error updating the endpoint.');
      }
      drupal_static_reset('open_data_schema_map_api_load_all');
      menu_rebuild();
    }

	public function createDummyGroup() {
      $nodes = $this->getNodeByTitle('Health', TRUE);
      if($nodes) {
        node_delete_multiple(array_keys($nodes));
      }
      $node = new stdClass();
      $node->type = 'group';
      $node->title = 'Health';
      $node->language = LANGUAGE_NONE;
      $node->status = 1;
      node_object_prepare($node);
      $node->uid = 1;
      $node->body[$node->language][0]['value'] = 'Health Body';
      node_save($node);
      return $node;
    }

    public function getNodeByTitle($title, $multiple = FALSE) {
      $query = new EntityFieldQuery();

       $entities = $query->entityCondition('entity_type', 'node')
        ->propertyCondition('title', $title)
        ->range(0,1)
        ->execute();

        if (!empty($entities['node'])) {
          $ids = array_keys($entities['node']);
          $ids = (!$multiple) ? array_shift($ids) : $ids;
          $node = (!$multiple) ? node_load($ids) : node_load_multiple($ids);
        }
        return $node;
    }

    public function nodeAssert($expected, $result)
    {
      foreach ($expected as $field => $value) {
        $this->assertEquals($expected[$field], $result[$field]);
      }

    }

    public function rollback($migrationName, $remainingNodes = 0)
    {
      $migration = Migration::getInstance($migrationName);
      $result = $migration->processRollback();

      // Test rollback
      // TODO: DKAN comes with 4 resources. Remove first so count is 0.
      $rawnodes = node_load_multiple(FALSE, array('type' => 'dataset', 'status' => 1), TRUE);
      $this->assertEquals($remainingNodes, count($rawnodes));
      $count = db_select('migrate_map_' . $migrationName, 'map')
                ->fields('map', array('sourceid1'))
                ->countQuery()
                ->execute()
                ->fetchField();
      $this->assertEquals(0, $count);
    }

    public function migrate($migrationName)
    {
      // Run migration.
      $migration = Migration::getInstance($migrationName);
      $map = new MigrateSQLMap(
        $migrationName,
        array(
          'uuid' => array(
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'description' => 'id',
          ),
        ),
        MigrateDestinationNode::getKeySchema()
      );
      $table = $map->getMapTable();
      dkan_migrate_base_add_modified_column($table);
      $result = $migration->processImport();

      $this->assertNotEquals($result, Migration::RESULT_FAILED);
      $this->assertEquals(0, $migration->errorCount());
      return $migration;
    }

    public function testCKANResourceImport()
    {
      $expected = $result = array();
      $this->migrate('dkan_migrate_base_example_ckan_resources');

      $node = $this->getNodeByTitle('Madison Polling Places Test');
      $file = $node->field_upload['und'][0];
      $body =
      '<p>This is a list and map of polling places in Madison, WI.</p>

<p>Original data here:
  <a href="https://data.cityofmadison.com/Polling-Places/Polling-Places/rtyh-6ucr">https://data.cityofmadison.com/Polling-Places/Polling-Places/rtyh-6ucr</a></p>';
      $format = taxonomy_term_load($node->field_format['und'][0]['tid']);

      $result['title']  = $node->title;
      $expect['title']  = "Madison Polling Places Test";
      $result['body']   = $node->body['und'][0]['value'];
      $expect['body']   = $body;
      $result['format'] = $format->name;
      $expect['format'] = 'csv';
      $result['file']   = substr($file['filename'], 0, 22);
      $expect['file']   = 'Polling_Places_Madison';

      $this->nodeAssert($expect, $result);
    }

    public function CKANDatasetImport()
    {
      $expected = $result = array();
      $this->migrate('dkan_migrate_base_example_ckan_dataset');

      $node = $this->getNodeByTitle('Wisconsin Polling Places Test');

      $result['title']    = $node->title;
      $expect['title']    = "Wisconsin Polling Places Test";
      $result['created']  = $node->created;
      $expect['created']  = "1360559580";
      $result['id']       = $node->uuid;
      $expect['id']       = "eabaa139-4d2c-4ecf-b81e-cad681c3212e";
      // TODO:
      // maintainer
      // maintainer_email
      // licence_title
      $this->nodeAssert($expect, $result);
    }

    public function testCKANDatasetRollback()
    {
      $this->rollback('dkan_migrate_base_example_ckan_dataset');
    }

    public function testCKANResourceRollback()
    {
      $this->rollback('dkan_migrate_base_example_ckan_resources');
    }

    public function testDataJsonImport()
    {
      $expect = $result = array();
      $this->migrate('dkan_migrate_base_example_data_json11');

      $node = $this->getNodeByTitle('Gross Rent over time');
      $node2 = $this->getNodeByTitle('Hospital Compare');
      $group = isset($node->og_group_ref['und'][0]['target_id']) ? node_load($node->og_group_ref['und'][0]['target_id']) : NULL;
      $group2 = isset($node2->og_group_ref['und'][0]['target_id']) ? node_load($node2->og_group_ref['und'][0]['target_id']) : NULL;
      $keyword1 = taxonomy_term_load($node->field_tags['und'][0]['tid']);
      $keyword2 = taxonomy_term_load($node->field_tags['und'][1]['tid']);
      $resource1 = node_load($node->field_resources['und'][0]['target_id']);
      $resource2 = node_load($node->field_resources['und'][1]['target_id']);
      $format1 = taxonomy_term_load($resource1->field_format['und'][0]['tid']);
      $format2 = taxonomy_term_load($resource2->field_format['und'][0]['tid']);

      $result['title']  = $node->title;
      $expect['title']  = "Gross Rent over time";
      $result['body']   = $node->body['und'][0]['value'];
      $expect['body']  = "Here is a description";
      $result['id']  = $node->uuid;
      $expect['id']  = "b6a4942e-fa73-4cbf-804f-1f9eea6d02df";
      $result['keyword1']  = $keyword1->name;
      $expect['keyword1']  = "housing";
      $result['keyword2']  = $keyword2->name;
      $expect['keyword2']  = "rent";
      $result['group']  = $group->title;
      $expect['group']  = "Housing";
      $result['license']  = $node->field_license['und'][0]['value'];
      $expect['license']  = "notspecified";
      $result['modified']  = date_format(date_create($node->modified), 'm d y');
      $expect['modified']  = "06 24 14";
      $result['accessLevel']  = $node->field_public_access_level['und'][0]['value'];
      $expect['accessLevel']  = "public";
      $result['contactPoint']  = $node->field_contact_name['und'][0]['value'];
      $expect['contactPoint']  = "Bruce Wayne";
      $result['contactEmail']  = $node->field_contact_email['und'][0]['value'];
      $expect['contactEmail']  = "bruce@notbatman.com";
      $result['spatial']  = $node->field_spatial_geographical_cover['und'][0]['value'];
      $expect['spatial']  = "Lincoln, Nebraska";
      $result['landingPage']  = $node->field_landing_page['und'][0]['url'];
      $expect['landingPage']  = "http://www.agency.gov/vegetables";
      $result['rights']  = $node->field_rights['und'][0]['value'];
      $expect['rights']  = "This dataset contains Personally Identifiable Information and could not be released for public access.";
      $result['dataStandard']  = $node->field_conforms_to['und'][0]['url'];
      $expect['dataStandard']  = "http://www.agency.gov/common-vegetable-analysis-model";
      $result['describedByType']  = $node->field_data_dictionary_type['und'][0]['value'];
      $expect['describedByType']  = "application/pdf";
      $result['isPartOf']  = $node->field_is_part_of['und'][0]['value'];
      $expect['isPartOf']  = "http://dx.doi.org/10.7927/H4PZ56R2";
      if (module_exists('open_data_federal_extras')) {
        $result['bureauCode']  = $node->field_odfe_bureau_code['und'][0]['value'];
        $expect['bureauCode']  = "010:86";
        $result['programCode']  = $node->field_odfe_program_code['und'][0]['value'];
        $expect['programCode']  = "015:001";
        $result['programCode2']  = $node->field_odfe_program_code['und'][1]['value'];
        $expect['programCode2']  = "015:002";
        $result['primaryITInvestmentUII']  = $node->field_odfe_investment_uii['und'][0]['value'];
        $expect['primaryITInvestmentUII']  = "023-000000001";
        $result['systemOfRecords']  = $node->field_odfe_system_of_records['und'][0]['url'];
        $expect['systemOfRecords']  = "https://www.federalregister.gov/articles/2002/04/08/02-7376/privacy-act-of-1974-publication-in-full-of-all-notices-of-systems-of-records-including-several-new#p-361";
      }
      $result['temporalBegin']  = $node->field_temporal_coverage['und'][0]['value'];
      $expect['temporalBegin']  = "2000-01-15 00:45:00";
      $result['temporalEnd']  = $node->field_temporal_coverage['und'][0]['value2'];
      $expect['temporalEnd']  = "2010-01-15 00:06:00";
      $result['accrualPeriodicity']  = $node->field_frequency['und'][0]['value'];
      $expect['accrualPeriodicity']  = 3;
      $result['describedBy']  = $node->field_data_dictionary['und'][0]['value'];
      $expect['describedBy']  = "http://www.agency.gov/vegetables/definitions.pdf";
      $result['references']  = $node->field_related_content['und'][0]['url'];
      $expect['references']  = "http://www.agency.gov/legumes/legumes_data_documentation.html";
      $result['additional']  = $node->field_additional_info['und'][0]['first'];
      $expect['additional']  = "crazy";
      $result['additional2']  = $node->field_additional_info['und'][0]['second'];
      $expect['additional2']  = "what";
      $result['status']  = $node->status;
      $expect['status']  = 1;
      if (module_exists('dkan_workflow')) {
        $result['moderationState']  = $node->workbench_moderation['current']->state;
        $expect['moderationState']  = "published";
        $result['moderationStateResource1']  = $resource1->workbench_moderation['current']->state;
        $expect['moderationStateResource1']  = "published";
        $result['moderationStateResource2']  = $resource2->workbench_moderation['current']->state;
        $expect['moderationStateResource2']  = "published";
      }

      $result['resource1Name']  = $resource1->title;
      $expect['resource1Name']  = "txt";
      $result['resource1Format']  = $format1->name;
      $expect['resource1Format']  = "txt";
      $result['resource1DownloadUrl']  = $resource1->field_link_api['und'][0]['url'];
      $expect['resource1DownloadUrl']  = "http://example.com/sites/default/files/grossrents_adj.txt";

      $result['resource2Name']  = $resource2->title;
      $expect['resource2Name']  = "csv";
      $result['resource2Format']  = $format2->name;
      $expect['resource2Format']  = "csv";
      $result['resource2DownloadUrl']  = $resource2->field_link_remote_file['und'][0]['uri'];
      $expect['resource2DownloadUrl']  = "https://s3.amazonaws.com/dkan-default-content-files/files/Polling_Places_Madison_0.csv";
      $result['resource2AccessUrl']  = $resource2->field_link_api['und'][0]['url'];
      $expect['resource2AccessUrl']  = "http://demo.getdkan.com/dataset/wisconsin-polling-places";

      $this->nodeAssert($expect, $result);

      // Group should be assigned to the Health group
      // created during test setup instead of create
      // new one based in the incoming data.

      // Assert node is created and group Health is properly assigned
      $expect = $result = array();
      $result['title'] = $node2->title;
      $expect['title'] = 'Hospital Compare';
      $result['group_name'] = $group2->title;
      $expect['group_name'] = 'Health';
      $result['body'] = $group2->body['und'][0]['value'];
      $expect['body'] = 'Health Body';

      $this->nodeAssert($expect, $result);

      // Check Health data is not duplicated.
      $this->assertEquals(1, count($this->getNodeByTitle('Health', TRUE)));

      // TODO:
      // maintainer
      // maintainer_email
      // licence_title

    }

    public function testDataJsonEndpoint() {
      global $base_url;
      $this->migrate('dkan_migrate_base_example_data_json11');
      $expected = array();
      $url = $base_url . '/json';
      $response = drupal_http_request($url);
      if($response->code != 200) throw new Exception('Request '.$url.' failed');
      $datasets = json_decode($response->data)->dataset;
      $dataset = array_filter($datasets,  function($d) {
        return $d->title == 'Gross Rent over time';
      })[0];
      $expected['title'] = 'Gross Rent over time';
      $expected['modified'] = '2014-06-24';
      $this->nodeAssert($expected, (array)$dataset);
    }

    public function testDataJsonHighwater() {
      $this->rollback('dkan_migrate_base_example_data_json11');
      // First run should create some elements and update 0.
      $migration = $this->migrate('dkan_migrate_base_example_data_json11');
      $this->assertGreaterThan(0, $migration->getDestination()->getCreated());
      $this->assertEquals(0, $migration->getDestination()->getUpdated());
      // Second run should create 0 and update 0.
      $migration->getDestination()->resetStats();
      $migration->prepareUpdate();
      $migration = $this->migrate('dkan_migrate_base_example_data_json11');
      $this->assertEquals(0, $migration->errorCount());
      $this->assertEquals(0, $migration->getDestination()->getCreated());
      $this->assertEquals(0, $migration->getDestination()->getUpdated());
      // Tamper source data.
      $data_folder = implode(
        '/',
        array(
          DRUPAL_ROOT,
          drupal_get_path('module', 'dkan_migrate_base_example'),
          'data',
        )
      );
      // Prepare files
      $original_file = implode('/', array($data_folder, 'data11.json'));
      $backup_file = implode('/', array($data_folder, 'data11.json.backup'));
      // Tamper original file
      shell_exec('rm -rf ' . $backup_file);
      shell_exec('cp ' . $original_file . ' ' . $backup_file);
      $file = file_get_contents($original_file);
      $file = str_replace('2014-', '2016-', $file);
      file_unmanaged_save_data($file, $original_file, FILE_EXISTS_REPLACE);
      // Run migration again
      drupal_static_reset('getInstance');
      $migration = $this->migrate('dkan_migrate_base_example_data_json11');
      $this->assertEquals(0, $migration->errorCount());
      $this->assertEquals(0, $migration->getDestination()->getCreated());
      $this->assertGreaterThan(0, $migration->getDestination()->getUpdated());
      // Run Migration for the fourth time.
      $migration->getDestination()->resetStats();
      $migration->prepareUpdate();
      $migration = $this->migrate('dkan_migrate_base_example_data_json11');
      $this->assertEquals(0, $migration->errorCount());
      $this->assertEquals(0, $migration->getDestination()->getCreated());
      $this->assertEquals(0, $migration->getDestination()->getUpdated());
      // Put everything as it was
      shell_exec('rm ' . $original_file);
      shell_exec('mv ' . $backup_file . ' ' . $original_file);
      $this->rollback('dkan_migrate_base_example_data_json11');
    }

    public function tearDown(){
      $this->rollback('dkan_migrate_base_example_data_json11');
    }

    public static function tearDownAfterClass() {
      // module_disable(array('dkan_migrate_base_example'));
      self::setMigrationEndpointName('data_json_1_1', 'data.json');
    }
}

class DKANMigrateBaseTestUnit extends PHPUnit_Framework_TestCase
{
    public function setup() {
        include "dkan_migrate_base.module";
    }

    public function testIsoConversion() {
        $date = "2000-01-15T00:45:00Z";
        $result = dkan_migrate_base_iso_interval_to_timestamp($date);
        $this->assertEquals("01 15 00", date('m d y', $result['from']));
        $this->assertEquals(NULL, $result['to']);

        $date = "2000-01-15T00:45:00Z/P1W";
        $result = dkan_migrate_base_iso_interval_to_timestamp($date);
        $this->assertEquals("01 15 00", date('m d y', $result['from']));
        $this->assertEquals("01 22 00", date('m d y', $result['to']));

        $date = "2000-01-15T00:45:00Z/2010-01-15T00:06:00Z";
        $result = dkan_migrate_base_iso_interval_to_timestamp($date);
        $this->assertEquals("01 15 00", date('m d y', $result['from']));
        $this->assertEquals("01 15 10", date('m d y', $result['to']));

    }
}
