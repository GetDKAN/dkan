<?php

/**
 * @file
 * Migration Class for Resources.
 */

/**
 * A dynamic migration that is reused for each source CSV file.
 */
class MigrateCkanResourceBase extends MigrateCkanBase {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {
    parent::__construct($arguments);

    $this->highwaterField = array(
      'name' => 'revision_timestamp',
    );

    $fields = array(
      "name" => "Name",
      "id" => "UUID",
      "description" => "Description",
      "format" => "Format",
      "created" => "Created Date",
      "last_modified" => "Modified Date",
      "url" => "URL",
      "revision_id" => "Revision ID",
      // Not sure if this is just Mimetype.
      "mimetype_inner" => "Mimetype Inner",
      // This doesn't actually exist but we are adding it later in prepareRow.
      "uid" => "User ID",
      "file" => "file",
    );

    $this->endpoint = isset($arguments['endpoint']) ? $arguments['endpoint'] : dirname(__FILE__) . '/modules/dkan_migrate_base_example/data/';
    $list_url = isset($arguments['list_url']) ? $arguments['list_url'] : 'resource_list';
    $item_url = isset($arguments['item_url']) ? $arguments['item_url'] : 'resource_show?id=:id';

    $this->page = isset($arguments['page']) ? $arguments['page'] : '';
    $this->offset = isset($arguments['offset']) ? $arguments['offset'] : '';
    $this->ids = isset($arguments['ids']) ? $arguments['ids'] : '';

    if ($list_url == 'resource_list') {
      $file_name = 'public://ckan-migrate-resource_list_' . get_class($this);
      if (!file_exists($file_name)) {
        drupal_set_message(t('ID list resource_show downloaded locally as %filename', array('%filename' => $file_name)));
        // CKAN doesn't have a resource_list endpoint. This function fakes it.
        dkan_migrate_base_create_resource_list($this->endpoint, $file_name, TRUE);
        variable_set('dkan_resource_list_' . get_class($this), date('r', time()));
      }
      else {
        $date = variable_get('dkan_resource_list_' . get_class($this), t('unknown'));
        drupal_set_message(t('CKAN Resources ID file for %migration migration downloaded locally. Last updated %date.', array('%date' => $date, '%migration' => get_class($this))));
      }
      $list_url = file_create_url($file_name);
      $list_url = $file_name;
    }

    // Add endpoint for local file.
    if (strpos($list_url, 'http://') === FALSE && strpos($list_url, 'public://') === FALSE) {
      $list_url = $this->endpoint . $list_url;
    }
    if (strpos($item_url, 'http://') === FALSE && strpos($item_url, 'public://') === FALSE) {
      $item_url = $this->endpoint . $item_url;
    }

    $this->source = new MigrateSourceList(new CKANListJSON(
      $list_url,
      array(
        'page' => $this->page,
        'offset' => $this->offset,
        'ids' => $this->ids,
      )
    ),
    new CKANItemJSON($item_url, $fields), $fields);

    $this->map = new MigrateSQLMap(
            $this->machineName,
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

    $this->destination = new MigrateDestinationNode('resource', array('text_format' => 'html'));
    $this->addFieldMapping('id', 'uuid');
    $this->addFieldMapping('title', 'name');
    $this->addFieldMapping('created', 'created');
    $this->addFieldMapping('body', 'description');
    $this->addFieldMapping('field_format', 'format');
    $this->addFieldMapping('field_upload', 'file');
    $this->addFieldMapping('field_link_remote_file', 'file_remote_link');
    $this->addFieldMapping('field_link_api', 'link');
    $this->addFieldMapping('og_group_ref', 'group_ids');
    $this->addFieldMapping('changed', 'last_modified');
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    // TODO:
    // + Find way to get user name for creator of resource
    // + Improve preview for files stuck as links.
    $row->created = $this->StringToTime($row->created);
    $row->created = $this->StringToTime($row->last_modified);
    $row->group_ids = isset($row->resource_group_id) ? array($row->resource_group_id) : '';
    $row->last_modified = $this->StringToTime($row->last_modified);

    $row->name = $row->name ? $row->name : $row->description;

    // Tax terms in Drupal are case sensitive. It is better to have a single
    // 'html' term instead of 'html' and 'HTML'.
    // TODO: move to hook_node_update in dkan_dataset.
    $format = taxonomy_vocabulary_machine_name_load('format');
    $remote_mime = remote_mime($row->url);
    $row->format = strtolower($row->format);
    $this->createTax($row->format, 'format', $format->vid);

    // Decide which of DKAN's three fields is best for resource file.
    if ($row->url_type == 'upload' || $row->resource_type == 'file.upload' || $row->resource_type == 'file' || $remote_mime) {
      $name = explode('/', $row->url);
      $name = $name[count($name) - 1];
      $uri = 'public://' . $name;
      $file = $this->downloadExternalFile($row->url, $uri);
      $row->file = $file['fid'];
    }
    else {
      // CKAN API doesn't make it clear if file is link to API or just file.
      $field = field_info_instance('node', 'field_link_remote_file', 'resource');
      $extensions = explode(' ', $field['settings']['file_extensions']);
      if (in_array($row->format, $extensions) && $row->format != 'html') {
        $row->file_remote_link = $row->url;
      }
      else {
        $row->link = $row->url;
      }
    }
  }

  /**
   * Implements prepare.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    // UUID doesn't show up as a field so had to do this.
    $node->uuid = $row->id;
    $row->format = isset($row->format) && $row->format ? $row->format : 'data';
  }

}
