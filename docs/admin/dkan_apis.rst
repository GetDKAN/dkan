.. _`user-docs DKAN APIs`
=========
DKAN APIs
=========

In the same way that data on a Granicus Open Data site is made accessible to general users through sensory devices like visualizations, dashboards, and data stories, Granicus Open Data APIs are essential to developers. Granicus Open Data comes with three API’s, the Dataset API, the Datastore API, and the Dataset REST API. These can be queried to discover metadata associated with the catalog or a specific dataset. It can also be harvested by other portals by complying with the API standard data.json.

Datastore API
-------------

The Granicus Open Data Datastore houses any CSV files that have been uploaded and then imported into the Datastore. One reason for importing CSV files into the Datastore is to make the file part of the public Datastore API. Allowing data from uploaded files to be included in the Datastore API greatly increases ability to discover the information as well as the usability and accessibility of the data.

The Datastore API enables interactions with specific Resources in the Granicus Open Data Datastore down to specific rows. Without the Datastore API data could not be searched with such precision.  

With the Datastore API, technical users can write programs to interact with the information held within the API. This means that the data in the Granicus Open Data Datastore can be accurately and efficiently queried (searched) and the data then used in other contexts and applications. The Datastore API is another way to show how open data can be used to provide a tangible return for citizens.

.. figure:: ../images/site_manager_playbook/DKAN_APIs/datastore_tab_view.png
   :alt: datastore api view
   
   Datastore API view on a Resource page.

Using the API
~~~~~~~~~~~~~

For any Resource imported to the Datastore, click Manage the Datastore. Then click the Data API button to get information about the Resource.

You won't perform API queries from here, but you can get linked to a sample query and find :ref:`documentation for more instruction<datastore API>` on how to use the Datastore API. The image below is the information returned on a sample query of the Granicus Open Data Datastore using the Datastore API.  

The query below shows the results in a "raw" form. This is generally more difficult to read, but it is what appears with a standard query. 
