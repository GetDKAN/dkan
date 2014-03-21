<?php
/**
 * @file views-views-rdf-style-foaf.tpl.php
 * Default template for the Views RDF style plugin using the FOAF vocabulary
 *
 * Variables
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_rdf_render_fields
 * - $persons Array of person objects as created by template_preprocess_views_views_rdf_style_foaf
 *
 * @ingroup views_templates
 */

global $base_url;
$xml = "";
$content_type = ($options['content_type'] == 'default') ? 'application/rdf+xml' : $options['content_type'];
if (empty($header) || !$header) { //build our own header
  $xml .= '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
  $xml .= '<!-- generator="Drupal Views_Datasource.Module" -->'."\n";
  $xml .= '<rdf:RDF xmlns="http://xmlns.com/foaf/0.1"'."\n";
  $xml .= '  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
  $xml .= '  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"'."\n";
  $xml .= '  xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
  $xml .= '  xmlns:foaf="http://xmlns.com/foaf/0.1/">'."\n";
}
else {
  $xml .= "  $header\n";
}
foreach($persons as $person) {
  $xml .= "<foaf:Person>\n";
  if (array_key_exists("name", $person))              $xml .= "  <foaf:name>".$person["name"]."</foaf:name>\n";
  if (array_key_exists("firstName", $person))         $xml .= "  <foaf:firstName>".$person["firstName"]."</foaf:firstName>\n";
  if (array_key_exists("surName", $person))           $xml .= "  <foaf:surName>".$person["surName"]."</foaf:surName>\n";
  if (array_key_exists("title", $person))             $xml .= "  <foaf:title>".$person["title"]."</foaf:title>\n";
  if (array_key_exists("nick", $person))              $xml .= "  <foaf:nick>".$person["nick"]."</foaf:nick>\n";
  if (array_key_exists("mbox", $person))              $xml .= "  <foaf:mbox rdf:resource=\"mailto:".$person["mbox"]."\">".$person["mbox"]."</foaf:mbox>\n";
  if (array_key_exists("mbox_sha1sum", $person))      $xml .= "  <foaf:mbox_sha1sum>".$person["mbox_sha1sum"]."</foaf:mbox_sha1sum>\n";
  if (array_key_exists("openid", $person))            $xml .= "  <foaf:openid>".$person["openid"]."</foaf:openid>\n";
  if (array_key_exists("workplaceHomepage", $person)) $xml .= "  <foaf:workplaceHomepage rdf:resource=\"".$person["workplaceHomepage"]."\">".$person["workplacehomepage"]."</foaf:workplaceHomepage>\n";
  if (array_key_exists("homepage", $person))          $xml .= "  <foaf:homepage rdf:resource=\"".$person["homepage"]."\">".$person["homepage"]."</foaf:homepage>\n";
  if (array_key_exists("weblog", $person))            $xml .= "  <foaf:weblog rdf:resource=\"".$person["weblog"]."\">".$person["weblog"]."</foaf:weblog>\n";
  if (array_key_exists("img", $person))               $xml .= "  <foaf:img rdf:resource=\"".$person["img"]."\">".$person["img"]."</foaf:img>\n";
  if (array_key_exists("depiction", $person))         $xml .= "  <foaf:depiction>".$person["depiction"]."</foaf:depiction\n";
  if (array_key_exists("member", $person))            $xml .= "  <foaf:openid>".$person["member"]."</foaf:member>\n";
  if (array_key_exists("phone", $person))             $xml .= "  <foaf:phone>".$person["phone"]."</foaf:phone>\n";
  if (array_key_exists("jabberID", $person))          $xml .= "  <foaf:jabberID>".$person["jabberID"]."</foaf:jabberID>\n";
  if (array_key_exists("msnChatID", $person))         $xml .= "  <foaf:msnChatID>".$person["msnChatID"]."</foaf:msnChatID>\n";
  if (array_key_exists("aimChatID", $person))         $xml .= "  <foaf:aimChatID>".$person["aimChatID"]."</foaf:aimChatID>\n";
  if (array_key_exists("yahooChatID", $person))       $xml .= "  <foaf:yahooChatID>".$person["yahooChatID"]."</foaf:yahooChatID>\n";
  $xml .= "</foaf:Person>\n";
}
$xml .= "</rdf:RDF>\n";
if ($view->override_path) {       // inside live preview
  print htmlspecialchars($xml);
}
elseif ($options['using_views_api_mode']) {     // We're in Views API mode.
  print $xml;
}
else {
  drupal_add_http_header("Content-Type", "$content_type; charset=utf-8");
  print $xml;
  drupal_page_footer();
  exit;
}
