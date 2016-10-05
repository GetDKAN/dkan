DKAN Migrate Base
=================
This provides base classes for common DKAN migrations (ie imports or harvests).

The base classes will import Datasets, resources, tags, groups, and users from a CKAN site.

To use, create your own migration and create a class that inherits MigrateCkanDatasetBase (code examples coming soon) or change the endpoint ``$this->endpoint = 'http://demo.ckan.org/api/3/action/';`` to your favorite CKAN or DKAN site.

### Migrate Module
This uses the Migrate module which is well documented: https://www.drupal.org/node/415260

Once setup, migrations can be run through the user interface:

![screen shot 2014-08-19 at 9 49 02 am](https://cloud.githubusercontent.com/assets/512243/3968050/13c20b04-27b3-11e4-9365-3567a9adcc2d.png)

through the command line, or run periodically.

### Example module

We have provided an example module in this repo. To create a custom migration just create a module that inherits the Resource and Dataset classes and puts in the endpoint for your CKAN instance: https://github.com/NuCivic/dkan_migrate_base/blob/master/modules/dkan_migrate_base_example/dkan_migrate_base_example.module#L41

### Periodic Migrations
After the initial time the migration is run it will check each dataset and resource from the CKAN instance and only update items that have changed in CKAN.

### Mappings

#### Dataset

##### POD 1.0
```
'title' => 'title',
'body' => 'description',
'og_group_ref' => 'publisher',
'field_tags' => 'keyword',
'field_modified_source_date' => 'modified',
'created' => 'issued',
'field_public_access_level' => 'accessLevel',
'field_resources' => 'resources',
'field_contact_name' => 'contactPointName',
'field_contact_email' => 'mbox',
'uuid' => 'identifier',
'field_license' => 'license',
'field_spatial_geographical_cover' => 'spatial',
'field_temporal_coverage' => 'temporalBegin',
'field_temporal_coverage:to' => 'temporalEnd',
'field_frequency' => 'accrualPeriodicity',
'field_data_dictionary' => 'dataDictionary',
'field_is_part_of' => 'isPartOf',
'field_landing_page' => 'landingPage',
'field_rights' => 'rights',
'field_pod_theme' => 'theme',
'field_conforms_to' => 'conformsTo',
'field_data_dictionary_type' => 'describedByType',
'field_language' => 'language',
'language' => 'language',
```

##### POD 1.1
```      
'title' => 'title',
'body' => 'description',
'og_group_ref' => 'group_id',
'field_tags' => 'keyword',
'field_modified_source_date' => 'modified',
'created' => 'issued',
'field_public_access_level' => 'accessLevel',
'field_resources' => 'resources',
'field_contact_name' => 'contactPointName',
'field_contact_email' => 'mbox',
'uuid' => 'identifier',
'field_license' => 'license',
'field_spatial_geographical_cover' => 'spatial',
'field_temporal_coverage' => 'temporalBegin',
'field_temporal_coverage:to' => 'temporalEnd',
'field_frequency' => 'accrualPeriodicity',
'field_data_dictionary' => 'describedBy',
'field_additional_info' => 'any additional info key',
'field_additional_info:second' => 'any additional info value',
'field_related_content' => 'references',
'field_is_part_of' => 'isPartOf',
'field_landing_page' => 'landingPage',
'field_pod_theme' => 'theme',
'field_conforms_to' => 'conformsTo',
'field_data_dictionary_type' => 'describedByType',
'field_language' => 'language',
'field_rights' => 'rights',
```

#### CKAN
```
'title' => 'title'
'field_license' => 'license_title'
'created' => 'metadata_created'
'changed' => 'metadata_modified'
'field_author' => 'author'
'field_contact_email' => 'author_email'
'uid' => 'uid'
'id' => 'uuid'
'path' => 'name'
'body' => 'notes'
'field_spatial_geographical_cover' => 'spatialText'
'field_spatial' => 'spatial'
'field_resources' => 'resource_ids'
'field_tags' => 'tag_names'
'field_additional_info' => 'any additional info key'
'field_additional_info:second' => 'any additional info value'
```

##### Open Federal Extra Fields
```
'field_odfe_bureau_code' => 'bureauCode',
'field_odfe_program_code' => 'programCode',
'field_odfe_data_quality' => 'dataQuality',
'field_odfe_investment_uii' => 'primaryITInvestmentUII',
'field_odfe_system_of_records' => 'systemOfRecords',
```

##### Notes
**publisher POD:** is being mapped to a DKAN group. If that group doesn't exists then is created.
**field_additional_info:** is a DKAN field that holds json keys that can't be mapped to any other DKAN field. 
**open_data_federal_extras:** is a module that can be enabled to add fields that are present in the POD spec but aren't present in DKAN out-of-the-box. By enabling this module you are adding these fields to your DKAN entities (datasets and resources).

### Resources
TODO

### Documentation
We are working on improving this documentation. Please let us know if you have any questions in the mean time.


### Contributing

We are accepting issues in the dkan issue thread only -> https://github.com/NuCivic/dkan/issues -> Please label your issue as **"component: dkan_migrate_base"** after submitting so we can identify problems and feature requests faster.

If you can, please cross reference commits in this repo to the corresponding issue in the dkan issue thread. You can do that easily adding this text:

```
NuCivic/dkan#issue_id
``` 

to any commit message or comment replacing **issue_id** with the corresponding issue id.

