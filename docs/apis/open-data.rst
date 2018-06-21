Open Data APIs
==============

In addition to the Drupal and CKAN-based APIs supplied with DKAN, two major open data standards are supported. Both are supplied by and configurable through the `Open Data Schema Map </components/open-data-schema>`_ module.

Project Open Data
-----------------

DKAN provides full support and mapping for U.S. `Project Open Data <https://project-open-data.cio.gov/v1.1/schema/>`_, in both its federal and non-federal variants, with a data.json endpoint. The optional `Open Data Federal Extras` module is needed for full federal POD compliance. See a demo `here <http://demo.getdkan.com/data.json>`_.

DCAT-AP
-------

DKAN also provides endpoints and configurable field mappings for `DCAT-AP <https://joinup.ec.europa.eu/asset/dcat_application_profile/description>`_, the application profile for data portals in Europe, developed by the European Commission. DCAT-AP is of course based on the `Data Catalog Vocabulary (DCAT) <https://www.w3.org/TR/vocab-dcat/>`_, but provides stricter definitions of catalogs, datasets, distributions and other objects. Through `Open Data Schema Map`_, DKAN provides both a catalog endpoint (see `demo <http://demo.getdkan.com/catalog.xml>`_) and individual RDF endoints for each dataset (see by going to any dataset on `<http://demo.getdkan.com/>`_ and clicking the "RDF" link on the lefthand sidebar).

.. _field_comparison:

Field Comparison
----------------

Catalog
*******


Dataset
*******

.. csv-table::
	:header: "DKAN Field/Property label", "DKAN Field/Property machine name", "DCAT-AP property", "POD property"

	"Title", "title", `dct:title <https://www.w3.org/TR/vocab-dcat/#Property:dataset_title>`_, `title <https://project-open-data.github.io/v1.1/schema/#title>`_
	"Description", "body", `dct:description <https://www.w3.org/TR/vocab-dcat/#Property:dataset_description>`_, `description <https://project-open-data.github.io/v1.1/schema/#description>`_
	"Tags", "field_tags", `dcat:keyword <https://www.w3.org/TR/vocab-dcat/#Property:dataset_keyword>`_, `keyword <https://project-open-data.github.io/v1.1/schema/#keyword>`_
	"License", "field_license","", `license <https://project-open-data.github.io/v1.1/schema/#license>`_
	"Author", "field_author", "", ""
	"Spatial / Geographical Coverage Area", "field_spatial_geographical_area", "", ""
	"Spatial / Geographical Coverage Location", "field_spatial_geographical_cover", `dct:spatial <https://www.w3.org/TR/vocab-dcat/#Property:dataset_spatial>`_, `spatial <https://project-open-data.github.io/v1.1/schema/#spatial>`_
	"Frequency", "field_frequency", `dct:accrualPeriodicity <https://www.w3.org/TR/vocab-dcat/#Property:dataset_frequency>`_, `accrualPeriodicity <https://project-open-data.github.io/v1.1/schema/#accrualPeriodicity>`_
	"Publisher", "og_group_ref", `dct:publisher <https://www.w3.org/TR/vocab-dcat/#Property:dataset_publisher>`_, `publisher <https://project-open-data.github.io/v1.1/schema/#publisher>`_
	"Temporal Coverage", "field_temporal_coverage", `dct:temporal <https://www.w3.org/TR/vocab-dcat/#Property:dataset_temporal>`_, `temporal <https://project-open-data.github.io/v1.1/schema/#temporal>`_
	"Granularity", "field_granularity", "", ""
	"Data Dictionary", "field_data_dictionary", "", `dataDictionary <https://project-open-data.github.io/v1.1/schema/#dataDictionary>`_
	"Contact Name", "field_contact_name", "dcat:contactPoint.vcard:fn", `contactPoint <https://project-open-data.github.io/v1.1/schema/#contactPoint>`_
	"Contact Email", "field_contact_email", "dcat:contactPoint.vcard:hasEmail", `mbox <https://project-open-data.github.io/v1.1/schema/#mbox>`_
	"Public Access Level", "field_public_access_level", `dct:accessRights <http://udfr.org/docs/onto/dct_accessRights.html>`_, `accessLevel <https://project-open-data.github.io/v1.1/schema/#accessLevel>`_
	"Additional Info", "field_additional_info", "", ""
	"Resources", "field_resources", `dcat:distribution <https://www.w3.org/TR/vocab-dcat/#Property:dataset_distribution>`_, `distribution <https://project-open-data.github.io/v1.1/schema/#distribution>`_
	"Related Content", "field_related_content", "", `references <https://project-open-data.github.io/v1.1/schema/#references>`_
	"Data Standard", "field_conforms_to", "", `conformsTo <https://project-open-data.github.io/v1.1/schema/#dataset-conformsTo>`_
	"", "uuid", `dct:identifier <https://www.w3.org/TR/vocab-dcat/#Property:dataset_identifier>`_, `identifier <https://project-open-data.github.io/v1.1/schema/#identifier>`_
	"", "modified_date", `dct:modified <https://www.w3.org/TR/vocab-dcat/#Property:dataset_modified_date>`_, `modified <https://project-open-data.github.io/v1.1/schema/#modified>`_
	"", "release_date", `dct:issued <https://www.w3.org/TR/vocab-dcat/#Property:dataset_release_date>`_, `issued <https://project-open-data.github.io/v1.1/schema/#issued>`_

The following properties are provided by the Open Data Federal Extras module and have no equivilant in DCAT. They are only relevant to U.S. federal agencies.

.. csv-table::
	:header: "DKAN Field/Property label", "DKAN Field/Property machine name", "POD property"

	"Bureau Code", "field_odfe_bureau_code", `bureauCode <https://project-open-data.cio.gov/v1.1/schema/#bureauCode>`_
	"Data Quality", "field_odfe_data_quality", `dataQuality <https://project-open-data.cio.gov/v1.1/schema/#dataQuality>`_
	"Primary IT Investment UII", "field_odfe_investment_uii", `primaryITInvestmentUII <https://project-open-data.cio.gov/v1.1/schema/#primaryITInvestmentUII>`_
	"Program Code", "field_odfe_program_code", `programCode <https://project-open-data.cio.gov/v1.1/schema/#programCode>`_
	"System of Records", "field_odfe_system_of_records", `systemOfRecords <https://project-open-data.cio.gov/v1.1/schema/#systemOfRecords>`_


Resource / Distribution
***********************

.. csv-table::
	:header: "DKAN Field/Property label", "DKAN Field/Property machine name", "DCAT-AP property", "POD property"

	"", "", "dcat:accessURL", "accessURL"
	"", "", "dct:conformsTo", "conformsTo"
	"", "", "", "describedBy"
	"", "", "", "describedByType"
	"Description", "body", "dct:description", "description"
	"Link File || Upload", "field_link_remote_file || field_upload", "dcat:downloadURL", "downloadURL"
	"Format", "field_format", "", "format"
	"", "field_upload:mime", "dcat:mediaType", "mediaType"
	"Title", "title", "title", "dct:title"
