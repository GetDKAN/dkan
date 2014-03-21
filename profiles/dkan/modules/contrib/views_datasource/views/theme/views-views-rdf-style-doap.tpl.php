<?php
/**
 * @file views-views-rdf-style-doap.tpl.php
 * Default template for the Views RDF style plugin using the DOAP vocabulary
 *
 * Variables
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_rdf_render_fields
 * - $projects Array of project objects as created by template_preprocess_views_views_rdf_style_doap
 *
 * @ingroup views_templates
 */
global $base_url;
$content_type = ($options['content_type'] == 'default') ? 'application/rdfxml' : $options['content_type'];
if (!$header) { //build our own header
  $xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .= '<rdf:RDF xmlns="http://usefulinc.com/ns/doap#"'."\n";
  $xml .= '  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
  $xml .= '  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"'."\n";
  $xml .= '  xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
  $xml .= '  xmlns:foaf="http://xmlns.com/foaf/0.1/"'."\n";
  $xml .= '  xmlns:doap="http://usefulinc.com/ns/doap#">'."\n";
}
else {
  $xml .= "  $header\n";
}
foreach($projects as $nid => $project) {
  $xml .= "<doap:Project rdf:about=\"".$project["homepage"]."\">\n";
  if (array_key_exists("homepage", $project)) {
    $xml .= "  <doap:homepage rdf:resource=\"".$project["homepage"]."\"/>\n";
  }
  if (array_key_exists("name", $project)) {
    $xml .= "  <doap:name>".$project["name"]."</doap:name>\n";
  }
  if (array_key_exists("shortdesc", $project)) {
    $xml .= "  <doap:shortdesc>".$project["shortdesc"]."</doap:shortdesc>\n";
  }
  if (array_key_exists("license", $project)) {
    $xml .= "  <doap:license rdf:resource=\"http://usefulinc.com/doap/licenses/".$project["license"]."\"/>\n";
  }
  if (array_key_exists("language", $project)) {
    $xml .= "  <programming-language>".$project["language"]."</programming-language>\n";
  }
  foreach ($project["repositories"] as $repository) {
    $xml .= "  <repository><Repository><location rdf:resource=\"";
    $xml .= $repository."\"/></Repository></repository>\n";
  }
  foreach ($project["developers"] as $developer) {
    $xml .= "  <developer><foaf:Person><foaf:name>".$developer;
    $xml .= "</foaf:name></foaf:Person></developer>\n";
  }
  $xml .= "</doap:Project>\n";
}
$xml .= "</rdf:RDF>\n";
if ($view->override_path) {       // inside live preview
  print htmlspecialchars($xml);
}
else if ($options['using_views_api_mode']) {     // We're in Views API mode.
  print $xml;
}
else {
  drupal_add_http_header("Content-Type", "$content_type; charset=utf-8");
  print $xml;
  drupal_page_footer();
  exit;
}
