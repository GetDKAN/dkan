<?php

/**
 * Migration class used to import Default Datasets from fixtures.
 */
class DefaultContentDatasetImport extends MigrateCkanDatasetImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'package_list.json';
      $arguments['item_url'] = 'package_show?id=:id.json';
    }

    // Add field mapping for topics.
    $this->addFieldMapping('field_topic', 'topic_names');

    parent::__construct($arguments);
  }

  /**
   * Prepare row data for processing.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);

    // If the License URI is available ODSM will return that on the 'license_title' field instead of the ID.
    // In order to import the data we need to always work with the URI ID so we need to do a convertion.
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