Ajaxify Search API pages.

INSTALLATION AND CONFIGURATION

This Ajax module does not understand your theme CSS id's by default. You must
implement a custom module hook to let it know about your theme.
 
For example, create and enable a custom mymodule.module containing this code:

<?php
/**
 * Implements hook_search_api_ajax_settings().
 */
function mymodule_search_api_ajax_settings() {
  $settings = array(
  
    // CSS id for main content (search results html)
    // format: content => CSS id
    'content' => '#content .content',
    
    // CSS id's for regions containing search blocks
    // check your region names in mytheme.info
    // format: region_name => CSS id
    'regions' => array(
      'sidebar_first' => '#sidebar-first',
      'sidebar_second' => '#sidebar-second',
    ),

    // OPTIONAL: if you want to decide which regions links are
    // AJAXified. Will allow you to have links in the search result
    // that have the same base URL for the search.
    facet_locations = '#sidebar-first';
    
    // OPTIONAL: if you want to provide an AJAX spinner
    // this paht is for a default spinner path provided with this module
    // @note: see the search_api_ajax.css
    'spinner' => drupal_get_path('module', 'search_api_ajax') .'/spinner.gif',
    
    // OPTIONAL: if you want to use scroll-to-top functionality when paging
    // scroll target div
    'scrolltarget' => '#main-content',
    'scrollspeed' => 1000,
    
    // OPTIONAL: if you want to fade search results when Ajaxing
    // please set to 1 for TRUE
    'fade' => 1,
    'opacity' => 0.3,    
  );
  
  return $settings;
}

ADDING MODULES' BLOCKS TO THE AJAX SYSTEM

If you want your third party modules blocks to show up in the AJAX JSON,
then you need to register them with a custom function:

@see function search_api_ajax_modules() (default modules are included)

/**
 * Implements hook_search_api_ajax_modules_alter().
 *
 * Add custom modules to search api ajax blocks.
 */
function mycustommodule_search_api_ajax_modules_alter(&$modules) {
  $modules[] = 'custom_module';
}

