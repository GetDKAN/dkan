<?php

/**
 * Checks incoming AJAX requests for Search API pages paths
 */
function search_api_ajax_proxy($id, $keys = NULL) {
  $page = search_api_page_load((int)$id);
  if (!$page) {
    return MENU_NOT_FOUND;
  }

  $return = menu_execute_active_handler($page->path . '/' . $keys, FALSE);
  search_api_ajax_return_json($return);
}

/**
 * Checks incoming AJAX requests for Search API views paths
 */
function search_api_ajax_proxy_views($name, $display, $keys = NULL) {
  $view = views_get_view($name);
  if (!$view) {
    return MENU_NOT_FOUND;
  }

  // ?query is passed on through $.get in
  // search_api_ajax.js::Drupal.search_api_ajax.request_callback
  $path = str_replace('search_api_ajax/', '', $_GET['q']);
  $return = menu_execute_active_handler($path, FALSE);
  search_api_ajax_return_json($return);
}

/**
 * Performs a search request and returns page & block content html as JSON
 *
 * If the incoming request is not an AJAX request, user/bot is redirected
 * to the non-Ajax URL.
 *
 * @see search_api_ajax_modules()
 */
function search_api_ajax_return_json($return) {

  // check request source type: non-ajax is redirect back to non-ajax URL
  if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    $params = drupal_get_query_parameters();
    $path = module_exists('facetapi_pretty_paths') ? ltrim(parse_url(request_uri(), PHP_URL_PATH), '/') : $_GET['q'];
    $real_path = str_replace('search_api_ajax/', '', $path);
    drupal_goto($real_path, array('query' => $params), 301);
    exit();
  }

  if (is_int($return)) {
    switch ($return) {
      case MENU_NOT_FOUND :
        drupal_add_http_header('Status', '404 Not Found');
        break;
      case MENU_ACCESS_DENIED :
        drupal_add_http_header('Status', '403 Forbidden');
        break;
      case MENU_SITE_OFFLINE :
        drupal_add_http_header('Status', '503 Service unavailable');
        break;
    }
  }
  elseif (isset($return)) {
    global $theme;
    if (!isset($theme)) {
      init_theme();
    }

    // render JSON (views are already rendered)
    if (is_array($return)) {
      $return = drupal_render($return);
    }
    $json = array('regions' => array('search_api_ajax' => $return));

    // add new blocks
    $blocks = array();

    // Drupal blocks
    // @todo cache this?
    $regions = system_region_list($theme);
    foreach ($regions as $region_key => $region_name) {
      $settings['regions'][] = $region_key;
      $block_list = block_list($region_key);

      // remove inactive blocks (status=0)
      foreach ($block_list as $key => $block) {
        if ($block->status == 0) {
          unset($block_list[$key]);
        }
      }
      $blocks = array_merge(block_list($region_key), $blocks);
      if (module_exists('context')) {
        $blocks = array_merge(context_get_plugin('reaction', 'block')->block_list($region_key), $blocks);
      }
    }

    $modules = search_api_ajax_modules();
    foreach ($blocks as $block) {
      if (in_array($block->module, $modules)) {
        $rendered_block = _block_render_blocks(array($block));
        $rendered_block = _block_get_renderable_array($rendered_block);
        $json['regions'][$block->region][$block->module . '_' . $block->delta] = drupal_render($rendered_block);

        // new blocks may have come into existence, we notify about them
        // otherwise they would never be displayed to the user
        // @note that we have to strtolower the final $block->delta (!!)
        // @see search_api_ajax.js
        $json['blocks'][$block->module . '_' . $block->delta] = str_replace('_', '-', '#block-' . $block->module . '-' . strtolower($block->delta));
      }
    }

    // merge all JS scopes and settings
    // copied from core ajax.inc
    $scripts = drupal_add_js();
    if (!empty($scripts['settings'])) {
      $settings = $scripts['settings'];
      $json['settings'] = call_user_func_array('array_merge_recursive', $settings['data']);
    }

    $json['debug'] = search_api_ajax_debug();

    drupal_add_http_header('Content-Type', 'application/json; charset=utf-8');
    print drupal_json_encode($json);
  }
}
