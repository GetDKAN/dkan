## DKAN Topics

While DKAN includes a free-tagging tags/keywords field for datasets, many data portals organize datasets into more predefined catagories by subject matter. These are usually a small collection of subjects with logos that are incoporated into the site navegation. Neither DKAN's tags or "groups" (which are designed for grouping user permissions and usually represent organizational divisions) are exactly appropriate for this task.

The DKAN Topics module adds a "topics" vocabulary to DKAN, and corresponding functionality throughout the site. It adds a facet to the search/datasets page, and a pane to the default homepage. Topics can be administered through the standard Drupal taxonomy interface.

The included [DKAN Default Topics](https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_topics/modules/dkan_default_topics) module will add, on enable, a set of default civic topics using the [Taxonomy Fixtures](https://github.com/NuCivic/taxonomy_fixtures) module.

DKAN Topics is enabled by default on new DKAN installations, with default terms loaded into the vocabulary. The module can be disabled and uninstalled, and all existing topics will be removed.

### Known Issues

* When enabling this module on an existing DKAN site, the default topics may be added to the top-level navigation menu. If this happens, simply use the taxonomy UI to move the new terms under the "Topics" term. New topics you add should then be added under the Topics term in the main menu.
* The module adds menu items under "Topics" for any new topic you add. At present, this functionality cannot be disabled. If you want a different behavior in the main navegation bar (a different word in place of "Topics", or no presence at all for Topics in the main navegation), you will need to modify every menu item created by the module after adding a new topic. Future versions should add more flexibility.
* To filter by topics on the Datasets/Search page, field_topics needs to be added to the dataset search index. This should happen automatically when you enable this module and revert all DKAN core [features](https://www.drupal.org/project/features). However, in some cases this has been observed not to work. To fix edit the datasets index manually (`admin/config/search/search_api/index/datasets/fields`) and add field_topics.
* Since this module adds a field to the Dataset content type, your search indexes will need to be rebuilt, whether or not the previous step (manually adding the field to the index) is necessary. After installing, clear your dataset index and re-index all items by doing one of the following:
  * Visit _admin/config/search/search_api/index/datasets_ and perform the steps in the browser
  * Use drush: `drush sapi-c datasets && drush sapi-r datasets && drush sapi-i datasets`
