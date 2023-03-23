Glossary
========

.. glossary::

  Dashboard
    A tool that allows users to view, track, and analyze multiple data points at once. A dashboard is centered around data visualizations, including charts, graphs, and maps.

  Data Quality
    The quality of a particular dataset may be defined by measures of it's accuracy, completeness, reproducibility, lineage, and recency.

    For instance, a 'poor' dataset may be better defined by noting that it is inaccurate, missing values, cannot be reproduced/collected again by the same methods, cannot explain where it came from/who produced it, and is many months or years old and therefore not necessarily representative of the current reality.

    The poorer the dataset, the less likely it is to be a useful dataset for analysis, adjudication, policymaking, etc.

  Data Resource
    This refers to the actual data, usually provided as a file (csv, xls, xlsx, dat, zip, tar, pdf, etc.) but may also be a URL that provides access to the data. CSV is one of the most widely used data file formats and can be opened and edited by almost any simple text editor as well as Microsoft Excel, Apple Numbers, or Google Spreadsheets.

  Dataset
    A dataset is an identifiable collection of structured data objects unified by some criteria (authorship, subject, scope, spatial or temporal extent…) this unifiying criteria is called metadata. In DKAN, the term **dataset** refers to the metadata plus the data resource(s). A dataset can have multiple data resources and these are listed under the metadata property called **distribution**.

  Datastore
    A datastore is the data resource stored in a database. DKAN will import data from a UTF-8 encoded csv file into a database table and provide an API endpoint from which other applications can run queries on that data.

  Distribution
    Within a dataset, distribution is used to aggregate the metadata specific to a dataset's data resource. Each distribution should contain one accessURL or downloadURL. A downloadURL should always be accompanied by mediaType (file format).

  Harvest
    This is the migration of datasets from one data portal into another. It can also be used to manage a group of datasets from a single JSON file.

  Harvest Plan
    The harvest plan is the configuration used to import data into your catalog. The structure of a DKAN harvest plan can be found `here <https://github.com/GetDKAN/harvest/blob/master/schema/schema.json>`_.

  Metadata
    Metadata is structured information that describes, explains, locates, or otherwise
    makes it easier to retrieve, use, or manage an information resource (NISO 2004, ISBN: 1-880124-62-9).

    The challenge is to define and name standard metadata fields so that a data consumer has sufficient information to find, process and understand the described data. The more information that can be conveyed in a standardized regular format, the more valuable data becomes. Metadata can range from basic to advanced, from allowing one to discover the mere fact that a certain data asset exists and is about a general subject all the way to providing detailed information documenting the structure, processing history, quality, relationships, and other properties of a dataset.

    Making metadata machine readable greatly increases its utility, but requires more detailed standardization, defining not only field names, but also how information is encoded in the metadata fields.

    There are a number of specifications for dataset metadata. By default, DKAN ships with `JSON schema files <https://github.com/GetDKAN/dkan/tree/2.x/schema/collections>`_ to define metadata fields. These are based on `DCAT-US Metadata <https://resources.data.gov/resources/dcat-us/>`_ schema.

  Moderation State

    Datasets will have a publishing **status** (published or unpublished).

    Datasets will also have a moderation **state**, DKAN provides the following moderation states to facilitate an editorial workflow for the content of your catalog.

     - **Draft**: Status is unpublished.
       This moderation state means the content is only visible to authenticated users with permission to view/edit unpublished content.
     - **Published**: Status is published.
       This moderation state means the content is publicly availble, no authentication required to view.
     - **Published (hidden)**: Status is published.
       This moderation state means the content is publicly availble, but will not be indexed, meaning it will not be discoverable through the Dataset Search page. This 'hidden' state keeps the API endpoints public but the user must know the ID, and the dataset page is only visible if you know the URL.
     - **Archived**: Status is unpublished.
       This state is useful if you have published a dataset, but would like to remove it from public access but not delete it entirely.
     - **Orphaned**: Status is unpublished.
       This state is only available to the harvest workflow. If you are harvesting datasets from another catalog and a dataset is removed from the source catalog, the local copy will be deleted and it's referenced distribution, keyword, and category nodes will be set to the 'orphaned' state if not used by other datasets.


  Schema
    Schema refers to the structure of data as a blueprint for how a database or data object is constructed. The schema describes each column (or field) that will be encountered within the record, defining the column name, data type, and description.
