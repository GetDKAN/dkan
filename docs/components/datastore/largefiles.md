#Large File Support in DKAN

DKAN will allow file uploads as large as your server configuration will allow. The file upload limit is a PHP setting that can be modified using [a few different strategies explained in the Drupal documentation](https://www.drupal.org/node/97193) and elsewhere.

While there is no specific upper limit to the file upload size you can allow through PHP's settings, the larger files become the more impractical the standard HTTP file upload form element used on the "add Resource" form becomes. This is because a) larger files demand more memory and other system resources on the web server; and b) the upload will start to take many minutes or even hours using a standard broadband Internet connection, during which time the user will see no indication of the upload's progress or estimate of how long it will take to complete. These two factors begin to become apparent around the 20mb mark. Uploads of 500mb or more are generally impossible with a standard HTTP upload. 

In the open data world, datasets of many hundreds of megabytes or even several gigabytes are not unheard of. A number of 3rd-party libraries offer a way around the limits of HTTP file uploads, Javascript, HTML5, and/or Flash technologies. 

The best existing option for doing this in Drupal is the [Plupload library](http://www.plupload.com/), which has its own well-supported, [community-contributed Drupal module](http://drupal.org/project/plupload). An integration with Plupload exists specifically for the [recline](https://github.com/NuCivic/recline) module, which DKAN uses to provide its standard upload form element. 

**To install:**

1. Download the [Plupload](http://drupal.org/project/plupload) and [**Plupload Recline**](https://github.com/NuCivic/plupload_recline) to _sites/all/modules/contrib_ (or wherever you are keeping your 3rd-party modules).
2. Download the [Plupload library version 1.5.8](https://github.com/moxiecode/plupload/archive/v1.5.8.zip) (newer versions are not supported as of this writing) to _sites/all/libraries_. See the [Plupload module documentation](https://www.drupal.org/node/1647890) for detailed instructions.
3. Enable the Plupload Recline DKAN module (_plupload_recline_dkan_) and its dependencies using [drush](https://github.com/drush-ops/drush) or by visiting _admin/modules_. You may be prompted to download additional modules if you are missing other dependencies. 

The upload widget in DKAN should now look like this:

![Plupload integration](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-20%20at%209.29.26%20AM.png)

Note that you will still need to increase your file upload limit in PHP as detailed at the beginning of this page.

We have successfully tested file uploads of over 1 gigabyte in DKAN on a variety of hosting environments withthe [Plupload Recline DKAN module](https://github.com/NuCivic/plupload_recline#plupload-recline-and-plupload-recline-dkan).