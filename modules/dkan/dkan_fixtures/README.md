## DKAN Fixtures

_Fixtures_ are a programming concept for default data that is included with application code for testing or other purposes. The data is provided in a structured format like XML or JSON, and imported into the database as part of a build process.

In DKAN, fixtures are used to provide datasets and other supporting content out of the box. The most visible use case for this will be DKAN's default content, which showcases DKAN's various capabilities. The fixtures themselves for default content are packaged in a separate sub-module, [DKAN Default Content](https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_fixtures/modules/dkan_default_content).

The DKAN Fixtures module provides tools to easily export all the content that lives inside a
DKAN site into JSON fixture files, following a defined schema. Currently the content supported by the module are Groups, Resources, Datasets, Data Dashboards, Data Stories and Pages. [Visualization Entites](https://github.com/NuCivic/visualization_entity) are also supported.

The module also provides basic Migrate classes that can be used to import content easily on a DKAN site.
