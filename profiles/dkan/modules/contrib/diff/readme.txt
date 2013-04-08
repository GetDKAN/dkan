Diff module - http://drupal.org/project/diff
============================================

Diff enhances usage of node revisions by adding the following features:

- Diff between node revisions on the 'Revisions' tab to view all the changes
  between any two revisions of a node.
- Highlight changes inline while viewing a node to quickly see color-coded
  additions, changes, and deletions.
- Preview changes as a diff before updating a node.

It is also an API to compare any entities although this functionality is not
exposed by the core Diff module.

REQUIREMENTS
------------
Drupal 7.x

INSTALLATION
------------
1.  Place the Diff module into your modules directory.
    This is normally the "sites/all/modules" directory.

2.  Go to admin/build/modules. Enable the module.
    The Diff modules is found in the Other section.

Read more about installing modules at http://drupal.org/node/70151

See the configuration section below.

UPGRADING
---------
Any updates should be automatic. Just remember to run update.php!

CONFIGURATION
-------------

Unlike the earlier version, the module now has a lot of configurable settings.

Global settings can be found under Configuration > Content > Diff

i.e. http://www.example.com/admin/config/content/diff

Entity specific settings would be listed under the entities settings. This 
module only handles Node revisioning functionality, and these are detailed 
below.

1) Node revisioning settings

Diff needs to be configured to be used with specific node types on your site.
To enable any of Diff's options on a content type's settings page.

e.g. http://www.example.com/admin/structure/types/manage/page

  a) Diff options

  Under "Compare revisions", enable the settings that you want;
  
    i) "Show View changes button on node edit form" adds a new "Preview" like
        submit button to node editing pages. This shows a diff preview.
  
    ii) "Enable the Revisions page for this content type" adds the revisioning
         tab to content. This allows users to compare between various revisions
         that they have access to.
  
    iii) "Standard comparison preview" option allows you to control how the most
          current revision is show on the revision comparision page.
       
  b) Publishing options

  It is strongly advised that you also enable the automatic creation of
  revisions on any content types you want to use this with. If you do not do
  this, chances are there will be limited revisioning information available to
  compare. 

  Under "Publishing options", enable "Create new revision".

2) Field revisioning settings

   Global settings per field type can be found here:

   http://www.example.com/admin/config/content/diff/fields

   "Show field title" toggles field title visibility on the comparison page.
   
   "Markdown callback" is the callback used to render the field when viewing the
   page in the "Marked down" page view.
   
   "Line counter" is an optional. This shows the approximate line number where
   the change occurred. This is an approximate counter only.
   
   Other fields add additional settings here.
   
3) Entity revisioning settings

  Global configurable settings limited to node entities.

  a) Show entity label header
  
  This provides a field like title for the entity label field.
  
  i.e. For nodes, this provides a header for the node's title. 
  
  b) Treat diff pages as administrative
  
  By default, the revisioning pages are administrative, i.e. they will use the
  administration theme. You can block this by unchecking this option.
  
4) Global settings

A small number of new features have been added to the 7.x-3.x branch, these
include the ability to change the leading and trailing lines in the comparison,
a new CSS theme for the diff pages, new JScript options for the revisioning
selection form and options to help prevent cross operating systems in relation
to line endings.

http://www.example.com/admin/config/content/diff

Technical
---------
- Diff compares the raw data, not the filtered output, making it easier to see
changes to HTML entities, etc.
- The diff engine itself is a GPL'ed php diff engine from phpwiki.

API
---
See diff.api.php

Maintainers
-----------
- realityloop (Brian Gilbert)
- Alan D. (Alan Davison)
- dww (Derek Wright)
- moshe (Moshe Weitzman)
- rötzi (Julian)
- yhahn (Young Hahn)
