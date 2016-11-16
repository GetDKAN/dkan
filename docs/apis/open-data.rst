Open Data APIs
==============

In addition to the Drupal and CKAN-based APIs supplied with DKAN, two major open data standards are supported. Both are supplied by and configurable through the `Open Data Schema Map </components/open-data-schema>`_ module.

Project Open Data
-----------------

DKAN provides full support and mapping for U.S. `Project Open Data <https://project-open-data.cio.gov/v1.1/schema/>`_, in both its federal and non-federal variants, with a data.json endpoint. The optional `Open Data Federal Extras` module is needed for full federal POD compliance. See a demo `here <http://demo.getdkan.com/data.json>`_.

DCAT-AP
-------

DKAN also provides endpoints and configurable field mappings for `DCAT-AP <https://joinup.ec.europa.eu/asset/dcat_application_profile/description>`_, the application profile for data portals in Europe, developed by the European Commission. DCAT-AP is of course based on the `Data Catalog Vocabulary (DCAT) <https://www.w3.org/TR/vocab-dcat/>`_, but provides stricter definitions of catalogs, datasets, distributions and other objects. Through `Open Data Schema Map`_, DKAN provides both a catalog endpoint (see `demo <http://demo.getdkan.com/catalog.xml>`_) and individual RDF endoints for each dataset (see by going to any dataset on `<http://demo.getdkan.com/>`_ and clicking the "RDF" link on the lefthand sidebar).

Field Comparison
----------------

Catalog
*******


Dataset
*******

.. csv-table::
	:header: "DKAN Field/Property", "DCAT-AP property", "POD property"

	"title", `dct:title <http://www.w3.org/TR/vocab-dcat/#Property:dataset_title)|[title](http://project-open-data.github.io/schema/#title>`_, "title"
	"body", `dct:description <http://www.w3.org/TR/vocab-dcat/#Property:dataset_description>`_, `description <http://project-open-data.github.io/schema/#description>`_
	"field_tags", `dcat:keyword <http://www.w3.org/TR/vocab-dcat/#Property:dataset_keyword>`_, `keyword <http://project-open-data.github.io/schema/#keyword>`_
	"field_license","", `license <http://project-open-data.github.io/schema/#license>`_
	"field_author", "", ""
	"field_spatial_geographical_area", "", ""
	 "field_spatial_geographical_cover", `dct:spatial <http://www.w3.org/TR/vocab-dcat/#Property:dataset_spatial>`_, `spatial <http://project-open-data.github.io/schema/#spatial>`_
	"field_frequency", `dct:accrualPeriodicity <http://www.w3.org/TR/vocab-dcat/#Property:dataset_frequency>`_, `accrualPeriodicity <http://project-open-data.github.io/schema/#accrualPeriodicity>`_
	"og_group_ref", `dct:publisher <http://www.w3.org/TR/vocab-dcat/#Property:dataset_publisher>`_, `publisher <http://project-open-data.github.io/schema/#publisher>`_
	"field_temporal_coverage", `dct:temporal <http://www.w3.org/TR/vocab-dcat/#Property:dataset_temporal>`_, `temporal <http://project-open-data.github.io/schema/#temporal>`_
	"field_granularity", "", ""
	"field_data_dictionary", "", `dataDictionary <http://project-open-data.github.io/schema/#dataDictionary>`_
	"field_contact_name", "dcat:contactPoint.vcard:fn", `contactPoint <http://project-open-data.github.io/schema/#contactPoint>`_
	"field_contact_email", "dcat:contactPoint.vcard:hasEmail", `mbox <http://project-open-data.github.io/schema/#mbox>`_
	"field_public_access_level", `dct:accessRights <http://udfr.org/docs/onto/dct_accessRights.html>`_, `accessLevel <http://project-open-data.github.io/schema/#accessLevel>`_
	"field_additional_info", "", "" 
	"field_resources", `dcat:distribution <http://www.w3.org/TR/vocab-dcat/#Property:dataset_distribution>`_, `distribution <http://project-open-data.github.io/schema/#distribution>`_
	"field_related_content", "", `references <http://project-open-data.github.io/schema/#references>`_
	"uuid", `dct:identifier <http://www.w3.org/TR/vocab-dcat/#Property:dataset_identifier>`_, `identifier <http://project-open-data.github.io/schema/#identifier>`_
	"modified_date", `dct:modified <http://www.w3.org/TR/vocab-dcat/#Property:dataset_modified_date>`_, `modified <http://project-open-data.github.io/schema/#modified>`_
	"release_date", `dct:issued <http://www.w3.org/TR/vocab-dcat/#Property:dataset_release_date>`_, `issued <http://project-open-data.github.io/schema/#issued>`_

The following properties are provided by the Open Data Federal Extras module and have no equivilant in DCAT. They are only relevant to U.S. federal agencies.

.. csv-table::
	:header: "DKAN Field/Property", "POD property"

	"field_odfe_bureau_code", `bureauCode <https://project-open-data.cio.gov/v1.1/schema/#bureauCode>`_
	"field_odfe_data_quality", `dataQuality <https://project-open-data.cio.gov/v1.1/schema/#dataQuality>`_
	"field_odfe_investment_uii", `primaryITInvestmentUII <https://project-open-data.cio.gov/v1.1/schema/#primaryITInvestmentUII>`_
	"field_odfe_program_code", `programCode <https://project-open-data.cio.gov/v1.1/schema/#programCode>`_
	"field_odfe_system_of_records", `systemOfRecords <https://project-open-data.cio.gov/v1.1/schema/#systemOfRecords>`_


Resource / Distribution
***********************

.. csv-table::
	:header: "DKAN Field/Property", "DCAT-AP property", "POD property"

	"", "dcat:accessURL", "accessURL"
	"", "dct:conformsTo", "conformsTo"
	"", "", "describedBy",
	"", "", "describedByType" 
	"body", "dct:description", "description"
	"field_link_remote_file || field_upload", "dcat:downloadURL", "downloadURL"
	"field_format", "", "format"
	"field_upload:mime", "dcat:mediaType", "mediaType"
	"title", "title", "dct:title"
