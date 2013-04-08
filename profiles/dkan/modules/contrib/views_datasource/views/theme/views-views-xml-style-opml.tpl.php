<?php
/**
 * @file views-views-xml-style-opml.tpl.php
 * Default template for the Views XML style plugin using the OPML schema
 *
 * Variables:
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields
 * - $outlines Array of outline arrays as created by template_preprocess_views_views_xml_style_opml
 *
 * @ingroup views_templates
 */
  $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  $xml .= '<!-- generator="Drupal Views Datasource.Module" -->'."\n";
  $xml .= "<opml version =\"2.0\">\n";
  if (empty($header) || !$header) { //build our own header
    $xml .= "  <head>\n";
    $xml .= "    <title>$title</title>\n";
    $xml .= "    <ownerName>$ownerName</ownerName>\n";
    $xml .= "    <ownerEmail>$ownerEmail</ownerEmail>\n";
    $xml .= "    <docs>$docs</docs>\n";
    $xml .= "    <dateCreated>$dateCreated</dateCreated>\n";
    $xml .= "  </head>\n";
  }
  else {
    $xml .= "  <head>\n";
    $xml .= "   $header\n";
    $xml .= "  </head>\n";
  }
  $xml .="    <body>\n";
  foreach($outlines as $outline) {
    if (!array_key_exists("text", $outline)) {
      if ($view->override_path)
        print '<b style="color:red">The text field of an outline must be present in an OPML document.</b>';
      elseif ($options['using_views_api_mode'])
        print "The text field of an outline must be present in an OPML document.";
      else drupal_set_message(t('The text field of an outline must be present in an OPML document.'), 'error');
      return;
    }
    $xml .= "      <outline";
    foreach($outline as $n => $v) {
      if (is_array($v)) {
        foreach ($v as $i => $j) $xml .= " $n$i = \"$j\"";
      }
      else $xml .= " $n = \"$v\"";
    }
    $xml .= "/>\n";
  }
  $xml .= '    </body>'."\n";
  $xml .= '</opml>'."\n";
  if ($view->override_path) {       // inside live preview
    print htmlspecialchars($xml);
  }
  elseif ($options['using_views_api_mode']) {     // We're in Views API mode.
    print $xml;
  }
  else {
    $content_type = $options["content_type"];
    drupal_add_http_header("Content-Type", "$content_type; charset=utf-8");
    print $xml;
    exit;
  }
