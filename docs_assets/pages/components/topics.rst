Topics
======

Topics is the term used to reference the metadata property that `Project Open Data <https://project-open-data.cio.gov>`_ calls "theme". 
The `theme <https://project-open-data.cio.gov/v1.1/schema/#theme>`_ is the main thematic category of the dataset.

DKAN will create a *data* node for each theme specified in the datasets.

When the value of themes change or become outdated, the corresponding data node will be removed by the **orphan_reference_processor** queue task.
If you prefer to run it manually, you may do so with:

.. code-block::
  
    drush queue-run orphan_reference_processor