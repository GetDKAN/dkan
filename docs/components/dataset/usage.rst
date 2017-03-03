Usage
=====

Creating Datasets and Resources
------------------------------------


DKAN’s data publishing model is based on the concept of :ref:`datasets <datasets>` and :ref:`resources <resources>`.  A dataset is a container for one or more resources; a resource is the actual “data” being published, such as a CSV table, a GeoJSON data file, or a TIFF aerial image.

The dataset and resource content types in DKAN are provided by the `DKAN Dataset module <https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_dataset>`_.

In our example, we’ll be adding a dataset with Wisconsin polling places to a DKAN site. The data may look familiar; it's one of the sample datasets provided with DKAN upon installation.

Step 1: Create the Dataset
**************************

By default, only authenticated (“logged-in”) users can add new Datasets and Resources to a DKAN website.The default DKAN user permissions allows Site managers, Editors, and Content Creators access to the administration menu. From here a user may navigate to the Content » Add Content » Dataset link to access the “Create Dataset” form.

The Dataset is the container for the actual data resource files and contains basic information about the data, such as title, description, category tags, and license.  Once we’ve entered information about the data, we can click the “Next: Add data” button to begin adding data.

Step 2: Add one or more Resources to the Dataset
**************************

After creating a dataset, we’re prompted to add one or more data resources to it.  There are three types of Resources that can be added to a Dataset, depending on the type and location of the Resource:

:Link to a file: this option allows publishers to create a link to a data file published on another Internet website.  Although the file itself will remain on the other site, the data within the file can be imported into your DKAN site’s Datastore for preview and analysis by your users.  See The DKAN Datastore for more information.
:Link to an API: some data resources aren’t standalone files but queryable online databases; the interface to these databases is known as an API.  Adding links to these types of online database interfaces to your DKAN data catalog can be very useful for developers interested in working with your data.
:Upload a file: this option allows publishers to upload data files to the DKAN site.  As in the “link to a file” option, the data within the file will be imported into your DKAN site’s Datastore for preview and analysis by your users.  See The DKAN Datastore for more information.

To continue with our Wisconsin Polling Places example, we’ll add one resource file to the Dataset we created in Step 1.  Our resource file is a CSV, that is, comma-separated values format; this is a popular file format for exchanging tabular data.  Let’s explore the example resource shown here and the various fields within:

:Resource / Choose File: upload a file from your local hard drive.
:Resource / Recline Views: DKAN’s “Data Preview” feature allows visitors to preview published data in three views:

  * **Map** - data with latitude and longitude columns or GeoJSON files can be previewed in a map interface
  * **Graph** - tabular (spreadsheet) data can be graphed by users, letting them create their own meaningful visualizations
  * **Grid** - by default, tabular data is presented in a spreadsheet view, with filter, sort, and search capabilities

:Title: this is the title of the individual data file, not the parent dataset container.
:Description: a rich-text editor field is provided so publishers can offer detailed and useful descriptions
:Format: entering the file format here will allow users the ability to search for data by specific format
:Dataset: this is the parent dataset container; this field should already be populated if you’re adding a Resource subsequent to adding a Dataset

At the bottom of the *Add Resource* page, we can choose:

:Save: Save progress on this resource and immediately return to it for further editing
:Save and add another: Save this resource and add another resource to the same dataset
:Next\: Additional Info: Save this resource and move to the third stage in adding a complete dataset, entering optional metadata about the dataset

In our example, we’re only adding a single resource, so we’ll click “Next: Additional Info” to move onto Step 3. If we had more than one resource to add to this dataset, we would choose the “Save and add another” option. Simply clicking "Save" would end the Dataset creation process and save the dataset, for now, with no additional metadata.

Step 3: Adding Metadata to a Dataset
**************************

We now come to a third form which allows us to add additional metadata to the dataset. All these fields are optional, but provide valuable information about your dataset to both human visitors to the website and machines discovering your dataset through one of :doc:`DKAN's public APIs <../../apis/index>`.

Let's take a closer look at some of the metadata fields available on this form:

:Author: The Dataset's author, in plain text.
:Spatial / Geographical Coverage Area: Lets us define what region the data applies to. In this case, the US State of Wisconsin. You can use the map widget to draw an outline around the state borders, or, click the "Add data manually" button if you already have a `GeoJSON <http://geojson.org/>`_ string you can paste in.
:Spatial / Geographical Coverage Location: The region the data applies to, written in plain text. This can be used instead of or in addition to the **Coverage Area** field.
:Frequency: How often is this dataset updated? We might expect our list of polling places to be updated every year, so we could select "annually." However, often we don't expect the data to be updated (even in this case, perhaps we plan to post the next version of the data as a _separate_ dataset), in which case we can leave this blank.
:Temporal Coverage: Like Geographic Coverage, this field lets us give some context to the data, but now for the relevant time period. Here we could enter the year or years for which our polling places data is accurate.
:Granularity: This is a somewhat open-ended metadata field that lets you describe the granularity or accuracy of your data. For instance: "Year". Note, this field is depreciated in DCAT and Project Open Data, and may be removed from DKAN.
:Data Dictionary: Another open-ended field, this is a space for almost any kind of explanation for understanding the terminology/units/column names/etc. in our dataset. Ideally, this will be a simple URL to a Data Dictionary resource elsewhere on the web.
:Additional Info: Lets us arbitrarily define other metadata fields. See :ref:`Additional Info field <custom_metadata>` for more information.
:Resources: This field is a reference to the resources you have already added.

After you click "Save", the metadata we enter will appear on the page for this Dataset:

Configuration
--------------

Adding or Removing Allowed Resource File Types
**********************************************

Any type of file can be uploaded to Resources (though only CSV files can be imported to the :doc:`Datastore <../datastore/index>`.

File types are controlled at "/admin/structure/types/manage/resource/fields/field_upload"

To add or remove file types navigate as an 'administrator' and enter extensions into the "Allowed file extensions" field.
