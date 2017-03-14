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

    $this->addFieldMapping('changed', 'modified');
    $this->addFieldMapping('created', 'issued');
    $this->addFieldMapping('field_public_access_level', 'accessLevel');
    $this->addFieldMapping('uuid', 'identifier');
    $this->addFieldMapping('field_license', 'license');
    $this->addFieldMapping('field_spatial_geographical_cover', 'spatial');
    $this->addFieldMapping('field_temporal_coverage', 'temporalBegin');
    $this->addFieldMapping('field_temporal_coverage:to', 'temporalEnd');
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
   * Creates list of fields for Data.json Dataset.
   */
  public function getDataJsonDatasetFields($version = '1.1') {
    $common_fields = array(
        // “Common Core” Required Fields.
      "title" => "Title",
      "description" => "Description",
      "keyword" => "Tags",
      "modified" => "Last Update",
      "publisher" => "Publisher",
      "contactPoint" => "Contact Name",
      "identifier" => "Unique Identifier",
      "accessLevel" => "Public",
        // “Common Core” Required-if-Applicable Fields.
      "bureauCode" => " Bureau Code",
      "programCode" => "Program Code",
      "license" => "License",
      "spatial" => "Spatial",
      "temporal" => " Temporal",
        // Beyond Common Core.
      "dataQuality" => " Data Quality",
      "distribution" => "Distribution",
      "issued" => "Release Date",
      "language" => " Language",
      "references" => " Related Documents ",
      "systemOfRecords" => " System of Records",
    );
    if ($version == '1.0') {
      return $common_fields + array(
        "mbox" => "Contact Email",
        "accessLevelComment" => " Access Level Comment",
        "accessURL" => "Download",
        "webService" => " Endpoint ",
        "format" => " Format",
        // Beyond Common Core.
        "theme" => "Category",
        "dataDictionary" => "Data Dictionary",
        "accrualPeriodicity" => "Frequency",
        "landingPage" => "Homepage",
        "PrimaryITInvestmentUII" => "Primary IT Investment",
      );
    }
    elseif ($version == '1.1') {
      return $common_fields + array(
        "rights" => " Rights",
        "accrualPeriodicity" => " Frequency",
        "conformsTo" => " Data Standard URI",
        "describedBy" => "Data Dictionary",
        "describedByType" => "Data",
        "isPartOf" => " Collection",
        "landingPage" => "Homepage",
        "primaryITInvestmentUII" => "Primary",
        "theme" => "Theme",
      );
    }
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    parent::prepareRow($row);
    if (property_exists($row, 'accrualPeriodicity')) {
      $row->accrualPeriodicity = dkan_dataset_content_types_iso2frequency($row->accrualPeriodicity);
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
   *        Resources data present on row.
   *
   * @return array
   *         An array with all resources data ready to be imported or empty if
   *         no resources where found.
   */
  public function prepareRowResources($resources_row_data) {
    $resources = array();

    foreach ($resources_row_data as $resource_row_data) {
      $resource = $this->prepareResourceHelper($resource_row_data->downloadURL,
        $resource_row_data->format,
        $resource_row_data->title,
        NULL,
        $resource_row_data->description);

      if ($resource) {
        $resources[] = $resource;
      }
    }

    return $resources;
  }

  /**
   * Prepare group that needs to be imported based on the row data.
   *
   * @param object $group_row_data
   *        $group_row_data Group data present on row.
   *
   * @return object
   *         An object value with the group data needed to import the group.
   */
  public function prepareRowGroup($group_row_data) {

    $group = new stdClass();
    $group->name = $group_row_data->name;

    return $group;
  }

}
