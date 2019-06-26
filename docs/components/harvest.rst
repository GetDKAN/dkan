Harvest
=======

To “harvest” data is to use the public feed or API of another data portal to import items from that portal’s catalog into your own. 
For example, `Data.gov <https://data.gov/>`_ harvests all of its datasets from the `data.json <https://project-open-data.cio.gov/v1.1/schema/>`_ files of `hundreds of U.S. federal, state and local data portals <https://catalog.data.gov/harvest>`_.

DKAN Harvest uses the local file system to store source information in cache and plan files.

Drush Commands
--------------

.. table:: 
    :widths: 25, 15, 60

    =======================   ===========  =======
    Command                   Args         Notes
    =======================   ===========  ======= 
    dkan-harvest:list         n/a          Lists avaialble harvests.
    dkan-harvest:register     $config      Register a new harvest, file saved to *dkan_harvest/plans* directory.
    dkan-harvest:deregister   $identifier  Deletes the cached plan file.
    dkan-harvest:cache        $identifier  This will fetch the source data, apply the source configuration and cache the data to a local file.
    dkan-harvest:run          $identifier  This will harvest the current cache for the selected sources.
    dkan-harvest:revert       $identifier  Reverts harvest.
    =======================   ===========  =======


Setup
-----

Currently there is no UI for the harvests, use the drush commands above to harvest data:

To register a new harvest source, pass in the config argument as JSON:

.. code-block::

    dkan-harvest:register '{"identifier":"dkandemo","source":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"http:\/\/demo.getdkan.com\/data.json"},"transforms":[{"Filter":{"keyword":"environment"}},{"Override":{"publisher.name":"DKAN Demo"}},"\\Drupal\\dkan_harvest\\Transform\\DataJsonToDkan"],"load":{"type":"\\Drupal\\dkan_harvest\\Load\\Dataset"}}'
    ...

That source config looks like:

.. code-block:: json

    {
      "identifier": "dkandemo",
      "source": {
        "type": "\\Harvest\\Extract\\DataJson",
        "uri": "http:\/\/demo.getdkan.com\/data.json"
      },
      "transforms": [
        "\\Drupal\\dkan_harvest\\Transform\\DataJsonToDkan"
      ],
      "load": {
        "type": "\\Drupal\\dkan_harvest\\Load\\Dataset"
      }
    }


.. note:: About the load configs

    * **identifier**: The plan's identifier. Required.
    * **source**: Required.

      - **type**: Class utilized to extract the data from the source. Required.
      - **uri**: The URL or Location of the Source. Required.

    * **transforms**:

      - **type**: Class utilized to transform the data OR null.

    * **load**: Required.

      - **type**: Class utilized to load the harvested data. Required.


Project Open Data (as well as most metadata APIs) includes many fields that are not simple key-value pairs. If you need to access or modify nested array values you can use this dot syntax to specify the path: `key.nested_key.0.other_nested_key`. For example, the Publisher field in Project Open Data is expressed like this:

.. code-block:: JavaScript

    "publisher": {
      "@type": "org:Organization",
      "name": "demo.getdkan.com"
    },


Transforms
----------

The transform classes will help you fine tune the results of your harvest.

:Filters: Filters restrict the datasets imported by a particular field. For instance, if you are harvesting a data.json source and want only to harvest health-related datasets, you might add a filter with "keyword" in the first text box, and "heatlh" in the second.
:Excludes: Excludes are the inverse of filters. For example, if you know there is one publisher listed on the source whose datasets you do **not** want to bring into your data portal, you might add "publisher.name" with value "Governor's Office of Untidy Data"
:Overrides: Overrides will replace values from the source when you harvest. For instance, if you want to take responsibility for the datasets once harvested and add your agency's name as the publisher, you might add "publisher.name" with your agency's name as the value.
:Defaults: Defaults work the same as overrides, but will only be used if the relevant field is empty in the source
