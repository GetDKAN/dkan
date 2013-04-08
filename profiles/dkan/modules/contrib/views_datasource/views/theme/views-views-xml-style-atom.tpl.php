<?php
/**
 * @file views-views-xml-style-atom.tpl.php
 * Default template for the Views XML style plugin using the Atom schema
 *
 * Variables
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields
 * - $entries Array of Atom entries as created by template_preprocess_views_views_xml_style_atom
 *
 * @ingroup views_templates
 */

global $base_url;
$content_type = ($options['content_type'] == 'default') ? 'application/atom+xml' : $options['content_type'];
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$xml .= "<!-- generator=\"Drupal Views Datasource.Module\" -->\n";
$xml .= "<feed xmlns=\"http://www.w3.org/2005/Atom\" xml:lang=\"en\">\n";
if (empty($header) || !$header) { //build our own header
  $xml .= "  <title>$title</title>\n";
  $xml .= "  <link rel=\"alternate\" type=\"text/html\" href=\"$base_url\" />\n";
  $xml .= "  <link rel =\"self\" type=\"application/atom+xml\" href=\"$link\" />\n";
  $xml .= "  <id>tag:$link</id> \n";
  $xml .= "  <updated>$updated</updated>\n";
  if ($author) {
    $xml .= "  <author>\n";
    $xml .= "    <name>" . $author["name"] . "</name>\n";
    if (array_key_exists("email", $author)) {
      $xml .= "    <email>" . $author["email"] . "</email>\n";
    }
    $xml .= "  </author>\n";
  }
}
else {
  $xml .= "  $header\n";
}

foreach($entries as $entry) {
  if (!((array_key_exists("id", $entry)) && array_key_exists("title", $entry) && array_key_exists("updated", $entry))) {
    if ($view->override_path)
      print '<b style="color:red">Either the id, title, or updated attribute is missing.</b>';
    elseif ($options['using_views_api_mode'])
      print "Either the id, title, or updated attribute is missing.";
    else drupal_set_message(t('Either the id, title, or updated attribute is missing.'), 'error');
    return;
  }
  $id = $entry["id"];
  $title = $entry["title"];
  $updated = $entry["updated"];

  if (array_key_exists("link", $entry)) {
    $link = $entry["link"];
  }
  if (array_key_exists("teaser", $entry)) {
    $teaser = $entry["teaser"];
  }
  if (array_key_exists("content", $entry)) {
    $content = $entry["content"];
  }
  $xml .= "  <entry>\n    <id>$id</id>\n    <title>" . (($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$title]]>": "$title")."</title>\n    <updated>$updated</updated>\n";  //put required elements first
  if (isset($link)) {
    $xml .= "    <link href=\"$link\"/>\n";
  }
  if (isset($teaser)) {
    $xml .= "    <teaser>".(($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$teaser]]>": "$teaser")."</teaser>\n";
  }
  if (isset($content)) {
    $xml .= "    <content>".(($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$content]]>": "$content")."</content>\n";
  }
  if (array_key_exists("author", $entry)) {
    $author_name = $entry["author"]["name"]; if (array_key_exists("email", $entry["author"])) $author_email = $entry["author"]["email"];
    $xml .= "    <author>\n      <name>$author_name</name>\n";
    if ($author_email) {
      $xml .= "      <email>$author_email</email>\n";
    }
    $xml .= "    </author>\n";
  }
  foreach ($entry as $l => $v) { //Then the rest
    if (($l != "id") && ($l != "title") && ($l != "updated") && ($l != "author") && ($l != "link") && ($l != "teaser") && ($l != "content")) {
      if (is_array($v)) {
        foreach($v as $i => $j) {
          $xml .= "    <$l>\n".(($options['escape_as_CDATA'] == 'yes') ? "          <![CDATA[$j]]>\n": "      $j")."\n    </$l>\n";
        }
      }
      else  $xml .= "    <$l>".(($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$v]]>": "$v")."</$l>\n";
    }
  }
  $xml .= "  </entry>\n";
}
$xml .= "</feed>\n";

if ($view->override_path) {       // inside live preview
  print htmlspecialchars($xml);
}
else if ($options['using_views_api_mode']) {     // We're in Views API mode.
  print $xml;
}
else {
  drupal_add_http_header("Content-Type", "$content_type; charset=utf-8");
  print $xml;
  exit;
}
