# DKAN JS Frontend Module

This is an integration module that provides a way to access a decoupled JavaScript frontend at defined paths.

## Defining Paths
The paths used are defined by the `dkan_js_frontend.config` yml. In the routes array add any path you want this module to use with a string structured like `unique_path_name,/the_path`. The first part is used by Drupal to store paths and the second is the actual path the JS frontend will show at.

### Sitemap Generation - the [Simple XML sitemap module](https://www.drupal.org/project/simple_sitemap)
If the Drupal [Simple XML sitemap module](https://www.drupal.org/project/simple_sitemap) is installed, the DKAN JS Frontend module will automatically add static routes and dataset routes listed in the `dkan_js_frontend.config` yml to the default sitemap.

## JS/CSS
The module assumes Create React App (CRA) has been loaded into the `/src/frontend` folder of the site. This can be changed by updating the `dkan_js_frontend.config` keys of `css_folder` and `js_folder` with the new directory path.

The code will glob all files in the folders specified and attach them to any route/path that has been defined. The glob functionality should allow you to get around issues like CRA's hash in file names. The JS/CSS is directly attached to the page template provided, so the generated `index.html` file from CRA will not be used so all header updates will need to be made in Drupal and not the public files provided by the JS framework.

## [Data Catalog App](https://github.com/GetDKAN/data-catalog-app#readme)

This is a React app that will use the [Data Catalog Components](https://github.com/GetDKAN/data-catalog-components) library to build the frontend. It will serve as a starting point for your own customizations. If you are **not** using DKAN Tools, follow the instructions on the [README file](https://github.com/GetDKAN/data-catalog-app/blob/master/README.md) for manual installation.

## Tips
If you are setting the JS frontend as the main frontend for your site, like on the demo DKAN site, you will want to do the following:

* Set the `404` and `home` paths for the site to `/home`. This will make it so whenever Drupal returns a page not found it will load the JS frontend and visiting the root url the JS site will be loaded instead of a default Drupal node.
