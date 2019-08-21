DKAN Feedback
=============

The DKAN Feedback module allows users and visitors to add feedback on published datasets and resources. Although this module is not available within out-of-the-box DKAN, it can be installed using `drush
make <https://github.com/NuCivic/nucivic-process/wiki/Using-drush-make-in-individual-modules>`_. 

You can view the DKAN Feedback module on Github at `https://github.com/GetDKAN/dkan_feedback <https://github.com/GetDKAN/dkan_feedback>`_.

Once published, individual pieces of feedback can be "upvoted" or "downvoted" by site visitors in order to bring the most relevant site comments to the top of the list.

A full listing of site feedback is located at ``/feedback``, with multiple search and sort options provided. On the primary Feedback page, feedback is published from newest to oldest by default regardless of how it has been upvoted or downvoted.

If the `Workflow module <https://docs.getdkan.com/en/latest/components/workflow.html>`_ has been installed, unpublished feedback nodes can be viewed in one location for efficient moderation at ``/admin/workbench/unpublished-nodes``.

For a live example of how the Feedback module is used within DKAN, please see `HealthData.gov's Feedback page <https://healthdata.gov/feedback>`_.

Requirements
*************

- Full installation of core DKAN 7.x.1.x. and all external dependencies outside of core DKAN encapsulated in the ``dkan_feedback.make`` file. 
- Necessary dependencies include `Rate <https://www.drupal.org/project/rate>`_, the `Voting API <https://www.drupal.org/project/votingapi>`_, and `Captcha <https://www.drupal.org/project/captcha>`_ to avoid spam feedback.

Installation
------------

This module needs to be built using `drush make <https://github.com/NuCivic/nucivic-process/wiki/Using-drush-make-in-individual-modules>`_ before being enabled. If you download only the DKAN Feedback module itself, you will miss key dependencies for required modules and libraries.

To install:
************

```
  cd <path to modules directory>
  git clone https://github.com/NuCivic/dkan_feedback
  drush make --no-core <path to modules directory>/dkan_feedback/dkan_feedback.make
  drush en dkan_feedback
```

Providing feedback on DKAN Feedback
------------------------------

Please feel free to submit contributions, bug reports or enhancement requests on Github at `https://github.com/GetDKAN/dkan_feedback <https://github.com/GetDKAN/dkan_feedback>`.
