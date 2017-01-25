<?php

/**
 * @file
 * Blocks for the core version of the DKAN site.
 */

/**
 * Implements hook_block_info().
 */
function dkan_sitewide_block_info() {
  $blocks['dkan_sitewide_license'] = array(
    'info' => t('License'),
    'cache' => DRUPAL_CACHE_PER_PAGE,
  );
  $blocks['dkan_sitewide_social'] = array(
    'info' => t('Social'),
    'cache' => DRUPAL_CACHE_PER_PAGE,
  );
  $blocks['dkan_sitewide_resource_add'] = array(
    'info' => t('Additional Information'),
    'cache' => DRUPAL_NO_CACHE,
  );
  $blocks['dkan_sitewide_footer_author'] = array(
    'info' => t('Powered by DKAN'),
    'cache' => DRUPAL_CACHE_GLOBAL,
  );
  $blocks['dkan_sitewide_search_bar'] = array(
    'info' => t('Search'),
    'cache' => DRUPAL_CACHE_GLOBAL,
  );
  $blocks['dkan_sitewide_other_access'] = array(
    'info' => t('Other Access'),
    'cache' => DRUPAL_CACHE_PER_PAGE,
  );
  $blocks['dkan_sitewide_user_menu'] = array(
    'info' => t('User menu'),
    'cache' => DRUPAL_CACHE_GLOBAL,
  );
  $blocks['dkan_sitewide_data_extent'] = array(
    'info' => t('Data Extent'),
    'cache' => DRUPAL_CACHE_PER_PAGE,
  );
  $blocks['dkan_search_front'] = array(
    'info' => t('Search Your Data'),
    'cache' => DRUPAL_NO_CACHE,
  );
  $blocks['dkan_demo_front'] = array(
    'info' => t('Welcome to the DKAN Demo'),
    'cache' => DRUPAL_NO_CACHE,
  );
  $blocks['dkan_add_front'] = array(
    'info' => t('Add a Dataset'),
    'cache' => DRUPAL_NO_CACHE,
  );

  return $blocks;
}

/**
 * Implements hook_block_view().
 */
function dkan_sitewide_block_view($delta = '') {
  $block = array();

  switch ($delta) {
    case 'dkan_sitewide_data_extent':
      $block['subject'] = t('Data Extent');
      $block['content'] = dkan_sitewide_data_extent_block();
      break;

    case 'dkan_sitewide_license':
      $block['subject'] = t('License');
      $block['content'] = dkan_sitewide_license_block();
      break;

    case 'dkan_sitewide_social':
      $block['subject'] = t('Social');
      $block['content'] = dkan_sitewide_social_block();
      break;

    case 'dkan_sitewide_footer_author':
      $block['subject'] = '';
      $block['content'] = dkan_sitewide_footer_author();
      break;

    case 'dkan_sitewide_resource_add':
      $block['subject'] = t('Additional Information');
      $block['content'] = dkan_sitewide_resource_additional_block();
      break;

    case 'dkan_sitewide_search_bar':
      $block['subject'] = '';
      $block['content'] = dkan_sitewide_search_bar();
      break;

    case 'dkan_sitewide_other_access':
      $block['subject'] = t('Other Access');
      $block['content'] = dkan_sitewide_other_access();
      break;

    case 'dkan_sitewide_user_menu':
      $block['subject'] = '';
      $block['content'] = dkan_sitewide_user_menu_block();
      break;

    case 'dkan_search_front':
      $block['subject'] = t('Search Your Data');
      $block['content'] = dkan_sitewide_search_front_block();
      break;

    case 'dkan_demo_front':
      $block['subject'] = t('Welcome to the DKAN Demo');
      $default = dkan_demo_front_block();
      $block['content'] = check_markup(variable_get('dkan_sitewide_dkan_demo_front_text', $default), variable_get('dkan_sitewide_dkan_demo_front_format', 'html'));
      break;

    case 'dkan_add_front':
      $block['subject'] = t('Add a Dataset');
      $default = dkan_add_front_block();
      $block['content'] = check_markup(variable_get('dkan_sitewide_dkan_add_front_text', $default), variable_get('dkan_sitewide_dkan_add_front_format', 'html'));
      break;

  }
  return $block;
}

/**
 * Creates "Additional" block for resources.
 *
 * TODO: Make this a faux field as well attached to resource node.
 */
