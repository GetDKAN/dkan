How to create a harvest
=======================

Use drush commands to :term:`Harvest` data into your catalog.

Create a harvest JSON file
--------------------------

Normally you would use the data.json provided by another data catalog and "harvest" the datasets into your catalog.
But harvests can also be used for bulk management of datasets from a manually generated data.json file.

Example data.json with a single dataset:

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
            "description": "Example dataset description text.",
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

If you plan to maintain the datasets with the harvest process then do not edit the datasets via the UI or API.
Edit the metadata in the data.json file and re-run the harvest to update the datasets.

If you are using the harvest to simply bulk generate datasets, and want to allow data publishers to update the datasets as needed
via the UI or API, delete the data.json file to prevent overwriting any changes. After the 2.16.13 release, you could also
deregister the harvest.

Register a harvest
------------------

  Register a new :term:`Harvest Plan`.

  - Create a unique name as the **identifier** of your harvest that will help you distinguish it from other harvest plans. Do not use spaces or hyphens in the name, underscores are allowed.
  - Provide the full URI for the data source

  **Example**

  .. prompt:: bash $

    drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json

  You can view a list of all registered harvest plans with ``dkan:harvest:list``


Run the harvest
---------------
  Once you have registered a harvest source, run the import, passing in
  the identifier as an argument

  .. prompt:: bash $

    drush dkan:harvest:run myHarvestId

View the status of the harvest
------------------------------

  .. prompt:: bash $

    drush dkan:harvest:status myHarvestId

You can also navigate to *admin/dkan/harvest* to view the status of the extraction,
the date the harvest was run, and the number of datasets that were added
by the harvest. By clicking on the harvest ID, you will also see specific
information about each dataset, and the status of the datastore import.

Revert a harvest
----------------

  .. prompt:: bash $

    drush dkan:harvest:revert myHarvestId

This will delete all of the datasets listed in the specified harvest ID. Any referenced
terms will be set to the orphaned state. Any distributions from the harvest will be unpublished.
Alternatively you could run `dkan:harvest:archive` to unpublish the datasets without
deleting them from your catalog.

Deregister a harvest
--------------------

  .. prompt:: bash $

    drush dkan:harvest:deregister myHarvestId

This will remove the harvest plan.

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

  .. prompt:: bash $

    drush dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json  --transform="\\Drupal\\custom_module\\Transform\\CustomTransform"
