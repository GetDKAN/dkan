<?php
/**
 * @file views-views-xml-style-raw.tpl.php
 * Default template for the Views XML style plugin using the raw schema
 *
 * Variables
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields
 *
 * @ingroup views_templates
 * @see views_views_xml_style.theme.inc
 */
  if (isset($view->override_path)) {       // inside live preview
    print htmlspecialchars($xml);
  }
  elseif ($options['using_views_api_mode']) {     // We're in Views API mode.
    print $xml;
  }
  else {
    drupal_add_http_header("Content-Type", "$content_type; charset=utf-8");
    print $xml;
    exit;
  }