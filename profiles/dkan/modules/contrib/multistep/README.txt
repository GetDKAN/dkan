
Summary
=======
Multistep adds multiple-step functionality to node type editing forms. It does
so by assigning a step number to each field or field group within the node type
and hiding all the fields or groups that do not belong to the current step. The
user can then use different submitting buttons that will redirect to the
previous, next, or current step.

The module also provides a block for each node type with a menu of the
different groups within that form and a progress bar. This provides an easy way
to jump to different steps throughout the form without having to go one by one
as well as keeping track of the progress of the form.

For a full description visit the project page:
  http://drupal.org/project/multistep
  
Bug reports, feature suggestions and latest developments:
  http://drupal.org/project/issues/multistep

Requirements
============
This module requires Fields, which is part of Drupal core. It also benefits
strongly from Field group, which can be found here:
  http://drupal.org/project/field_group

To Use
======
To use this module, go into the node type editing form in Structure >> Content
types and select the content type you want to enable Multistep for.
  
There will be a collapsed Multistep Form section below, mark it as Enabled and
enter the amount of steps that you want this form to span.
  
Now, whenever you add or edit a group (or a field that does not belong to any
group), you will be able to select which step that group belongs to. The group
will only be shown when in that step, or in all of them if All is selected as
an option.

If you are configuring multistep for a content type that already had data
previously, you should go to Configuration >> Multistep and reset the table for
that node type. This will create step data for all nodes that were previously
created.

If you have a Taxonomy vocabulary set for the content type, you will see an
option to set which step it should belong to in the content type editing form
after you save the number of steps.

Configuration
=============
To configure the multistep menu and the progress bar, go to Administer >> Site
building >> Blocks and configure the corresponding block that will appear on
the list. You can select whether to enable or disable the menu and the progress
bar.

To remove/show the Preview button on the node editing form, go to the content
type editing form in Administer >> Content management >> Content types and
check/uncheck the box that says "Hide Preview button".

To change the text that appears on the different buttons of the form (Previous,
Next, Save, Done), go to the admin settings page in Administer >> Site
configuration >> Multistep and modify the values shown in the Navigation button
labels section.

Users with "toggle multistep" permission can select whether to view the entire
form in a single page or the multistep form split over multiple pages. This is
useful for vieweing a whole form at a glance before starting to enter the data.

You can also set whether the default display of the form is the multistep form
or the entire form. Only users with "toggle multistep" permissions will be able
to switch displays.

Development
===========
For hooks provided by Multistep, read multistep.api.php

If you create a module that defines fieldsets for CCK, you have to implement
hook_content_extra_fields(). See http://drupal.org/node/901420 for information
on how to implement this specific hook.

Credits
=======
Author: Victor Kareh (vkareh) - http://www.vkareh.net
