Choropleth
===========
.. warning::

	Under Development. Do not use on production.

Enable the choropleth bundle:

.. code-block:: php

	$ drush en -y visualization_entity_choropleth_bundle
	$ drush cc all


Examples Files
--------------
Two example files are provided in the **examples** folder:

.. code-block:: php

	africa.geojson
	africa-data.csv


Create Visualization
--------------------
+ Look for **Content -> Add Content -> Resource** in the admin menu and click on it.

+ Upload a **africa-data.csv** file from the examples folder for the resource.

.. image:: images/choropleth-step-00.png

+ Fill the required fields, enter 'geojson' in the format field, and **save** the resource

.. image:: images/choropleth-step-01.png

+ Look for **Structure -> Entity Types -> Geo File -> geojson -> Add geojson** in the admin menu and click on it.

.. image:: images/choropleth-step-02.png

+ Set **Title**
+ Upload a **geojson** file
+ Fill **name attribute** with the **column name** in the data (csv resource) that will match the **name** property for the features in the **geojson** file.

.. image:: images/choropleth-step-03.png

+ Click **Save**.
+ You'll get a preview for the geojson file you just uploaded.

.. image:: images/choropleth-step-04.png

+ Look for **Structure -> Entity Types -> Visualization -> Choropleth Visualization -> Add Choropleth Visualization** in the admin menu and click on it.

.. image:: images/choropleth-step-05.png

+ Fill Title
+ Select the **geojson** file we created for the **geojson** field.
+ Select the **resource** file we created for the **resource** field.

.. image:: images/choropleth-step-06.png

+ Select the **colors** you like to use for the choropleth map.
+ Fill **data column** with the column in the csv data you'll like to pick as the source of numerical data for the polygon coloring. If you leave this field blank, you'll get a list of radio buttons to pick up the column when the visualization gets rendered.
+ Fill the **data breakpoints** with comma separated numbers. If you leave this field blank, breakpoints will be calculated for you based on the data.

.. image:: images/choropleth-step-07.png

+ Click **Save** & Enjoy!
.. image:: images/choropleth-step-08.png
