Metadata Source Module
======================

Summary
--------
This documentation is for a module that is not part of the DKAN distribution but can be added to an existing DKAN site. This module, along with further documentation, is here: `DKAN Metadata Source <https://github.com/NuCivic/dkan_dataset_metadata_source#dkan-metadata-source>`_

DKAN Metadata Source
---------------------
Metadata is the “Who, What, When, Where, Why” of each dataset and its associated resources. When data contributors make sure to provide appropriate and thorough information for each dataset, users will have an easier time understanding the source and purpose of each dataset, and they can more easily plug it into their application of choice.

How is metadata standardized?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The International Organization for Standardization (ISO) is an global standard-setting body composed of representatives from various national standards organizations, and has determined a wide array of specific protocols for various types of data. Other standards setting organizations include the U.S. Federal Geographic Data Committee (FGDC) and the European INSPIRE Metadata Directive.

What is geospatial metadata?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Geospatial metadata pertaining to geographic digital resources such as Geographic Information System (GIS) files, geospatial databases, and earth imagery must involve core library catalog elements including, but not limited to, Title, Abstract, and Publication Data; geographic elements such as Geographic Extent and Projection Information; and database elements such as Attribute Label Definitions and Attribute Domain Values. (Source: U.S. FGDC)

Federal agencies in the U.S. are encouraged to follow ISO standards when working with or uploading geospatial data. For more information, see `the U.S. FGDC’s geospatial metadata documentation <http://www.fgdc.gov/metadata/geospatial-metadata-standards>`_.

The majority of geospatial data tools such as ESRI ArcGIS and GeoNetwork allow users to export metadata content to a stand-alone XML file that is formatted correctly for each standard or profile, and can be validated using the appropriate XML schema. The exported XML file can also then be published to a metadata catalog such as geodata.gov.

How can I ensure that my datasets and resources are associated with their proper metadata under these standards?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

When working with metadata created outside of DKAN, it would be tedious to import it with a 1 to 1 field ratio in DKAN Dataset given that you’d have to take the time to manually add each additional field for your standard of choice.

For example, the ISO 19115 metadata specification has dozens of fields that go above and beyond the Project Open Data fields included in DKAN.

Fortunately, the DKAN metadata source module was built to solve this dilemma.

Installation
^^^^^^^^^^^^^^

Install like any other Drupal module. Once enabled you will have the new content type and taxonomy term.

Metadata Sources and Types
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The DKAN metadata source module creates a Metadata Source content type and a Metadata Type taxonomy term.

Creating a Metadata Type
^^^^^^^^^^^^^^^^^^^^^^^^^

The Metadata Type is a taxonomy term used for linking multiple metadata sources with the same specification. To add or remove Metadata Types visit `/admin/structure/taxonomy/extended_metadata_schema`.

Click Add term to create a new type or Edit on an existing type to update or modify it.

Creating a Metadata Source
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The Metadata Source content type allows DKAN datasets and resources to be associated with metadata that has either been directly uploaded or linked from an outside source.

Creating a Metadata Source
^^^^^^^^^^^^^^^^^^^^^^^^^^^

To create a new Metadata Type visit ``node/add/metadata``:

.. figure:: https://cloud.githubusercontent.com/assets/512243/9552367/0927b9d2-4d7d-11e5-85e6-137751a336b1.png

Link on Dataset
^^^^^^^^^^^^^^^^

Once a Metadata Type has been created the Metadata Source will be displayed on the linked Dataset:

.. figure:: https://cloud.githubusercontent.com/assets/512243/9552388/3d15af2e-4d7d-11e5-9d51-2304bf03c59f.png

Clicking ‘View’ will allow you to check out a preview of the metadata content; clicking the ‘Download’ button will allow you to download it directly.
