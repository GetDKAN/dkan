# DKAN Harvest

DKAN Harvest is a module that provides a common harvesting framework and for DKAN.
To "harvest" data is to use the public feed or API of another data portal to
import items from that portal's catalog into your own. To cite a well-known
example, [Data.gov](https://data.gov) harvests all of its datasets from the
[data.json](https://project-open-data.cio.gov/v1.1/schema/) files of [hundreds
of U.S. federal, state and local data portals](http://catalog.data.gov/harvest).
It supports custom extensions and adds [drush](http://www.drush.org/en/master/)
commands and a web UI to manage harvesting sources and jobs.

DKAN Harvest is built on top of the widely-used
[Migrate](https://www.drupal.org/project/migrate) framework for Drupal. It
follows a two-step process to import datasets:

1. Process a source URI and save resulting data locally to disk as JSON
2. Perform migrations into DKAN with the locally cached JSON files, using mappings provided by the [DKAN Migrate Base](https://github.com/NuCivic/dkan_migrate_base) module.

## Harvest Sources

Harvest Sources are nodes that store the source's URI and some additional
configuration. To create a new source, make sure you have a role with permissions
to create Harvest Sources (administrators and site managers under default DKAN
Permissions), go to `node/add/harvest-source` and fill out the form.

The Harvest Source form includes four multi-value fields to control the results of your harvest.

* **Filters** restrict the datasets imported by a particular field. For instance, if you are harvesting a data.json source and want only to harvest health-related datasets, you might add a filter with "keyword" in the first text box, and "heatlh" in the second.
* **Excludes** are the inverse of filters. For example, if you know there is one
publisher listed on the source whose datasets you do _not_ want to bring into your data portal, you might add "publisher" with value "Governor's Office of Terrible Data"
* **Overrides** will replace values from the source when you harvest. For instance, if you want to take responsibility for the datasets once harvested and add your agency's name as the publisher, you might add "publisher" with your agency's name as the value.
* **Defaults**   work the same as overrides, but will only be used if the relevant field is empty in the source

![Add Harvest Source](https://cloud.githubusercontent.com/assets/381224/20218155/ec7b23b0-a801-11e6-9c07-f27159927ca5.png)

Project Open Data (as well as most metadata APIs) includes many fields that are not simple key-value pairs. If you need to access or modify nested array values you can use this dot syntax to specify the path: `key.nested_key.0.other_nested_key`. For example, the Publisher field in Project Open Data is expressed like this:

```json
      "publisher": {
        "@type": "org:Organization",
        "name": "demo.getdkan.com"
      },
```

To access the name property for filtering or overriding, you can set `publisher.name` in the first text box and the value you want to use in the second one.

If the Harvest Source type you are looking for is not available, please refer
to the **Define a new Harvest Source Type** section in the developers docs (coming soon).

Harvest Source nodes are viewable by the public, providing some basic metadata
for the source and listing all datasets harvested from that source.

![Harvest Source Page](https://cloud.githubusercontent.com/assets/381224/20218476/93a6196e-a803-11e6-895f-d82d5228b055.png)

Additional tabs are available to administrators and site managers.

### Preview

After you create or edit a source, an initial cache operation will be performed and you will be directed to the preview page. This page shows a list of dataset titles and identifiers now in the harvest cache, allowing you to perform a basic check on
your source configuration and make any adjustments before running the migration.

### Event Log

The events tab on the Harvest Source page provides historical data on all harvests
run on this source.

![Harvest Source Event Log Page](../images/harvest_source_event_log.png)

The information is managed by the core `dkan_harvest` via a per-harvest source
`migrate_log` table that tracks the number of datasets created, updated,
failed, orphaned, and unchanged and status. If the value for the field Status is Error then you can click on the text to see the log error and identify the problem.

### Error Log

Similar to the Events tab, this shows a log of all errors recorded during harvesting on the source.

### Manage Datasets Screen

An administrative view that lets you sort and filter by certain harvesting metadata. The most powerful function on this page is to filter by "orphan" status. When a dataset that was harvested into your system previously is no longer
provided in the source, it is considered "orphaned" on your site and unpublished.
From the Manage Datasets screen, you can either permanently delete or re-publish
orphan datasets.

Presenting the event log via some easy to parse charts is in the TODO list.

## The Harvest Dashboard

To run and manage harvest operations from the web interface, navigate to
 `admin/dkan/harvest/dashboard`. This is a view of all
available (published) Harvest Sources in the system. This page will display the
harvest source title, the source type, the last time a harvest
migration was run for the specific source, the number of datasets that were
imported, and the status of the harvest.

![Harvest Dashboard](../images/harvest_dashboard.png)

The dashboard allows you to select one or more sources and perform one of the following operations on it:

* **Harvest (cache and migrate)** is the operation you are most likely to want to perform on this page. It will cache the source data locally and migrate that source data into your site content.
* **Cache source(s)** will simply fetch the source data, apply the source configuration (filters, excludes, etc.) and cache the data locally without migrating. You may wish to do this to check for errors, or to refresh the preview available for each specific source (see the section on source pages below).
* **Migrate source(s)** will migrate the current cache for the selected sources, no matter how old it is.


## Harvested Resources
When datasets are harvested, the resources are added as remote files, which means they are links to the original files on the remote server. If you modify the resource in your DKAN site, your changes will be overwritten the next time a harvest is performed. If you add the resource to the [datastore](datastore.rst) be sure to set up periodic importing so that the resource stays in sync with the source. For these reasons, we do not recommend that you create visualizations based on harvested resources as the visualizations could break when changes are made to the files upstream.

## Harvest Drush Commands

DKAN Harvest provides multiple drush commands to manage harvest sources and
control harvest jobs. In fact, once your sources are properly configured, running
harvests from Drush on a cron job or other scheduling system like [Jenkins](https://jenkins.io/) is highly
reccomended.

It is recommanded to pass the `--user=1` drush option to
harvest operation (especially harvest migration jobs) to make sure that the
entities created have a proper user as author.

### List Harvest sources available

```sh
# List all available Harvest Sources
$ drush --user=1 dkan-harvest-status
# Alias
$ drush --user=1 dkan-hs
```

### Run a full harvest (Cache & Migration)

```sh
# Harvest data and run migration on all the harvest sources available.
$ drush --user=1 dkan-harvest
# Alias
$ drush --user=1 dkan-h

# Harvest specific  harvest source.
$ drush --user=1 dkan-harvest test_harvest_source
# Alias
$ drush --user=1 dkan-h test_harvest_source
```

### Run a harvest cache

```sh
# Run a harvest cache operation on all the harvest sources available.
$ drush --user=1 dkan-harvest-cache
# Alias
$ drush --user=1 dkan-hc

# Harvest cache specific harvest source.
$ drush --user=1 dkan-harvest-cache test_harvest_source
# Alias
$ drush --user=1 dkan-hc test_harvest_source
```

### Run a harvest migration job

```sh
# Run a harvest migrate operation on all the harvest sources available.
$ drush --user=1 dkan-harvest-migrate
# Alias
$ drush --user=1 dkan-hm

# Harvest migrate specific harvest source.
$ drush --user=1 dkan-harvest-migrate test_harvest_source
# Alias
$ drush --user=1 dkan-hm test_harvest_source
```

## Extending DKAN Harvest

DKAN developers can use the api provided by DKAN Harvest to add support for
additional harvest source types. The `dkan_harvest_datajson` module encapsulate
the reference implementation providing support for POD type sources.

If you need to harvest from an end point type other then POD. You can extend
the DKAN Harvest APIs to implement said support by following a simple
checklist:
* Define a new Harvest Source Type via `hook_harvest_source_types`.
* Implement the Harvest Source Type cache callback.
* Implement the Harvest Source Type Migration Class.
* (Optional) Write tests for your source type implementation.

### Define a new Harvest Source Type

DKAN Harvest leverages Drupal's hook system to provide a way to extend the
Source types that DKAN Harvest supports. To add a new harvest source type the
we return their definitions as array items via the
`hook_harvest_source_types()` hook.

```php
/**
 * Implements hook_harvest_source_types().
 */
function dkan_harvest_test_harvest_source_types() {
  return array(
    'harvest_test_type' => array(
      'machine_name' => 'harvest_test_type',
      'label' => 'Dkan Harvest Test Type',
      'cache callback' => 'dkan_harvest_cache_default',
      'migration class' => 'HarvestMigration',
    ),

    // Define another harvest source type.
    'harvest_another_test_type' => array(
      'machine_name' => 'harvest_another_test_type',
      'label' => 'Dkan Harvest Another Test Type',
      'cache callback' => 'dkan_harvest_cache_default',
      'migration class' => 'HarvestMigration',
    ),
  );
}
```

Each array item defines a single harvest source type. Each harvest source item consists of an array with 4 keyed values:

* `machine_name` _(Unique string identifying the harvest source type.)_
* `label` _(This label wil be used on the harvest add node form.)_
* `cache callback` _(Cache function to perform; takes HarvestSource object and timestamp as arguments) and returns a HarvestCache object)_
* `migration class` _(A registered Migrate class to use for this source type)_

### Cache callbacks

```php
/**
 * @param HarvestSource $source
 * @param $harvest_updatetime
 *
 * @return HarvestCache
 */
function dkan_harvest_datajson_cache(HarvestSource $source, $harvest_updatetime)
```

This callback takes care of downloading/filtering/altering the data from the
source end-point to the local file directory provided by the
HarvestSource::getCacheDir() method. The recommended folder structure for
cached data is to have one dataset per uniquely named file. The actual migration
is then performed on the cached data, not on the remote source itself.

```sh
$ tree
.
├── 5251bc60-02e2-4023-a3fb-03760551ab4a
├── 80756f84-894f-4796-bb52-33dd0a54164e
├── 846158bd-1821-48d8-80c8-bb23a98294a9
└── 84cada83-2382-4ba2-b9be-97634b422a07

0 directories, 4 files

$ cat 84cada83-2382-4ba2-b9be-97634b422a07
/* JSON content of the cached dataset data */
```

The harvest cache function needs to support the modifications to the source
available from the harvest source via the Filter, Excludes, Overrides and Default
fields. Each of these configurations is available
from the HarvestSource object via the `HarvestSource::filters`,
`HarvestSource::excludes`, `HarvestSource::overrides`,
`HarvestSource::defaults` methods.

### Migration Classes

The common harvest migration logic is encapsulated in the [`HarvestMigration`
class](https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_harvest/dkan_harvest.migrate.inc#L15),
(which extends the [MigrateDKAN](https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_migrate_base/dkan_migrate_base.migrate.inc#L241) class provided
via the [DKAN Migrate Base](https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_migrate_base)
module. DKAN Harvest will support only migration classes extended from
`HarvestMigration`. This class is responsible for consuming the downloaded data
during the harvest cache step to create the DKAN `dataset` and associated
nodes.

Implementing a Harvest Source Type Migration class is the matter of checking
couple of boxes:

* Wire the cached files on the `HarvestMigration::__construct()` method.
* Override the fields mapping on the `HarvestMigration::setFieldMappings()` method.
* Add alternate logic for existing default DKAN fields or extra logic for
  custom fields on the `HarvestMigration::prepareRow()` and the
  `HarvestMigration::prepare()`.

Working on the Migration Class for Harvest Source Type should be straitforward,
but a good knowladge on how [migrate
works](https://www.drupal.org/node/1006982) is a big help.

#### `HarvestMigration::__construct()`

Setting the `MigrateSourceList` is the only logic required during the
construction of the extended `HarvestMigration`. During the harvest migration
we can't reliably determin and parse the type of cache file (JSON, XML, etc..)
so we still need to provide this information to the Migration class via the
`MigrateItem` variable. the Migrate module provide different helpful class for
different input file parsing (`MigrateItemFile`, `MigrateItemJSON`,
`MigrateItemXML`). For the the POD `dkan_harvest_datajson` reference
implementation we use the `MigrateItemJSON` class to read the JSON files
downloaded from data.json end-points.

```php
public function __construct($arguments) {
  parent::__construct($arguments);
  $this->itemUrl = drupal_realpath($this->dkanHarvestSource->getCacheDir()) .
    '/:id';

  $this->source = new MigrateSourceList(
    new HarvestList($this->dkanHarvestSource->getCacheDir()),
    new MigrateItemJSON($this->itemUrl),
    array(),
    $this->sourceListOptions
  );
}
```

#### `HarvestMigration::setFieldMappings()`

The default Mapping for all the default DKAN fields and properties is done on
the `HarvestMigration::setFieldMapping()` method. Overriding one or many field
mapping is done by overrrding the `setFieldMapping()` in the child class and
add/update the new/changed fields.

For example to override the mapping for the `og_group_ref` field.
```php
  public function setFieldMappings() {
    parent::setFieldMappings();
    $this->addFieldMapping('og_group_ref', 'group_id');
```

#### Resources import
The base `HarvestMigration` class will (by default) look for a `$row->resources` objects
array that should contain all the data needed for constructing the resource
node(s) associated with the dataset. the helper method
`HarvestMigration::prepareResourceHelper()` should make creating the
`resources` array items more streamlined.

Example code snippet:
```php
/**
 * Implements prepareRow.
 */
public function prepareRow($row) {
  // Redacted code

  $row->resources = $this->prepareRowResources($row->xml);

  // Redacted code
}
```

#### Harvest and [DKAN Workflow](https://github.com/NuCivic/dkan_workflow) support
By default, DKAN Harvest will make sure that the harvested dataset node will be
set to the `published` moderation state if the DKAN Workflow module is enabled
on the DKAN site. This can be changed at the fields mapping level by overriding
the `workbench_moderation_state_new` field.
