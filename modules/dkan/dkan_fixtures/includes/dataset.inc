<?php

/**
 * @file
 * Migration class for dataset fixtures.
 */

/**
 * Dataset class, based on the CKAN migration class.
 */
class DkanDatasetImport extends MigrateCkanDatasetBase {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'package_list.json';
      $arguments['item_url'] = 'package_show%3Fid=:id.json';
    }
    parent::__construct($arguments);

    $fields = $this->additionalFields();
    foreach ($fields as $id => $field) {
      $this->addFieldMapping($field, $field, FALSE);
    }

    $this->addFieldMapping('field_topic', 'topic_names');
    $this->addFieldMapping('path', 'path');
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
        $field != 'field_tags' &&
        $field != 'field_topic' &&
        $field != 'field_additional_info') {
        $additional_fields[] = $field;
      }
    }
    return $additional_fields;
  }

  /**
   * Prepare all data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    $this->addAdditionalFields($row);
    $this->createAndMapGroups($row);

    // If the License URI is available ODSM will return that on the
    // 'license_title' field instead of the ID. In order to import the data we
    // need to always work with the URI ID so we need to do a convertion.
    if (isset($row->license_title)) {
      $row->license_title = $this->getLicenseId($row->license_title);
    }

    // Process topics.
    $topics = taxonomy_vocabulary_machine_name_load('dkan_topics');
    if (isset($row->dkan_additional_fields->field_topic)) {
      foreach ($row->dkan_additional_fields->field_topic as $topic) {
        $topic = $this->createTax($topic->value, 'dkan_topics', $topics->vid);
        $row->topic_names[] = $topic->name;
      }
    }
  }

  /**
   * Do extra processing before the node is saved.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    $node->path['pathauto'] = FALSE;
    if (!$row->field_additional_info) {
      $node->field_additional_info = array();
    }

    if (isset($row->dkan_additional_fields->field_spatial->geo_type)) {
      $node->field_spatial[LANGUAGE_NONE][0] = (array) $row->dkan_additional_fields->field_spatial;
    }

    if (isset($row->dkan_additional_fields->field_related_content[0])) {
      foreach ($row->dkan_additional_fields->field_related_content as $key => $link) {
        $node->field_related_content[LANGUAGE_NONE][$key] = (array) $link;
      }
    }

    if ($row->private == 'Published') {
      $node->status = NODE_PUBLISHED;
      foreach ($row->resource_ids as $resource) {
        $resource = node_load($resource);
        $resource->status = NODE_PUBLISHED;
        node_save($resource);
      }
    }

    if (!$node->uid) {
      $node->uid = 1;
    }
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

  /**
   * Get license ID based on license URI.
   */
  private function getLicenseId($licenseURI) {
    $licenses = dkan_dataset_content_types_license_subscribe();
    foreach ($licenses as $license_id => $license_data) {
      if (isset($license_data['uri']) && ($license_data['uri'] === $licenseURI)) {
        return $license_id;
      }
    }

    return '';
  }

}
