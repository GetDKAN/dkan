<?php

/**
 * @file
 * Migration class used to import Default Visualization Entities from fixtures.
 */

/**
 * Visualiztion entity fixtures migration class.
 */
class DkanVisualizationEntityImport extends Migration {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {
    parent::__construct($arguments);

    $this->endpoint = isset($arguments['endpoint']) ? $arguments['endpoint'] : drupal_get_path('module', 'dkan_fixtures') . '/data/';
    $list_url = isset($arguments['list_url']) ? $arguments['list_url'] : 'visualization_entity_list.json';
    $list_url = $this->endpoint . $list_url;
    $item_url = isset($arguments['item_url']) ? $arguments['item_url'] : 'visualization_entity_show%3Fid=:id.json';
    $item_url = $this->endpoint . $item_url;

    $this->page = isset($arguments['page']) ? $arguments['page'] : '';
    $this->offset = isset($arguments['offset']) ? $arguments['offset'] : '';

    $fields = $this->getVisualizationEntityFields();

    // The source that will feed data to the migration.
    $this->source = new MigrateSourceList(
      new CKANListJSON($list_url, array('page' => $this->page, 'offset' => $this->offset)),
      new CKANItemJSON($item_url, $fields), $fields);

    // This will receive data that originated from the source and has been
    // mapped by the Migration class, and create Drupal objects.
    $this->destination = new MigrateDestinationEntityAPI('visualization', 've_chart');

    // This will keep track of which source items have been imported and what
    // destination objects they map to.
    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'id' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'description' => 'id',
        ),
      ),
      MigrateDestinationEntityAPI::getKeySchema('visualization')
    );

    // Add Field mappings.
    $this->addFieldMapping('title', 'title');
    $this->addFieldMapping('field_description', 'description');
    $this->addFieldMapping('field_ve_settings', 'settings');
    $this->addFieldMapping('field_file', 'local_file');
    $this->addFieldMapping('uuid', 'uuid');
  }

  /**
   * Prepare row data to be imported.
   */
  public function prepareRow($row) {

    // If a file was used then download file locally and update field settings.
    $settings = json_decode($row->settings);
    $source_url = $settings->source->url;
    if (!empty($source_url)) {
      $path_parts = explode('/', $source_url);
      if ($path_parts[0] === 'files') {
        // The file needs to be copied into the files directory.
        $file = import_file($source_url, DRUPAL_ROOT . '/' . $this->endpoint);
        if ($file) {
          // Add file data in row.
          $row->local_file = (array) $file;
          // Update file path on settings field.
          $settings->source->url = '/' . variable_get('file_public_path', conf_path() . '/files') . '/' . $name;
          $row->settings = json_encode($settings);
        }
        else {
          $this->queuedMessages[] = array(
            'message' => t('%url was not available for download', array('%url' => $source_url)),
            'level' => MigrationBase::MESSAGE_NOTICE,
          );
        }
      }
    }
  }

  /**
   * Do extra processing on the node data before its saved.
   */
  public function prepare($entity, $row) {
    $entity->path['pathauto'] = FALSE;
    // Setup superuser as author.
    $entity->uid = 1;

    // Add resource reference if present.
    $resource_ref[] = array('target_type' => 'node', 'target_uuid' => $row->existing_resource_uuid);
    $entity->field_uuid_resource = array(LANGUAGE_NONE => $resource_ref);
  }

  /**
   * Get fields.
   */
  public function getVisualizationEntityFields() {
    return array(
      "id" => "Visualization ID",
      "type" => "Visualization Bundle",
      "title" => "Title",
      "description" => "Description",
      "settings" => "Visualization settings",
      "existing_resource_uuid" => "The ID of the existing resource",
    );
  }

}
