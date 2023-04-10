How to create a harvest
=======================

Use drush commands to :term:`Harvest` data into your catalog.

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