function dkan_sitewide_resource_additional_block($nid = '') {
  $node = $nid ? node_load($nid) : menu_get_object();
  if ($node) {
    $file_info = array();
    $lang = $node->language ? $node->language : 'und';
    $link = isset($node->field_link_remote_file[$lang][0]) ? $node->field_link_remote_file[$lang][0] : NULL;
    $link = !$link && isset($node->field_link_remote_file['und'][0]) ? $node->field_link_remote_file['und'][0] : $link;
    $upload = isset($node->field_upload[$lang][0]) ? $node->field_upload[$lang][0] : NULL;
    $upload = !$upload && isset($node->field_upload['und'][0]) ? $node->field_upload['und'][0] : $upload;

    if (($file = $link) || ($file = $upload)) {
      $file_info[] = array(t('mimetype'), $file['filemime']);
      $file_info[] = array(t('filesize'), format_size($file['filesize']));
      $file_info[] = array(t('resource type'), 'file upload');
      $file_info[] = array(t('timestamp'), date('M d, Y', $file['timestamp']));
      return theme('table',
        array('rows' => $file_info, 'header' => array(t('Field'), t('Value'))));
    }
  }
}

/**
 * Provides Data Extent block.
 */
function dkan_sitewide_data_extent_block() {
  $node = menu_get_object();
  if ($node && isset($node->type) && $node->type == 'dataset') {
    $wrapper = entity_metadata_wrapper('node', $node);
    $spatial = $wrapper->field_spatial->value();
    if ($spatial && $spatial['wkt']) {
      drupal_add_library('leaflet_widget', 'leaflet_core');
      $features = array(leaflet_widget_geojson_feature($spatial['wkt']));
      $geojson = drupal_json_encode(leaflet_widget_geojson_feature_collection($features));
      $output = "var dataExtent = " . $geojson;
      $output .= "; var map = L.map('map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a>'
        }).addTo(map);

        var geojson = L.geoJson(dataExtent).addTo(map);
        map.fitBounds(geojson.getBounds());";
      drupal_add_js($output, array(
        'type' => 'inline',
        'scope' => 'footer',
        'weight' => 5,
      ));
      return '<div id="map" style="height:250px; width:100%"></div>';
    }
  }
  else {
    return FALSE;
  }
}

/**
 * Implements hook_block_configure().
 */
function dkan_sitewide_block_configure($delta = '') {
  $form = array();
  if ($delta == 'dkan_demo_front') {
    $form['dkan_sitewide_dkan_demo_front_text'] = array(
      '#type' => 'text_format',
      '#title' => t('Default'),
      '#description' => t('Add text here to override the default block content. Leave blank to continue using default.'),
      '#default_value' => variable_get('dkan_sitewide_dkan_demo_front_text', ''),
      '#format' => variable_get('dkan_sitewide_dkan_demo_front_format', 'html'),
    );
  }
  if ($delta == 'dkan_add_front') {
    $form['dkan_sitewide_dkan_add_front_text'] = array(
      '#type' => 'text_format',
      '#title' => t('Add a dataset'),
      '#description' => t('Add text here to override the default block content. Leave blank to continue using default.'),
      '#default_value' => variable_get('dkan_sitewide_dkan_add_front_text', ''),
      '#format' => variable_get('dkan_sitewide_dkan_add_front_format', 'html'),
    );
  }

  return $form;
}

/**
 * Implements hook_block_save().
 */
function dkan_sitewide_block_save($delta = '', $edit = array()) {
  if ($delta == 'dkan_demo_front') {
    if (empty($edit['dkan_sitewide_dkan_demo_front_text']['value'])) {
      variable_del('dkan_sitewide_dkan_demo_front_text');
      drupal_set_message(t('Reset block content to default.'));
    }
    else {
      variable_set('dkan_sitewide_dkan_demo_front_text', $edit['dkan_sitewide_dkan_demo_front_text']['value']);
      variable_set('dkan_sitewide_dkan_demo_front_format', $edit['dkan_sitewide_dkan_demo_front_text']['format']);
    }
  }
  if ($delta == 'dkan_add_front') {
    if (empty($edit['dkan_sitewide_dkan_add_front_text']['value'])) {
      variable_del('dkan_sitewide_dkan_add_front_text');
      drupal_set_message(t('Reset block content to default.'));
    }
    else {
      variable_set('dkan_sitewide_dkan_add_front_text', $edit['dkan_sitewide_dkan_add_front_text']['value']);
      variable_set('dkan_sitewide_demo_front_dkan_add_front_format', $edit['dkan_sitewide_demo_front_dkan_add_front_text']['format']);
    }
  }
}

