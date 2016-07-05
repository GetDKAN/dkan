<?php

/**
 * Migration class used to import Default Panelized Nodes from fixtures.
 */
class DefaultContentPanelizedNodesImport extends Migration {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    $this->endpoint = isset($arguments['endpoint']) ? $arguments['endpoint'] : drupal_get_path('module', 'dkan_default_content') . '/data/';
    $list_url = isset($arguments['list_url']) ? $arguments['list_url'] : $this->endpoint . 'panelized_node_list.json';
    $item_url = isset($arguments['item_url']) ? $arguments['item_url'] : $this->endpoint . 'panelized_node_item.json';

    parent::__construct($arguments);

    $this->page = isset($arguments['page']) ? $arguments['page'] : '';
    $this->offset = isset($arguments['offset']) ? $arguments['offset'] : '';

    $fields = $this->getPanelizedNodeFields();

    // The source that will feed data to the migration.
    $this->source = new MigrateSourceList(
      new CKANListJSON($list_url, array('page' => $this->page, 'offset' => $this->offset)),
      new CKANItemJSON($item_url, $fields), $fields);

    // This will receive data that originated from the source and has been mapped
    // by the Migration class, and create Drupal objects.
    $this->destination = new MigrateDestinationNode($arguments['content_type']);

    // This will keep track of which source items have been imported and what destination objects they map to.
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
  }

  /**
   * Do extra processing on the node data before its saved.
   */
  public function prepare($entity, $row) {
    // Setup superuser as author.
    $entity->uid = 1;
  }

  /**
   *
   */
  public function complete($entity, $row) {
    // Import panelizer data.
    $this->importPanelizerData($entity, $row->panelizer);
  }

  /**
   *
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
   *
   */
  public function importPanelizerData($entity, $panelizer_data) {

    // Convert panelizer data into array recursively. Almos all functions on Panels expect arrays.
    $panelizer_data = json_decode(json_encode($panelizer_data), true);

    $panelizer_entity = $panelizer_data['panelizer_entity'];
    $panels_display = $panelizer_data['panels_display'];
    $panels_pane = $panelizer_data['panels_pane'];

    // Import panels_display.
    drupal_write_record('panels_display', $panels_display);

    // Import panels_pane.
    foreach ($panels_pane as $pane) {

      $pane['did'] = $panels_display['did'];

      if ($pane['type'] === 'visualization_embed') {
        if (!empty($pane['configuration']['local_source'])) {
          // Get the visualization UUID.
          $viz_uuid = $pane['configuration']['local_source'];
          // The 'local_source' setting needs to be in the format "Title [id:XXX]".
          $visualization = reset(entity_uuid_load('visualization', array($viz_uuid)));
          $pane['configuration']['local_source'] = '';
          if ($visualization) {
            $pane['configuration']['local_source'] = $visualization->title . ' [id:' . $visualization->id . ']';
          }
        }
      }

      var_dump($pane['type']);

      if ($pane['type'] === 'fieldable_panels_pane' && !empty($pane['fpp_data'])) {
        $fpp_data = $pane['fpp_data'];
        list($id_type, $id_value) = explode(':', $pane['subtype']);

        $fpp = new stdClass();
        $fpp->uid = 1;
        $fpp->title = $fpp_data['title'];
        $fpp->bundle = $fpp_data['bundle'];
        $fpp->uuid = $id_value;
        $fpp->link = $fpp_data['link'];
        $fpp->path = $fpp_data['path'];
        $fpp->reusable = $fpp_data['reusable'];
        $fpp->admin_title = $fpp_data['admin_title'];
        $fpp->admin_description = $fpp_data['admin_description'];
        $fpp->category = $fpp_data['category'];

        // Save fieldable panel pane.
        $fpp = fieldable_panels_panes_save($fpp);

        // Save fieldable panel pane fields.
        $this->save_fieldable_panel_pane_field($fpp, $fpp_data);
      }

      var_dump(1);
      drupal_write_record('panels_pane', $pane);
      var_dump(2);
    }

    // Import panelizer_entity.
    var_dump(3);
    $panelizer_entity['did'] = $panels_display['did'];
    $panelizer_entity['entity_id'] = $entity->nid;
    $panelizer_entity['revision_id'] = $entity->vid;
    var_dump(4);
    drupal_write_record('panelizer_entity', $panelizer_entity);
    var_dump(5);
  }

  /**
   * TODO: This function needs to be refactored. There should be a better way to do the same.
   *       Setting field values using an entity metadata wrapper is problematic for Spotlight.
   */
  private function save_fieldable_panel_pane_field($fpp, $fpp_data) {
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

        switch($field_name) {
          /////////////////////////////////
          // Image field.
          /////////////////////////////////
          case 'field_basic_image_caption': {
            $field_data = reset($fpp_data[$field_name]);
            $field_record['field_basic_image_caption_value'] = $field_data['value'];
            $field_record['field_basic_image_caption_summary'] = $field_data['summary'];
            $field_record['field_basic_image_caption_format'] = $field_data['format'];
            drupal_write_record('field_data_' . $field_name, $field_record);
            drupal_write_record('field_revision_' . $field_name, $field_record);
            break;
          }
          case 'field_basic_image_image': {
            $field_data = reset($fpp_data[$field_name]);
            $file = $this->import_file($field_data['image_path']);
            if (!empty($file)) {
              $field_record['field_basic_image_image_fid'] = $file->fid;
              $field_record['field_basic_image_image_alt'] = $field_data['alt'];
              $field_record['field_basic_image_image_title'] = $field_data['title'];
              $field_record['field_basic_image_image_width'] = $field_data['width'];
              $field_record['field_basic_image_image_height'] = $field_data['height'];
              drupal_write_record('field_data_' . $field_name, $field_record);
              drupal_write_record('field_revision_' . $field_name, $field_record);
            }
            break;
          }
          /////////////////////////////////
          // Spotlight field.
          /////////////////////////////////
          case 'field_basic_spotlight_duration': {
            $field_data = reset($fpp_data[$field_name]);
            $field_record['field_basic_spotlight_duration_value'] = $field_data['value'];
            drupal_write_record('field_data_' . $field_name, $field_record);
            drupal_write_record('field_revision_' . $field_name, $field_record);
            break;
          }
          case 'field_basic_spotlight_items': {
            // This is a multi-value field so multiple items need to be processed.
            $spotlight_items = $fpp_data[$field_name];
            foreach($spotlight_items as $key => $spotlight_item) {

              $field_record['delta'] = $key;
              $file = $this->import_file($spotlight_item['image_path']);
              if (!empty($file)) {
                $field_record['field_basic_spotlight_items_title'] = $spotlight_item['title'];
                $field_record['field_basic_spotlight_items_description'] = $spotlight_item['description'];
                $field_record['field_basic_spotlight_items_link'] = $spotlight_item['link'];
                $field_record['field_basic_spotlight_items_alt'] = $spotlight_item['alt'];
                $field_record['field_basic_spotlight_items_fid'] = $file->fid;
                drupal_write_record('field_data_' . $field_name, $field_record);
                drupal_write_record('field_revision_' . $field_name, $field_record);
              }
            }
            break;
          }
          /////////////////////////////////
          // Text field.
          /////////////////////////////////
          case 'field_basic_text_text': {
            $field_data = reset($fpp_data[$field_name]);
            $field_record['field_basic_text_text_value'] = $field_data['value'];
            $field_record['field_basic_text_text_format'] = $field_data['format'];
            drupal_write_record('field_data_' . $field_name, $field_record);
            drupal_write_record('field_revision_' . $field_name, $field_record);
            break;
          }
          default:
            break;
        }
      }
    }
  }

  /**
   *
   */
  private function import_file($path) {
    $file_url = urldecode($path);
    $file_url = preg_replace('!\s+!', ' ', $file_url);
    $file_url = DRUPAL_ROOT . '/' . $this->endpoint . $file_url;
    if (file_exists($file_url)) {
      // Copy file and save on public directory.
      $data = file_get_contents($file_url);
      $name = basename($file_url);
      $uri = 'public://' . $name;
      try {
        $file = file_save_data($data, $uri, FILE_EXISTS_RENAME);
        return $file;

      } catch (Exception $e) {
        $this->queuedMessages[] = array(
          'message' => t('The file %file could not be imported.', array('%file' => $uri)),
          'level' => MigrationBase::MESSAGE_ERROR,
        );
      }
    }

    return array();
  }
}