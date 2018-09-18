Adding additional file types to the *resource* allowed extensions list
======================================================================

Many different file types can be uploaded on a *resource* (though **only** CSV files can be imported to the :doc:`Datastore <../components/datastore>`.

To view the allowed file extensions that come with DKAN visit ``admin/dkan/dataset_forms``.

**To add additional file types**

1. Navigate to DKAN > DKAN Dataset Forms
2. Enter the extensions into the "Additional allowed file extensions" field
3. Click "Save configuration".

You can also add additional extensions in a custom module or with drush by using variable_set().

.. code-block:: php

  variable_set('dkan_custom_extensions', 'ods dct');
