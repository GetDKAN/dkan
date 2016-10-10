# Adding DKAN Features to an Existing Drupal Site

The [DKAN Dataset](https://github.com/nuams/dkan_dataset) and [DKAN Datastore](https://github.com/nuams/dkan_datastore) modules can be used independently of the DKAN distribution. These features include the [Dataset](/node/56) and Resource content types as well as the file preview and Datastore. This capability allows users to add a full featured open data catalog to an existing Drupal 7 site.

### DKAN Dataset Modules

The [DKAN Dataset](https://github.com/nuams/dkan_dataset) module contains the following modules:

#### DKAN Dataset (required)

This module captures [metadata for Datasets](http://docs.getdkan.com/what-dataset)<a>.

#### DKAN Dataset Groups (optional)

Provides group functionality based on the organic groups module.

#### DKAN Dataset API (optional)

Provides</a> [Dataset API](/dkan-documentation/dkan-api/dataset-api) functionality.

### DKAN Datastore Modules

The DKAN Datastore module provides the ability to store the contents of delimited files into a datastore, preview them, and query them with a public API.

#### DKAN Datastore (required)

Creates user interface for Datastore functionality as well as a swappable Datastore. By default this module uses a native Datastore. However there is a [Datastore class](https://github.com/nuams/dkan_datastore/blob/7.x-1.x/includes/Datastore.inc) that can be extended to include other datastores such as [CartoDB](https://github.com/nuams/dkan_datastore_cartodb) (in progress).

#### DKAN Datastore API (optional)

Provides an API for the default DKAN Datastore.

### Installing DKAN Dataset

DKAN Dataset requires specific versions of modules and libraries which are specified in the [dkan_dataset.make](https://github.com/nuams/dkan_dataset/blob/7.x-1.x/dkan_dataset.make) file. Because of the specific module versions and library dependencies **you must use drush make before installing DKAN Dataset.** Below is a recipe for installing DKAN Dataset into a new Drupal 7 site. The following can be adjusted for an existing Drupal 7 site:

~~~~
# Download drupal into folder named "webroot"
drush dl drupal --drupal-project-rename=webroot

# Move to modules folder
cd webroot/sites/all/modules

# Clone DKAN Dataset 
git clone https://github.com/nuams/dkan_dataset.git

# Checkout latest tag
cd dkan_dataset
git checkout 7.x-1.0

# Move to webroot/sites/all
cd ../../

# Copy make file to webroot/sites/all/dkan_dataset.make
cp modules/dkan_dataset/dkan_dataset.make .

# Make required modules and libraries.
drush make --no-core dkan_dataset.make --contrib-destination="."

# Enable DKAN Dataset!
# Currently we have to enable dkan_dataset_content_types. See https://github.com/nuams/dkan/issues/140 for fix
drush en -y dkan_dataset_content_types
~~~~

### Installing DKAN Datastore

DKAN Datastore integrates a datastore with the "Resources" provided by DKAN Dataset. Delimited data can be taken from the file field in the resource content type, parsed and placed in a datastore, and exposed via an API. After installing DKAN Dataset, follow similar steps for DKAN Datastore. DKAN Datastore requires using dkan_datstore.make file to download the dependencies.

### DKAN Sitewide Features

The rest of the features in the DKAN distribution are captured in the [DKAN Sitewide](https://github.com/nuams/dkan/tree/7.x-1.x/modules/dkan/dkan_sitewide) module. Developers can cherry pick these features by grabbing them from the DKAN Sitewide module and [submodules](https://github.com/nuams/dkan/tree/7.x-1.x/modules/dkan/dkan_sitewide/modules). 

![](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-06-01%20at%2012.02.50%20PM.png)