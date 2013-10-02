<?php
/**
 * @file
 * This file documents hooks provided by the FileField Sources module. Note that
 * none of this code is executed by using FileField Sources module, it is
 * provided here for reference as an example how to implement these hooks in
 * your own module.
 */

/**
 * Returns a list of widgets that are compatible with FileField Sources.
 *
 * FileField Sources works with the most common widgets used with Drupal (the
 * standard Image and File widgets). Any module that provides another widget
 * for uploading files may add compatibility with FileField Sources by
 * implementing this hook and returning the widgets that their module supports.
 */
function hook_filefield_sources_widgets() {
  // Add any widgets that your module supports here.
  return array('mymodule_file_widgetname');
}

/**
 * Return a list of available sources that FileField Sources can use.
 *
 * This hook returns a list of possible sources that can be utilized. Each
 * source must be enabled by the end user before it can be used on a file field.
 * Note that the ability to provide a configuration for this source is not
 * directly provided by FileField Sources, instead you may implement the
 * form_alter() hooks provided by Drupal core to add your options to the
 * existing list of FileField Source options.
 */
function hook_filefield_sources_info() {
  $sources = array();

  // Provide a potential Flickr source to import Flickr photos.
  $sources['flickr'] = array(
    'name' => t('File attach from Flickr'),
    'label' => t('Flickr'),
    'description' => t('Select a file from Flickr.'),
    // This callback function does all the heavy-work of creating a form element
    // to choose a Flickr photo and populate a field. For an example, see
    // filefield_source_remote_process().
    'process' => 'mymodule_filefield_source_flickr_process',
    // This callback function then takes the value of that field and saves the
    // file locally. For an example, see filefield_source_remote_value().
    'value' => 'mymodule_filefield_source_flickr_value',
    'weight' => 3,
    // This optional setting will ensure that your code is included when needed
    // if your value, process, or other callbacks are located in a file other
    // than your .module file.
    'file' => 'include/mymodule.flickr_source.inc',
  );
  return $sources;
}
