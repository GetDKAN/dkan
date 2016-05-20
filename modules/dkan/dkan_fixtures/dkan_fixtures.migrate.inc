<?php
/**
 * @file
 * Migration file for dkan_fixtures.
 */

/**
 * Implements hook_migrate_api().
 */
function dkan_fixtures_migrate_api() {
  $api = array(
    // Migrate API, not CKAN's of course.
    'api' => 2,
    'groups' => array(
      'dkan_fixtures' => array(
        'title' => t('DKAN Fixtures'),
      ),
    ),
    'migrations' => array(
      'ckan_dataset_fixtures' => array(
        'class_name' => 'MigrateCkanDatasetImport',
        'group_name' => 'dkan_fixtures',
        'title' => t('DKAN Dataset Fixtures'),
      ),
      'ckan_resource_fixtures' => array(
        'class_name' => 'MigrateCkanResourceImport',
        'group_name' => 'dkan_fixtures',
        'title' => t('DKAN Resource Fixtures'),
      ),
      'ckan_group_fixtures' => array(
        'class_name' => 'MigrateCkanGroupImport',
        'group_name' => 'dkan_fixtures',
        'title' => t('DKAN Group Fixtures'),
      ),
    ),
  );
  return $api;
}

class MigrateCkanDatasetImport extends MigrateCkanDatasetBase {
  /**
   * Here we go.
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = '_list.json';
      $arguments['item_url'] = 'package_show?id=:id.json';
    }
    parent::__construct($arguments);

    $fields = $this->additionalFields();
    foreach ($fields as $id => $field) {
      $this->addFieldMapping($field, $field);
    }
  }

  /**
   * Gets additional fields.
   */
  public function additionalFields() {
    $api = open_data_schema_map_api_load('ckan_package_show');
    $fields = open_data_schema_map_additional_fields($api);
    $additional_fields = array();
    foreach ($fields as $id => $field) {
      if ($field != 'field_resources' &&
          $field != 'field_contact_email' &&
          $field != 'field_spatial' &&
          $field != 'field_tags' &&
          $field != 'field_additional_info') {
        $additional_fields[] = $field;
      }
    }
    return $additional_fields;
  }

  /**
   * Implements prepare.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    if ($row->private == 'Published') {
      $node->status = 1;
      foreach ($row->resource_ids as $resource) {

        $resource = node_load($resource);
        $resource->status = 1;
        node_save($resource);
      }
    }
    if (!$node->uid) {
      $node->uid = 1;
    }
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);
    $this->addAdditionalFields($row);
    $this->createAndMapGroups($row);
  }

  /**
   * Adds Additional fields to $row.
   */
  public function addAdditionalFields(&$row) {
    $fields = $this->additionalFields();
    // Uses 'value' if available.
    foreach ($fields as $id => $field) {
      if (is_array($row->dkan_additional_fields->{$field}) && $row->dkan_additional_fields->{$field}) {
        if (is_object($row->dkan_additional_fields->{$field}[0])) {
          foreach ($row->dkan_additional_fields->{$field} as $num => $field_value) {
            if (isset($row->dkan_additional_fields->{$field}[$num]->value)) {
              $row->{$field}[$num] = $row->dkan_additional_fields->{$field}[$num]->value;
            }
          }
        }
        else {
          foreach ($row->dkan_additional_fields->{$field} as $num => $field_value) {
            if (isset($row->dkan_additional_fields->{$field}[$num]->value)) {
              $row->{$field}[$num] = $row->dkan_additional_fields->{$field}[$num];
            }
          }
        }
      }
      elseif (is_object($row->dkan_additional_fields->{$field})) {
        if (isset($row->dkan_additional_fields->{$field}->value)) {
          $row->{$field} = $row->dkan_additional_fields->{$field}->value;
        }
      }
      else {
        $row->{$field} = $row->dkan_additional_fields->{$field};
      }
    }
  }
}

class MigrateCkanResourceImport extends MigrateCkanResourceBase {
  /**
   * Here we go.
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'package_list.json';
      $arguments['item_url'] = 'package_show?id=:id.json';
    }
    parent::__construct($arguments);
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);
    // Replace with absolute path.
    if (isset($row->url) && trim ($row->url)) {
      if (strpos($row->url, 'http://') === FALSE && strpos($row->url, 'https://') === FALSE) {
        // Fixes transliteration issues.
        $row->url = urldecode($row->url);
        $row->url = preg_replace('!\s+!', ' ', $row->url);
        $row->url = DRUPAL_ROOT . '/' . $this->endpoint . $row->url;
        if (file_exists($row->url)) {
          // Create file.
          $data = file_get_contents($row->url);
          $name = basename($row->url);
          $uri = 'public://' . $name;
          $file = file_save_data($data, $uri, FILE_EXISTS_REPLACE);
          $row->file = $file->fid;
          // Unset other fields populated from url.
          unset($row->file_remote_link);
          unset($row->link);
        }
        else {
          $this->queuedMessages[] = array(
            'message' => t('%url was not available for download', array('%url' => $row->url)),
            'level' => MigrationBase::MESSAGE_NOTICE,
          );
        }
      }
    }
  }

  /**
   * Implements prepare.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    // If the url is a url and has a common domain or file extension treat it
    // as a remote link.
    if (isset($row->url) && (strpos($row->url, 'http://') !== FALSE || strpos($row->url, 'https://') !== FALSE)) {
      if ($ext = array_pop(explode('.', $row->url))) {
        if ($ext == 'html' ||
          $ext = 'shtml' ||
          $ext = 'com' ||
          $ext = 'net' ||
          $ext = 'info') {
            $node->field_link_remote_file = array();
            $node->field_upload = array();
            $node->field_link_api['und'][0]['url'] = $row->url;
        }
      }
    }
  }
}

class MigrateCkanGroupImport extends MigrateCkanGroupBase {
  /**
   * Here we go.
   */
  public function __construct($arguments) {
    parent::__construct($arguments);
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'group_list.json';
      $arguments['item_url'] = 'group_show?id=:id.json';
    }
  }

  /**
   * Implements prepare.
   */
  public function prepare($node, $row) {
    $node->uid = 1;
    $node->status = 1;
    $node->field_image[LANGUAGE_NONE][] = array(
      'fid' => $row->file,
      'display' => 1,
      'description' => '',
    );
    $node->body[LANGUAGE_NONE][0]['value_format'] = 'html';
    $node->body[LANGUAGE_NONE][0]['format'] = 'html';
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);
    // Replace with absolute path.
    if (isset($row->image_display_url) && trim ($row->image_display_url)) {
      if (strpos($row->image_display_url, 'http://') === FALSE && strpos($row->image_display_url, 'https://') === FALSE) {
        // Fixes transliteration issues.
        $row->image_display_url = urldecode($row->image_display_url);
        $row->image_display_url = preg_replace('!\s+!', ' ', $row->image_display_url);
        $row->image_display_url = DRUPAL_ROOT . '/' . $this->endpoint . $row->image_display_url;
        if (file_exists($row->image_display_url)) {
          // Create file.
          $data = file_get_contents($row->image_display_url);
          $name = basename($row->image_display_url);
          $uri = 'public://' . $name;
          $file = file_save_data($data, $uri, FILE_EXISTS_REPLACE);
          $row->file = $file->fid;
          // Unset other fields populated from url.
          unset($row->file_remote_link);
          unset($row->link);
        }
        else {
          $this->queuedMessages[] = array(
            'message' => t('%url was not available for download', array('%url' => $row->image_display_url)),
            'level' => MigrationBase::MESSAGE_NOTICE,
          );
        }
      }
    }
  }
}
