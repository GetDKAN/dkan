DKAN Feedback
=============

The `DKAN Feedback <https://github.com/GetDKAN/dkan_feedback>`_ module allows site visitors to give feedback on published datasets. 
This module is not included in `DKAN <https://github.com/GetDKAN/dkan>`_ core, but it can be added as an additional feature.

**Requirements**

- Full installation of core DKAN >=7.x-1.13. 
- Dependencies include `Captcha <https://www.drupal.org/project/captcha>`_, `Custom Publishing Options <https://www.drupal.org/project/custom_pub>`_, `Rate <https://www.drupal.org/project/rate>`_, `reCAPTCHA <https://www.drupal.org/project/recaptcha>`_, and `Voting API <https://www.drupal.org/project/votingapi>`_.

Installation
------------

**Using drush make**

.. code-block:: bash

  cd sites/all/modules/contrib
  git clone https://github.com/GetDKAN/dkan_feedback
  drush make --no-core sites/all/modules/contrib/dkan_feedback/dkan_feedback.make
  drush en dkan_feedback

**Manual installation**

- Download the zip file from https://github.com/GetDKAN/dkan_feedback
- Unzip the file in ``/sites/all/modules/contrib``
- Download all dependent contrib modules from the Requirements list above and add them to ``/sites/all/modules/contrib``
- ``drush en dkan_feedback -y``

Usage
-----

Once enabled, a "Feedback" menu item will be added to the main menu. Clicking this link will display a list of all published feedback.

Besides giving feedback, visitors can also "up vote" or "down-vote" feedback provided by other users, and leave comments on published feedback.

Multiple search and sort options are provided on the primary Feedback page, by default, feedback is listed from newest to oldest.


Administration
--------------

As feedback does not require authentication, it is important for site managers to stay on top of new entries, captcha is not fool-proof. 

From the administration menu, a site manager can access the feedback and comment administration dashboards to bulk publish, unpublish, or archive
feedback and comment content. For agencies that are required to NOT delete content, the 'Archive' option allows site managers to avoid 
repeated moderation of the same unpublished content.


Providing feedback on DKAN Feedback
-----------------------------------

Submit contributions, bug reports or enhancement requests on Github at `https://github.com/GetDKAN/dkan_feedback <https://github.com/GetDKAN/dkan_feedback>`_.
