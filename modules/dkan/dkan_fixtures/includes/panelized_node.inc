<?php

/**
 * @file
 * Migration class used to import Default Panelized Nodes from fixtures.
 */

/**
 * Panelized nodes migrate class.
 */
class DkanPanelizedNodesImport extends Migration {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {

    $this->endpoint = isset($arguments['endpoint']) ? $arguments['endpoint'] : drupal_get_path('module', 'dkan_fixtures') . '/data/';
    $list_url = isset($arguments['list_url']) ? $arguments['list_url'] : 'panelized_node_list.json';
    $list_url = $this->endpoint . $list_url;
    $item_url = isset($arguments['item_url']) ? $arguments['item_url'] : 'panelized_node_item.json';
    $item_url = $this->endpoint . $item_url;

    parent::__construct($arguments);

    $this->page = isset($arguments['page']) ? $arguments['page'] : '';
    $this->offset = isset($arguments['offset']) ? $arguments['offset'] : '';

    $fields = $this->getPanelizedNodeFields();

    // The source that will feed data to the migration.
    $this->source = new MigrateSourceList(
      new CKANListJSON($list_url, array('page' => $this->page, 'offset' => $this->offset)),
      new CKANItemJSON($item_url, $fields), $fields);

    // This will receive data that originated from the source and has been
    // mapped by the Migration class, and create Drupal objects.
    $this->destination = new MigrateDestinationNode($arguments['content_type']);

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
      MigrateDestinationNode::getKeySchema()
    );

    // Add Field mappings.
    $this->addFieldMapping('title', 'title');
    $this->addFieldMapping('uuid', 'uuid');
    $this->addFieldMapping('body', 'body_value');
    $this->addFieldMapping('body:summary', 'body_summary');
    $this->addFieldMapping('body:format', 'body_format');
  }

  /**
   * Prepare row data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    // Prepare 'body' field for field mapping.
    $body = $row->body->und[0];
    $row->body_value = $body->value;
    $row->body_summary = $body->summary;
    $row->body_format = $body->format;
  }

  /**
   * Do extra processing on the node data before its saved.
   */
  public function prepare($entity, $row) {
    $entity->path['pathauto'] = FALSE;
    // Setup superuser as author.
    $entity->uid = 1;
  }

  /**
   * Do extra processing after import.
   */
  public function complete($entity, $row) {
    // Import panelizer data.
    $this->importPanelizerData($entity, $row->panelizer);
  }

  /**
   * Get fields.
   */
  public function getPanelizedNodeFields() {
    return array(
      "id" => "Node ID",
      "uuid" => "UUID",
      "type" => "Content Type",
      "title" => "Title",
    );
  }

  /**
   * Import all data related with panelizer.
   */
  public function importPanelizerData($entity, $panelizer_data) {

    // Convert panelizer data into array recursively.
    // Almost all functions on Panels expect arrays.
    $panelizer_data = json_decode(json_encode($panelizer_data), TRUE);

    $panelizer_entity = $panelizer_data['panelizer_entity'];
    $panels_display = $panelizer_data['panels_display'];
    $panels_pane = $panelizer_data['panels_pane'];

    // Import panels_display.
    drupal_write_record('panels_display', $panels_display);

    // Import panels_pane.
    foreach ($panels_pane as $pane) {

      $pane['did'] = $panels_display['did'];

      // Import Visualization Embeds.
      if ($pane['type'] === 'visualization_embed') {
        if (!empty($pane['configuration']['local_source'])) {
          // Get the visualization UUID.
          $viz_uuid = $pane['configuration']['local_source'];
          // The 'local_source' setting needs to be in  format "Title [id:XXX]".
          $visualization = reset(entity_uuid_load('visualization', array($viz_uuid)));
          $pane['configuration']['local_source'] = '';
          if ($visualization) {
            $pane['configuration']['local_source'] = $visualization->title . ' [id:' . $visualization->id . ']';
          }
        }
      }

      // Import Fieldable Panel Panes.
      if ($pane['type'] === 'fieldable_panels_pane' && !empty($pane['fpp_data'])) {

        $pane['subtype'] = 'uuid:' . $pane['uuid'];

        $fpp_data = $pane['fpp_data'];

        $fpp = new stdClass();
        $fpp->uid = 1;
        $fpp->title = $fpp_data['title'];
        $fpp->bundle = $fpp_data['bundle'];
        $fpp->uuid = $pane['uuid'];
        $fpp->link = $fpp_data['link'];
        $fpp->path = $fpp_data['path'];
        $fpp->reusable = $fpp_data['reusable'];
        $fpp->admin_title = $fpp_data['admin_title'];
        $fpp->admin_description = $fpp_data['admin_description'];
        $fpp->category = $fpp_data['category'];

        // Save fieldable panel pane.
        $fpp = fieldable_panels_panes_save($fpp);

        // Save fieldable panel pane fields.
        $this->saveFieldablePanelPaneField($fpp, $fpp_data);
      }

      drupal_write_record('panels_pane', $pane);
    }

    // Import panelizer_entity.
    $panelizer_entity['did'] = $panels_display['did'];
    $panelizer_entity['entity_id'] = $entity->nid;
    $panelizer_entity['revision_id'] = $entity->vid;
    drupal_write_record('panelizer_entity', $panelizer_entity);
  }

  /**
   * Import Fieldable Panel Panes.
   */
  private function saveFieldablePanelPaneField($fpp, $fpp_data) {
    $fields = field_info_instances('fieldable_panels_pane', $fpp->bundle);

    foreach ($fields as $field_name => $field_spec) {

      if (isset($fpp_data[$field_name])) {
        // Setup basic field information (common to all field types).
        $field_record = array();
        $field_record['entity_type'] = 'fieldable_panels_pane';
        $field_record['bundle'] = $fpp->bundle;
        $field_record['entity_id'] = $fpp->fpid;
        $field_record['revision_id'] = $fpp->vid;
        $field_record['delta'] = 0;
        $field_record['language'] = LANGUAGE_NONE;

        // Import additional fpp data.
        $field_data = $fpp_data[$field_name];

        if (empty($field_data)) {
          try {
            // Save record as it is.
            drupal_write_record('field_data_' . $field_name, $field_record);
            drupal_write_record('field_revision_' . $field_name, $field_record);

          }
          catch (Exception $e) {
            drupal_set_message("There was an error saving the FFP field :field", array('field' => $field_name));
          }
        }
        else {
          // Add extra field data. 'Spotlight' FFPs for example have more than
          // one field item.
          foreach ($field_data as $key => $field_item_data) {
            $field_record['delta'] = $key;
            // Process all columns inside field item.
            foreach ($field_item_data as $column => $value) {
              if ($column === 'image_path') {
                // Import the image and save fid.
                $orig_file_dir = DRUPAL_ROOT . '/' . $this->endpoint;
                $file = dkan_fixtures_import_file($value, $orig_file_dir);
                $field_record[$field_name . '_fid'] = $file->fid;
              }
              else {
                $field_record[$field_name . '_' . $column] = $value;
              }
            }

            try {
              // Save records in DB.
              drupal_write_record('field_data_' . $field_name, $field_record);
              drupal_write_record('field_revision_' . $field_name, $field_record);

            }
            catch (Exception $e) {
              drupal_set_message("There was an error saving the FFP field :field", array('field' => $field_name));
            }
          }
        }
      }
    }
  }

}