/**
 * Add license info to block for dataset node.
 */
function dkan_sitewide_license_block() {
  $node = menu_get_object();
  if (!isset($node->type) || $node->type != 'dataset') {
    return '';
  }
  else {
    // Sometimes the node object returned from the menu_get_object will not be
    // fully standard with all fields information. This is usually due to the
    // alteration done by the panels page_manager. A potential workaround is to
    // re-load the actual node with node_load.
    $node_emw = entity_metadata_wrapper('node', node_load($node->nid, $node->vid));
    if (isset($node_emw->field_license)) {
      $key = $node_emw->field_license->value();
      $subscribed_values = dkan_dataset_content_types_license_subscribed_values();
      if (isset($subscribed_values[$key])) {
        $license = $subscribed_values[$key];
        if (isset($license['uri'])) {
          $output = l($license['label'], $license['uri']) . '<br/>';
          $output .= l(
            '<img class="open-data" src="https://assets.okfn.org/images/ok_buttons/od_80x15_blue.png" alt="[Open Data]">',
            $license['uri'],
            array('html' => TRUE)
          );
        }
        else {
          $output = $license['label'];
        }
        return $output;
      }
      elseif ($key) {
        // Harvested datasets may use license urls that do not match
        // the urls set up in hook_subscribed_values().
        // Check if the value is a url, if so theme it as a link.
        $url = parse_url($key);
        if (isset($url['scheme']) && isset($url['host'])) {
          $output = l($key, $key);
          return $output;
        }
        else {
          return $key;
        }
      }
    }
    return t('License not specified');
  }
}

/**
 * Adds Powered by.
 */
function dkan_sitewide_footer_author() {
  return t(
    "Powered by !dkan, a project of !nucivic",
    array(
      '!dkan' => l(t('DKAN'), 'http://nucivic.com/dkan'),
      '!nucivic' => l(t('NuCivic'), 'http://nucivic.com'),
    )
  );
}

/**
 * Adds social links.
 */
function dkan_sitewide_social_block() {
  $path = isset($_GET['q']) ? $_GET['q'] : '<front>';
  $link = url($path, array('absolute' => TRUE));
  $title = drupal_get_title();

  $output = array(
    '#theme' => 'item_list',
    '#items' => array(
      'twitter' => array(
        'data' => l('<i class="ckan-icon ckan-icon-twitter fa fa-lg fa-twitter-square"></i> ' . t('Twitter'),
          'https://twitter.com/share', array(
            'query' => array(
              'url' => $link,
              'text' => $title,
            ),
            'attributes' => array(
              'target' => '_blank',
            ),
            'html' => TRUE,
          )),
        'class' => array('nav-item'),
      ),
      'linkedin' => array(
        'data' => l('<i class="ckan-icon ckan-icon-fb fa fa-lg fa-linkedin-square"></i> ' . t('LinkedIn'),
          'https://www.linkedin.com/shareArticle', array(
            'query' => array(
              'url' => $link,
              'title' => $title,
            ),
            'attributes' => array(
              'target' => '_blank',
            ),
            'html' => TRUE,
          )),
        'class' => array('nav-item'),
      ),
      'reddit' => array(
        'data' => l('<i class="ckan-icon ckan-icon-fb fa fa-lg fa-reddit-square"></i> ' . t('Reddit'),
          'http://www.reddit.com/submit', array(
            'query' => array(
              'url' => $link,
              'title' => $title,
            ),
            'attributes' => array(
              'target' => '_blank',
            ),
            'html' => TRUE,
          )),
        'class' => array('nav-item'),
      ),
      'googleplus' => array(
        'data' => l('<i class="ckan-icon ckan-icon-gplus fa fa-lg fa-google-plus-square"></i> ' . t('Google+'),
          'https://plus.google.com/share', array(
            'query' => array(
              'url' => $link,
              'title' => $title,
            ),
            'attributes' => array(
              'target' => '_blank',
            ),
            'html' => TRUE,
          )),
        'class' => array('nav-item'),
      ),
      'facebook' => array(
        'data' => l('<i class="ckan-icon ckan-icon-fb fa fa-lg fa-facebook-square"></i> ' . t('Facebook'),
          'https://www.facebook.com/sharer.php', array(
            'query' => array(
              'u' => $link,
            ),
            'attributes' => array(
              'target' => '_blank',
            ),
            'html' => TRUE,
          )),
        'class' => array('nav-item'),
      ),
    ),
    '#attributes' => array(
      'class' => array('nav', 'nav-simple', 'social-links'),
    ),
  );

  return $output;
}

