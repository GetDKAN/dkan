## DKAN Topics

While DKAN includes a free-tagging tags/keywords field for datasets, many data portals organize datasets into more predefined catagories by subject matter. These are usually a small collection of subjects with logos that are incoporated into the site navegation. Neither DKAN's tags or "groups" (which are designed for grouping user permissions and usually represent organizational divisions) are exactly appropriate for this task.

The DKAN Topics module adds a "topics" vocabulary to DKAN, and corresponding functionality throughout the site. It adds a facet to the search/datasets page, and a pane to the default homepage. Topics can be administered through the standard Drupal taxonomy interface.

DKAN Topics is enabled by default on new DKAN installations, with default terms loaded into the vocabulary. The module can be disabled and all existing topics will be removed.

### Known Issues

* When enabling this module on an existing DKAN site, the default topics may be added to the top-level navigation menu. If this happens, simply use the taxonomy UI to move the new terms under the "Topics" term. New topics you add should then be added under the Topics term in the main menu.
* The module adds menu items under "Topics" for any new topic you add. At present, this functionality cannot be disabled. If you want a different behavior in the main navegation bar (a different word in place of "Topics", or no presence at all for Topics in the main navegation), you will need to modify every menu item created by the module after adding a new topic. Future versions should add more flexibility.
