<?php
function dkan_default_content_available_pages(){
  return array('about');
}

function dkan_default_content_about_page(){
	return array(
    'menu' => array(
      'menu_name' => 'main-menu',
      'link_path' => 'node-name/about',
      'router_path' => 'node/%',
      'link_title' => 'About',
      'options' => array(
        'attributes' => array(
          'title' => '',
        ),
      ),
      'module' => 'menu',
      'hidden' => '0',
      'external' => '0',
      'has_children' => '0',
      'expanded' => '0',
      'weight' => '-5',
      'identifier' => 'main-menu:node-name/about',
    ),
    'sort_value' => '3',
    'exported_path' => 'about',
    'uid' => 1,
    'title' => 'About',
    'status' => '1',
    'promote' => '0',
    'sticky' => '0',
    'type' => 'page',
    'language' => 'und',
    'created' => '1357935656',
    'comment' => '0',
    'translate' => '0',
    'machine_name' => 'about',
    'body' => array(
      'und' => array(
        0 => array(
          'value' => 'DKAN is the Drupal-based version of CKAN, the world\'s leading open-source open data publishing platform. It provides a complete open source software solution for data publishers, and adheres to the API, data, and functionality standards of CKAN. The goal of the project is to combine the utility of CKAN with the ease of maintenance and extensibility of Drupal.

Developers: see http://drupal.org/project/dkan for code, documentation, and to contribute.

Customers: see http://getdkan.com for more information.',
          'summary' => '',
          'format' => 'html',
          'safe_value' => 'DKAN is an Open Data Portal built in Drupal.

As an Open Data Portal is designed to be feature-compliant with DKAN: <a href="http://ckan.org">http://ckan.org</a>.

The goal is to combine the utility of the DKAN API with the power of Drupal. 

Development will be done here: <a href="http://drupal.org/project/dkan">http://drupal.org/project/dkan</a>

More documentation coming soon.',
          'safe_summary' => '',
        ),
      ),
    ),
  );
}