/**
 * Creates "Other Access" block.
 */
function dkan_sitewide_other_access() {
  $node = menu_get_object();
  if (!empty($node->nid)) {
    $alias = drupal_get_path_alias('node/' . $node->nid);
    $alias = $alias . '.xml';
    $output = array(
      'formats' => array(
        '#type' => 'markup',
        '#markup' => t('The information on this page (the dataset metadata) is also available in these formats.'),
      ),
      'buttons' => array(
        '#prefix' => '<p class="btn-group">',
        '#suffix' => '</p>',
        'children' => array(
          'json' => array(
            '#type' => 'markup',
            '#markup' => l('<i class="ckan-icon ckan-icon-json"></i> JSON',
              url('api/3/action/package_show', array('query' => array('id' => $node->uuid), 'absolute' => TRUE)),
              array(
                'html' => TRUE,
                'attributes' => array(
                  'class' => array('btn'),
                  'title' => t('json view of content'),
                ),
              )),
          ),
          'dcat' => array(
            '#type' => 'markup',
            '#markup' => l('<i class="ckan-icon ckan-icon-rdfxml"></i> RDF',
              url($alias, array('absolute' => TRUE)),
              array(
                'html' => TRUE,
                'attributes' => array(
                  'class' => array('btn'),
                  'title' => t('rdf view of content'),
                ),
              )),
          ),
        ),
      ),
      'dkan_api' => array(
        '#type' => 'markup',
        '#markup' => '<p><small>' . t('via the  <a href="http://docs.getdkan.com">DKAN API</a>') . '</small></p>',
      ),
    );

    return $output;
  }
}

/**
 * Creates search block for front page.
 */
function dkan_sitewide_search_front_block() {
  return dkan_sitewide_search_bar();
}

/**
 * Creates search bar.
 */
function dkan_sitewide_search_bar() {
  if (module_exists('dkan_sitewide_search_db')) {
    $output = drupal_get_form('dkan_sitewide_dataset_search_form');
    return drupal_render($output);
  }
}

/**
 * Form for sitewide search.
 */
function dkan_sitewide_dataset_search_form($form, $form_state) {
  $form['search'] = array(
    '#type' => 'textfield',
    // Add field label for 508 compliance.
    '#title' => 'Search',
    '#attributes' => array('placeholder' => t('search')),
    '#size' => 30,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    // '#value' => t('Apply'),
    // Use fontawesome icon instead.
    '#value' => decode_entities('&#xf002;'),
  );
  return $form;
}

/**
 * Redirects to search page.
 */
function dkan_sitewide_dataset_search_form_submit($form, &$form_state) {
  if ($query = $form_state['input']['search']) {
    drupal_goto('dataset', array('query' => array('query' => $query)));

  }
}

/**
 * Creates placeholder front block.
 */
function dkan_demo_front_block() {
  $dataset_url = url('dataset');
  $homepage_graphic = url(drupal_get_path('theme', 'nuboot_radix') . '/assets/images/homepage-graphic.jpg');
  $homepage_dataset_url = url('dataset/gold-prices-london-1950-2008-monthly');
  $content = t('<p>Search for datasets directly from the homepage or by navigating to the !link search page where you can facet by tags, groups and format.</p>', array('!link' => l(t('Datasets'), 'dataset')));
  $content .= '<p class="featured media-overlay"><strong class="media-heading">DKANs data previewing tool has many powerful features</strong><a class="image" href=" ' . $homepage_dataset_url . ' "><img src="' . $homepage_graphic . '" style="width: 100%" alt="Image of DKANs data previewing tool"></a>
    </p>';
  return $content;
}

/**
 * Creates "Add a Dataset" block for front page.
 */
function dkan_add_front_block() {
  $output = '<div class="add content">';
  $output .= t('You can add a !dataset to get a sense of publisher workflow. This form can be customised to require many additional fields. In this demo only the most basic fields are required.', array('!dataset' => l(t('Dataset'), 'node/add/dataset')));
  $output .= '</div>';
  return $output;
}