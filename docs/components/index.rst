Major Components
----------------------

This section contains the documentation for each of the major modules and other
components that make up DKAN.

With the exception of the modules described in the
last two items in this table of contents
(`Open Data Schema Map <https://github.com/GetDKAN/open_data_schema_map/>`_ and
`Visualization Entity <https://github.com/GetDKAN/visualization_entity>`_),
and of the `Recline <https://github.com/GetDKAN/recline>`_ module which is
described inside the Datasets section, all this functionality is provided by the
`modules that ship with the DKAN  profile <https://github.com/GetDKAN/dkan/tree/7.x-1.x/modules/dkan>`_.

.. toctree::
   :maxdepth: 1

   Datasets <dataset/index>
   Datastore <datastore>
   Harvester <harvest>
   Workflow <workflow>
   Topics <topics>
   fixtures
   federal-extras
   search
   theme
   Roles and Permissions <permissions>
   Storytelling <storytelling>
   Open Data Schema Map <open-data-schema>
   Visualizations <visualizations/index>

.. note:: The three modules mentioned above that are not distributed with DKAN
   continue to be maintained in separate repositories because they work
   independently of DKAN and could be installed in non-DKAN Drupal sites.
