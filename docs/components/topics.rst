DKAN Topics
===========

While DKAN includes a free-tagging tags/keywords field for datasets, many data portals organize datasets into more predefined categories by subject matter. These are usually a small collection of subjects with logos that are incorporated into the site navigation. Neither DKAN's tags or "groups" (which are designed for grouping user permissions and usually represent organizational divisions) are exactly appropriate for this task.

The DKAN Topics module adds a "topics" vocabulary to DKAN, and corresponding functionality throughout the site. It adds a facet to the search/datasets page, and a pane to the default homepage. Topics can be administered through the standard Drupal taxonomy interface.

The included `DKAN Default Topics <https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_topics/modules/dkan_default_topics>`_ module will add, on enable, a set of default civic topics using the `Taxonomy Fixtures <https://github.com/NuCivic/taxonomy_fixtures>`_ module.

DKAN Topics is enabled by default on new DKAN installations, with default terms loaded into the vocabulary. The module can be disabled and uninstalled, and all existing topics will be removed.

Permissions
------------------------------------

* Users with the Site Manager or Editor role can add and edit topic terms.
* Users with the Administrator role can add new icons.

Adding a new topic term
------------------------------------

From the Administration menu, navigate to ``Site Configuration > Taxonomy > Topics > Add term``

:Name: Enter the term for your new topic.
:Description: This field is not currently displayed publicly.
:Icon Type: DKAN Topics comes with a default set of font icons that can be used with your terms, you can upload your own font icons if desired. See Adding new icons. Or you may select to use image icons, when you toggle the image option, an image upload input field will appear.
:Icon: If using font icons, select the icon you want to associate with your topic term.
:Icon Color: Icons will display the same color as text on datasets unless a specific color is selected here.

Editing topic terms
------------------------------------

1. From the Administration menu, navigate to ``Site Configuration > Taxonomy > Topics``
2. You will see a list of current topic terms, click the 'edit' link under Operations that corresponds to the term you would like to edit. 
3. Make changes and click "Save".

Removing Topics from the main menu
------------------------------------

1. Navigate to ``Site Configuration > Menus``
2. On the Menus screen, click "list links"
3. Uncheck the box under "Enabled" for Topics
4. Click "Save configuration"

.. _`adding_new_icons`:

Adding new icons
------------------------------------
The font used for Topics can only be changed if there are **NO** default icon values in use, only one icon font can be used at a time.

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
* This module adds a main menu link for "Topics". If you want a different word in place of "Topics", you can change the name in the main menu configuration but the icons in the dropdown will stop working. If you use `String Overrrides <https://www.drupal.org/project/stringoverrides>`_ you can change the Menu link title and the icons will continue to work, however the facet block title and the dataset form field title will still display as 'Topics'.
* Adding a new icon font for use with topics **needs work** to keep the icon functionality in facets and menus from breaking.
