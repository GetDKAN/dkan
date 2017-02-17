DKAN Topics
===========

While DKAN includes a free-tagging tags/keywords field for datasets, many data portals organize datasets into more predefined catagories by subject matter. These are usually a small collection of subjects with logos that are incoporated into the site navigation. Neither DKAN's tags or "groups" (which are designed for grouping user permissions and usually represent organizational divisions) are exactly appropriate for this task.

The DKAN Topics module adds a "topics" vocabulary to DKAN, and corresponding functionality throughout the site. It adds a facet to the search/datasets page, and a pane to the default homepage. Topics can be administered through the standard Drupal taxonomy interface.

The included `DKAN Default Topics <https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_topics/modules/dkan_default_topics>`_ module will add, on enable, a set of default civic topics using the `Taxonomy Fixtures <https://github.com/NuCivic/taxonomy_fixtures>`_ module.

DKAN Topics is enabled by default on new DKAN installations, with default terms loaded into the vocabulary. The module can be disabled and uninstalled, and all existing topics will be removed.

Permissions
------------------------------------

* Users with the Site Manager or Editor role can add and edit topic terms.
* Users with the Administrator role can add new icons.

Adding a new topic term
------------------------------------

From the Administation menu, navigate to ``Site Configuration > Taxonomy > Topics > Add term``

:Name: Enter the term for your new topic.
:Description: This field is not currently displayed publicly.
:Icon Type: DKAN Topics comes with a default set of font icons that can be used with your terms, you can upload your own font icons if desired. See Adding new icons. Or you may select to use image icons, when you toggle the image option, an image upload input field will appear.
:Icon: If using font icons, select the icon you want to associate with your topic term.
:Icon Color: Icons will display the same color as text on datasets unless a specific color is selected here.

Editing topic terms
------------------------------------

1. From the Administation menu, navigate to ``Site Configuration > Taxonomy > Topics``
2. You will see a list of current topic terms, click the 'edit' link under Operations that corresponds to the term you would like to edit. 
3. Make changes and click "Save".

Removing Topics from the main menu
------------------------------------

1. Navigate to ``Site Configuration > Menus``
2. On the Menus screen, click "list links"
3. Uncheck the box under "Enabled" for Topics
4. Click "Save configuration"

Adding new icons
------------------------------------

**3 Important notes:** 
  * Only users with the Administrator role can add new icon fonts.
  * The font can only be changed if there are **NO** default icon values in use. Only one icon font can be used at a time.
  * The display of the icons in facets and the drop down menu will break.
1. Navigate to ``Configuration > Content Authoring > Font Icon Select Options``
2. Click "Upload New Library"
3. Enter a title for your new font option
4. Upload the four files for your font
5. Click "Save"
6. Navigate to ``Structure > Taxonomy > Topics > Manage Fields > Icon``
7. Select your font from the font dropdown in the Icon field settings section.
8. Click "Save settings"


Known Issues
------------------------------------

* When enabling this module on an existing DKAN site, the default topics may be added to the top-level navigation menu. If this happens, simply use the taxonomy UI to move the new terms under the "Topics" term. New topics you add should then be added under the Topics term in the main menu.
* The module adds a main menu link for "Topics". If you want a different word in place of "Topics", you will need to modify the term from the taxonomy UI. This will cause the DKAN Topics feature to be overridden and make future upgrades more difficult.
* To filter by topics on the Datasets/Search page, field_topics needs to be added to the dataset search index. This should happen automatically when you enable this module and revert all DKAN core `features <https://www.drupal.org/project/features>`_. However, in some cases this has been observed not to work. To fix edit the datasets index manually (`admin/config/search/search_api/index/datasets/fields`) and add field_topics.
* Since this module adds a field to the Dataset content type, your search indexes will need to be rebuilt, whether or not the previous step (manually adding the field to the index) is necessary. After installing, clear your dataset index and re-index all items by doing one of the following:
  * Visit `admin/config/search/search_api/index/datasets` and perform the steps in the browser
  * Use drush: ``drush sapi-c datasets && drush sapi-r datasets && drush sapi-i datasets``
