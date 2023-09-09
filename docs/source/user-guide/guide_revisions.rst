Revisions
===========

Revisions allow you to track changes made to a dataset. When content is edited, Drupal
will retain a copy of the current version, and create a new version alongside it.
Over time, you might end up with many different versions of the same dataset, each
reflecting a previous variant. While all of these different copies are stored in the
database, only one of them is the one that's used when you navigate to the dataset
in your browser.

When implementing DRAFT states, it is possible to create a revision that is newer than
the currently published revision. This is called a pending revision.
