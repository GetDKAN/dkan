How to create a harvest
=======================

Use drush commands to :term:`Harvest` data into your catalog.

Create a harvest JSON file
__________________________

Normally you would use the data.json provided by another data catalog and "harvest" the datasets into your catalog.
But harvests can also be used for bulk management of datasets from a manually generated data.json file.

Example data.json file with various placeholders:

    .. code-block:: json

      {
        "@context": "https://project-open-data.cio.gov/v1.1/schema/catalog.jsonld",
        "@id": "https://site-domain.com/data.json",
        "@type": "dcat:Catalog",
        "conformsTo": "https://project-open-data.cio.gov/v1.1/schema",
        "describedBy": "https://project-open-data.cio.gov/v1.1/schema/catalog.json",
        "dataset": [
          {
            "@type": "dcat:Dataset",
            "identifier": "my-universally-unique-identifier",
            "modified": "2023-10-01",
            "accessLevel": "public",
            "title": "Example Dataset Title",
            "description": "<p>Example dataset description text.</p>",
            "keyword": [
               "Example keyword"
            ]
            "distribution": [
              {
                "@type": "dcat:Distribution",
                "downloadURL": "https://site-domain.com/sites/default/files/Bike_Lane.csv",
                "mediaType": "text/csv"
              }
            ]
          }
        ]
      }

The above example contains all the required properties for a dataset if using the default schema provided with DKAN. The
default schema requires a distribution and at least one keyword. The identifier must be a unique string within the given
catalog. You can find descriptions of the above properties and additional optional properties by viewing the example
dataset schema that ships with DKAN at `schema/collections/dataset.json <https://github.com/GetDKAN/dkan/blob/2.x/schema/collections/dataset.json>`_.

Register a harvest
------------------

  Register a new :term:`Harvest Plan`.

  - Create a unique name as the **identifier** of your harvest
  - Provide the full URI for the data source

  **Example**

  .. code-block::

    drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json

  You can view a list of all registered harvest plans with ``dkan:harvest:list``


Run the harvest
---------------
  Once you have registered a harvest source, run the import, passing in
  the identifier as an arguement

  .. code-block::

    drush dkan:harvest:run myHarvestId

View the status of the harvest
------------------------------
Navigate to *admin/dkan/harvest* to view the status of the extraction,
the date the harvest was run, and the number of datasets that were added
by the harvest. By clicking on the harvest ID, you will also see specific
information about each dataset, and the status of the datastore import.

Transforms
----------
If you would also like to make changes to the data you are harvesting,
you can create custom  **transforms** that will modify the data before
saving it to your catalog. Add multiple transforms as an array.

How to create transforms
^^^^^^^^^^^^^^^^^^^^^^^^

Transforms allow you to modify what you are harvesting.
`Click here <https://github.com/GetDKAN/socrata_harvest>`_ to see an
example of how you can create a custom module to add a transform class.

  **Example with a transform item**

  .. code-block::

    drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json  --transform="\\Drupal\\custom_module\\Transform\\CustomTransform"
