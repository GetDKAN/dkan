<?php

/**
 * @file
 * Migration File for DKAN Harvest Data.Json.
 */

/**
 * Migration class to harves POD endpoints.
 */
class DatajsonHarvestMigration extends HarvestMigration {

  public $itemUrl;

  /**
   * Constructor.
   */
  public function __construct($arguments) {
    parent::__construct($arguments);
    $this->itemUrl = drupal_realpath($this->dkanHarvestSource->getCacheDir()) .
      '/:id';

    $this->source = new HarvestMigrateSourceList(
      new HarvestList($this->dkanHarvestSource->getCacheDir()),
      new MigrateItemJSON($this->itemUrl),
      array(),
      $this->sourceListOptions
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setFieldMappings($version = '1.1') {
    parent::setFieldMappings();

    $this->addFieldMapping('field_tags', 'keyword');
    $this->addFieldMapping('field_tags:create_term')
      ->defaultValue(TRUE);

    $this->addFieldMapping('field_harvest_source_issued', 'issued');
    $this->addFieldMapping('field_harvest_source_modified', 'modified');
    $this->addFieldMapping('field_public_access_level', 'accessLevel');
    $this->addFieldMapping('uuid', 'identifier');
    $this->addFieldMapping('field_license', 'license');
    $this->addFieldMapping('field_spatial_geographical_cover', 'spatial');
    $this->addFieldMapping('field_frequency', 'accrualPeriodicity');
    $this->addFieldMapping('field_data_dictionary', 'describedBy');
    $this->addFieldMapping('field_landing_page', 'landingPage');
    $this->addFieldMapping('field_rights', 'rights');
    $this->addFieldMapping('field_conforms_to', 'conformsTo');
    $this->addFieldMapping('field_data_dictionary_type', 'describedByType');
    $this->addFieldMapping('field_is_part_of', 'isPartOf');
    $this->addFieldMapping('field_language', 'language');
    $this->addFieldMapping('field_pod_theme', 'theme');
    $this->addFieldMapping('field_pod_theme:create_term')
      ->defaultValue(TRUE);

    if ($version == '1.0') {
      $this->addFieldMapping('field_data_dictionary', 'dataDictionary');
      $this->addFieldMapping('language', 'language');
    }
    else {
      // POD 1.1.
      $this->addFieldMapping('field_related_content', 'references');
    }
    if (module_exists('open_data_federal_extras')) {
      $this->addFieldMapping('field_odfe_bureau_code', 'bureauCode');
      $this->addFieldMapping('field_odfe_program_code', 'programCode');
      $this->addFieldMapping('field_odfe_data_quality', 'dataQuality');
      $this->addFieldMapping('field_odfe_investment_uii', 'primaryITInvestmentUII');
      $this->addFieldMapping('field_odfe_system_of_records', 'systemOfRecords');
    }
    // dkan_dataset_metadata_source.
    if (module_exists('dkan_dataset_metadata_source')) {
      $this->addFieldMapping('field_metadata_sources', 'metadata_sources');
    }
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);

    // The modified field is required. https://project-open-data.cio.gov/v1.1/schema/#modified.
    // Check the value when harvesting, it should be a valid ISO 8601 Date.
    if(isset($row->modified)) {
      $d = dkan_dataset_validate_date($row->modified) ? true : false;
      if ($d) {
        // Valid date, no change.
      }
      elseif (substr($row->modified, 0, 2) == 'R/') {
        try {
          $v = substr($row->modified, 2);
          $valid = new DateInterval($v);
        }
        catch (Exception $e) {
          $message = t(
            'Modified value unknown or bad format (@value)',
            array(
              '@value' => $v,
            ));
          $this->saveMessage($message);
        }
      }
      else {
        $message = t(
          'The modified value is not a valid ISO 8601 Date (@value)',
          array(
            '@value' => $row->modified,
          ));
        $this->saveMessage($message);
      }
    }

    // The issued field is not required. When missing, use the modified field to
    // have consistent dataset display.
    // https://project-open-data.cio.gov/v1.1/schema#issued
    if (!isset($row->issued) && isset($row->modified)) {
      $d = dkan_dataset_validate_date($row->modified) ? true : false;
      if ($d) {
        $row->issued = $row->modified;
      }
    }

    // Process contact name and email.
    if (is_object($row->contactPoint)
      && $row->contactPoint->hasEmail) {
      $row->contact_email = str_replace("mailto:", "", $row->contactPoint->hasEmail) ? str_replace("mailto:", "", $row->contactPoint->hasEmail) : $row->contactPoint->hasEmail;
      $row->contact_name = isset($row->contactPoint->fn) ? $row->contactPoint->fn : '';
      if (!filter_var($row->contact_email, FILTER_VALIDATE_EMAIL)) {
        $row->contact_email = '';
      }
    }

    if ($row->accessLevel == 'restricted public') {
      $row->accessLevel = 'restricted';
    }
    if ($row->accessLevel == 'non-public') {
      $row->accessLevel = 'private';
    }

    // Process Temporal Coverage. The mapping are defined in the base main
    // class, we just need to set the properties.
    if (isset($row->temporal)) {
      $date = $row->temporal;
      if (strpos($row->temporal, '/')) {
        $date = explode("/", $row->temporal);
      }
      elseif (strpos($row->temporal, '-')) {
        $date = explode("-", $row->temporal);
      }
      // The first key is the start date of the 'temporal coverage' and the second key is the
      // end date of the 'temporal coverage'.
      foreach ($date as $key => &$value) {
        // Check if this is a time interval on the second Argument.
        if ($key == 1
          && preg_match("/P(\d*Y)?(\d*M)?(\d*D)?(\d*W)?T?(\d*H)?(\d*M)?(\d*S)?/", $value)) {
          try {
            $value_diff = new DateInterval($value);
            // Get the date from the first segment. This should be represented
            // as a timestamp by now.
            $value_date = new DateTime();
            $value_date->setTimestamp($date[0]);
            $value_date->add($value_diff);
          }
          catch (Exception $e) {
            $message = t(
              'Cannot parse temporal coverage interval @value',
              array(
                '@value' => $value,
              ));
            $this->saveMessage($message);
          }
        }
        // Support 4 digits year time strings.
        elseif (preg_match("@^\d{4}$@", $value)) {
          $value_date = new DateTime();

          // If this is the end date then set it to the last day of the year.
          if ($key == 1) {
            $value_date->setDate($value, 12, 31);
          }
          // If this is the start date then set it to the first day of the year.
          else {
            $value_date->setDate($value, 1, 1);
          }

          $value_date->setTime(0, 0);
        }
        // Fallback to full date/time format.
        else {
          try {
            $value_date = new DateTime($value);
          }
          catch (Exception $e) {
            $message = t(
              'Cannot parse temporal coverage value @value',
              array(
                '@value' => $value,
              ));
            $this->saveMessage($message);
          }
        }

        if ($value_date) {
          $value = $value_date->getTimestamp();
        }
        else {
          $message = t(
            'Cannot determine temporal coverage value. Please review the formatting standards at https://project-open-data.cio.gov/v1.1/schema/#temporal'
          );
          $this->saveMessage($message);
        }
      }

      if (isset($date[0])) {
        $row->temporal_coverage_from = $date[0];
      }
      if (isset($date[1])) {
        $row->temporal_coverage_to = $date[1];
      }
    }

    // field_odfe_data_quality expects "true" or "false" as input values.
    if (isset($row->dataQuality)) {
      if ($row->dataQuality === FALSE) {
        $row->dataQuality = 'false';
      }
      elseif ($row->dataQuality === TRUE) {
        $row->dataQuality = 'true';
      }
    }
  }

  /**
   * Implements prepare.
   */
  public function prepare($dataset_prepare, $row) {
    parent::prepare($dataset_prepare, $row);

    // Prepare group data.
    $row->group = $this->prepareRowGroup($row->publisher);

    // Prepare resources data.
    if (isset($row->distribution)) {
      $row->resources = $this->prepareRowResources($row->distribution);
    }
  }

  /**
   * Prepare list of resources that need to be imported based on the row data.
   *
   * @param object $resources_row_data
   *   Resources data present on row.
   *
   * @return array
   *   An array with all resources data ready to be imported or empty if
   *         no resources where found.
   */
  public function prepareRowResources($resources_row_data) {
    $resources = array();

    foreach ($resources_row_data as $resource_row_data) {
      // Fallback to the accessURL if the downloadURL is not available.
      $url = $resource_row_data->downloadURL;
      if (empty($url)) {
        $url = $resource_row_data->accessURL;
      }

      $format = $resource_row_data->format;

      if (empty($format)) {
        // If mediayType is defined, then get the right format based on that mimetype.
        if (!empty($resource_row_data->mediaType)) {
          include_once DRUPAL_ROOT . '/includes/file.mimetypes.inc';
          $mimetype_mappings = file_mimetype_mapping();
          $mimetype_keys = array_keys($mimetype_mappings['mimetypes'], $resource_row_data->mediaType);
          // Get the candidate extensions from the mimetype_keys.
          $extensions_lookup = array();
          foreach ($mimetype_keys as $mimetype_key) {
            $extensions_lookup = array_merge($extensions_lookup, array_keys($mimetype_mappings['extensions'], $mimetype_key));
          }
          if (!empty($extensions_lookup)) {
            $format = array_pop($extensions_lookup);
          } else {
            $format = '';
          }
        }
      }

      list($status, $return) = $this->prepareResourceHelper($url,
        $format,
        $resource_row_data->title,
        NULL,
        $resource_row_data->description);

      if ($status == TRUE) {
        $resources[] = $return;
      }
      else {
        $this->reportMessage($return);
      }
    }

    return $resources;
  }

  /**
   * Prepare group that needs to be imported based on the row data.
   *
   * @param object $group_row_data
   *   $group_row_data Group data present on row.
   *
   * @return object
   *   An object value with the group data needed to import the group.
   */
  public function prepareRowGroup($group_row_data) {

    $group = new stdClass();
    $group->name = $group_row_data->name;

    return $group;
  }

}
