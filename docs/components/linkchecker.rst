DKAN Link Checker
=================

DKAN Link Checker adds configuration and additional reporting to the `link checker <https://www.drupal.org/project/linkchecker>`_ module.

  The Link checker module extracts links from your content when saved and periodically tries to detect broken hypertext links by checking the remote sites and evaluating the HTTP response codes.

Installation
------------
DKAN Link checker will check links in datasets, resources, and harvest sources. It is not enabled by default, to enable it, run these commands:

- ``drush en dkan_linkchecker -y``
- ``drush cc all``
- ``drush linkchecker-analyze``
- ``drush cron``

Links will be processed in batches and it may take a while to go through all of the links of your site.

Permissions
-----------
Users with the **site manager** role will be able to

- View the broken links report at ``admin/reports/dkan-linkchecker-report``
- Access the link checker configuration screen at ``admin/config/content/linkchecker``

For more information on link checker `click here <https://cgit.drupalcode.org/linkchecker/tree/README.txt?h=7.x-1.x>`_
