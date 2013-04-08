<?php
/**
 * @file
 * View template to render view fields as XML.
 *
 * - $view: The view in use.
 * - $rows: Array of row objects as rendered by _views_json_render_fields
 * - $attachment: not used currently
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_xml.views.inc
 */

function template_preprocess_views_views_xml_style_raw(&$vars) {
  $view = $vars["view"];
  $rows = $vars["rows"];
  $options = $vars["options"];
  $base = $view->base_table;
  $root = $options['root_element'];
  $endroot = preg_replace("/\s+.*/", "", $root);
  $top_child_object = $options["top_child_object"];
  $end_top_child_object = preg_replace("/\s+.*/", "", $top_child_object);
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'text/xml' : $options['content_type'];
  $header = $options['header'];
  $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  if ($header) {
    $xml .= $header . "\n";
  }
  $xml .= "<$root>\n";
  foreach ($rows as $row) {
    $xml .= ($options['element_output'] == 'nested') ? "  <$top_child_object>\n": "  <$top_child_object\n";
    foreach ($row as $id => $object) {
      if ($options['field_output'] == 'normal') {
        if ($object->label)
          $label = _views_xml_strip_illegal_xml_name_chars(check_plain(html_entity_decode(strip_tags($object->label))));
        else  $label = _views_xml_strip_illegal_xml_name_chars(check_plain(html_entity_decode((strip_tags($id)))));
        if (!$object->is_multiple)
          $content = ($plaintext_output ? check_plain(html_entity_decode(strip_tags($object->content))) : _views_xml_xmlEntities($object->content));
        else {
          $content = array();
          foreach ($object->content as $n => $oc) {
            $content[$n] = ($plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : _views_xml_xmlEntities($oc));
          }
        }
      }
      elseif ($options['field_output'] == 'raw') {
        $label = _views_xml_strip_illegal_xml_name_chars(check_plain(html_entity_decode(strip_tags($id))));
        if (!$object->is_multiple)
          $content = ($plaintext_output ? check_plain(html_entity_decode(strip_tags($object->raw))) : _views_xml_xmlEntities($object->raw));
        else {
          foreach ($object->raw as $n => $oc) $content[$n] = ($plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : _views_xml_xmlEntities($oc));
        }
      }
      $endlabel = preg_replace("/\s+.*/", "", $label);
      if ($options['element_output'] == 'nested') {
        if (!is_array($content)) {
          $xml .= "    <$label>" . (($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$content]]>" : $content) . "</$endlabel>\n";
          //_views_xml_debug_stop($xml);
        }
        else {
          foreach ($content as $c) {
            $xml .= "    <$label>";
            $xml .= "" . (($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$c]]>" : $c . "");
            $xml .= "</$endlabel>\n";
          }
        }
      }
      elseif ($options['element_output'] == 'attributes') {
        if (!is_array($content)) {
          $content = _views_xml_strip_illegal_xml_attribute_value_chars($content);
          $xml .= " $label=\"$content\" ";
        }
        else {
          foreach ($content as $n => $c) {
            $c = _views_xml_strip_illegal_xml_attribute_value_chars($c);
            $label = _views_xml_strip_illegal_xml_name_chars($label);
            $xml .= " $label$n=\"$c\" ";
          }
        }
      }
    }

    $xml .= ($options['element_output'] == 'nested') ? "  </$end_top_child_object>\n": "/>\n";
  }
  $xml .= "</$endroot>\n";
  $vars["xml"] = $xml;
}

function template_preprocess_views_views_xml_style_opml(&$vars) {
  $view = $vars["view"];
  $rows = $vars["rows"];
  $options = $vars["options"];
  $base = $view->base_table;
  $top_child_object = $options["top_child_object"];
  $root = "opml";
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'text/html' : $options['content_type'];
  $header = $options["header"];
  $author = _views_xml_format_author($options["author"]);
  if (empty($header) || !$header) {
    $vars["title"] = ($view->get_title() ? $view->get_title(): $view->name);
    $vars["dateCreated"] = format_date(time(), 'custom', DATE_RFC822);
    $vars["ownerName"] = $author["name"];
    $vars["ownerEmail"] = $author["email"];
    $vars["ownerId"] = $author["uri"];
    $vars["docs"] = url($view->get_url(), array('absolute' => TRUE));
  }
  else {
    $vars["header"] = $header;
  }
  $outlines = array();
  foreach ($rows as $row) {
    $outline = array();
    foreach ($row as $field) {
      if ($options["field_output"] == "normal") {
        if ($field->label)
          $label = _views_xml_strip_illegal_xml_name_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($field->label))) : $field->label);
        else $label = _views_xml_strip_illegal_xml_name_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id);
        if (!$field->is_multiple)
          $content = _views_xml_strip_illegal_xml_attribute_value_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content);
        else {
          $content = array();
          foreach ($field->content as $n => $oc) $content[$n] = _views_xml_strip_illegal_xml_attribute_value_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc);
        }
      }
      elseif ($options["field_output"] == "raw") {
        $label = _views_xml_strip_illegal_xml_name_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id);
        if (!$field->is_multiple)
          $content = _views_xml_strip_illegal_xml_attribute_value_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw);
        else {
          $content = array();
          foreach ($field->raw as $n => $oc) $content[$n] = _views_xml_strip_illegal_xml_attribute_value_chars($plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc);
        }
      }

      /* OPML text attribute */
      if (!array_key_exists("text", $outline)) {
        if (drupal_strtolower($label) == "text") {
          $outline["text"] = $content;
          continue;
        }
        elseif (drupal_strtolower($label) == "body") {
          $outline["text"] = $content;
          continue;
        }
        elseif ((drupal_strtolower($label) == "node_revisions_body")) {
          $outline["text"] = $content;
          continue;
        }
      }

      /* OPML type attribute */
      if (!array_key_exists("type", $outline)) {
        if (drupal_strtolower($label) == "type") {
          $outline["type"] = $content;
          continue;
        }
        elseif (drupal_strtolower($label) == "node_type") {
          $outline["type"] = $content;
          continue;
        }
      }

      /* OPML isComment attribute */
      if (!array_key_exists("isComment", $outline)) {
        if (drupal_strtolower($label) == "iscomment") {
          $outline["isComment"] = $content;
          continue;
        }
      }

      /* OPML isBreakpoint attribute */
      if (!array_key_exists("isBreakpoint", $outline)) {
        if (drupal_strtolower($label) == "isbreakpoint") {
          $outline["isBreakpoint"] = $content;
          continue;
        }
      }

      /* OPML created attribute */
      if (!array_key_exists("created", $outline)) {
        $value = NULL;
        if (drupal_strtolower($label) == "created") {
          $value = $content;
        }
        elseif (drupal_strtolower($label) == "published") {
          $value = $content;
        }
        elseif (drupal_strtolower($label) == "node_created") {
          $value = $content;
        }
        elseif (drupal_strtolower($label) == "postdate") {
          $value = $content;
        }
//        if ($value) {
//          $value = $field->raw;
//          if (intval($value))  // timestamp
//            $value = format_date(intval($value), 'custom', DATE_RFC822);
//          elseif (getdate($value))      // string date
//            $value = format_date(strtotime($value), 'custom', DATE_RFC822);
//          //otherwise just pass the string as is
//          $outline["created"] = $value;
//          continue;
//        }
        if ($value) $outline["created"] = $value;
        //continue;

      }
      //Otherwise just use the $label and $content as attribute nam and value
      $outline[$label] = $content;
    }
    $outlines[] = $outline;
  }
  //_views_xml_debug_stop($outlines);
  $vars["outlines"] = $outlines;
}

/**
 * Template preprocess for the Atom format
 * @param $vars
 * @return unknown_type
 */
function template_preprocess_views_views_xml_style_atom(&$vars) {
  global $base_url;
  $view = $vars["view"];
  $rows = $vars["rows"];
  $options = $vars["options"];
  $link = $view->display_handler->get_path();
  $url_options = array('absolute' => TRUE);
  if (!empty($view->exposed_raw_input)) {
    $url_options['query'] = $view->exposed_raw_input;
  }
  $base = $view->base_table;
  $root = "feed";
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'application/atom+xml' : $options['content_type'];
  $header  = $options["header"];
  if (!$header) {
    $vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
    $vars["id"] = check_url($view->get_url());
    $vars["updated"] = format_date(time(), 'custom', DATE_ATOM);
    if ($options["author"]) $vars["author"] = _views_xml_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  }
  else {
    $vars["header"] = $header;
  }
  $entries = array();
  foreach ($rows as $row) {
    $entry = array();
    foreach ($row as $field) {
      if ($options["field_output"] == "normal") {
        if ($field->label)
          $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->label))) : $field->label;
        else  $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        if (!$field->is_multiple)
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content;
        else {
          $content = array();
          foreach ($field->content as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
        }
      }
      elseif ($options["field_output"] == "raw") {
        $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        if (!$field->is_multiple)
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw;
        else {
          $content = array();
          foreach ($field->raw as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
        }
      }
      $label = _views_xml_strip_illegal_xml_name_chars(html_entity_decode(check_plain(strip_tags($label))));
      //if (($options["skip_empty_fields"] == TRUE) && (is_null($content) || $content === "")) continue;

      /* Atom id attribute */
      if (!array_key_exists("id", $entry)) {
        if (drupal_strtolower($label) == "id") {
          $entry["id"] = $content;
          continue;
        }
      }

      /* Atom title attribute */
      if (!array_key_exists("title", $entry)) {
        if (drupal_strtolower($label) == "title") {
          $entry["title"] = $content;
          continue;
        }
      }

      /* Atom updated attribute */
      if (!array_key_exists("updated", $entry)) {
        if (drupal_strtolower($label) == "updated") {
          $value = $content;
        }
      }

      /* Atom author attribute */
      if (!array_key_exists("author", $entry)) {
        if (drupal_strtolower($label) == "author") {
          $entry["author"] = _views_xml_format_author($content);
          continue;
        }
        elseif (drupal_strtolower($label) == "uid") {
          $entry["author"] = _views_xml_format_author($content);
          continue;
        }
      }

      /* Atom link attribute */
      if (!array_key_exists("link", $entry)) {
        if (drupal_strtolower($label) == "link") {
          $entry["link"] = $content;
          continue;
        }
      }

      /* Atom content attribute */
      if (!array_key_exists("content", $entry)) {
        if (drupal_strtolower($label) == "content") {
          $entry["content"] = $content;
          continue;
        }
      }

      /* Atom summary attribute */
      if (!array_key_exists("summary", $entry)) {
        if (drupal_strtolower($label) == "summary") {
          $entry["summary"] = $content;
          continue;
        }
      }

      /* Otherwise just use the $label and $content as element name and value */
      $entry[strtolower($label)] = $content;
    } //for

    /* Check if id attribute exists and if not fall back to nid and try to use that */
    if (!array_key_exists("id", $entry)) {
      if (array_key_exists("nid", $entry)) {
        $nid = $entry["nid"];
        $entry["id"] = "tag:$base_url,node-$nid";
      }
    }

    /* Check if title attribute exists and if not fall back to node_title */
    if (!array_key_exists("title", $entry)) {
      if (array_key_exists("node_title", $entry)) {
        $entry["title"] = $entry["node_title"];
      }
    }

    /* Check if link attribute exists and if not fall back to constructing a link from the nid */
    if (!array_key_exists("link", $entry)) {
      if (array_key_exists("nid", $entry)) {
        $nid = $entry["nid"];
        $entry["link"] = url("node/$nid", array('absolute' => TRUE));
      }
    }

    /* Check if updated attribute exists and if not fall back to other possible fields */
    if (!array_key_exists("updated", $entry)) {
      $value = NULL;
      if (array_key_exists("last_updated", $entry)) {
        $value = $entry["last_updated"];
      }
      elseif (array_key_exists("updateddate", $entry)) {
        $value = $entry["updateddate"];
      }
      elseif (array_key_exists("changed", $entry)) {
        $value = $entry["changed"];
      }
      elseif (array_key_exists("updatedcommenteddate", $entry)) {
        $value = $entry["updated/commented date"];
      }
      elseif (array_key_exists("lastcommenttime", $entry)) {
        $value = $entry["last comment time"];
      }
      elseif (array_key_exists("last_comment_timestamp", $entry)) {
        $value = $entry["last_comment_timestamp"];
      }
      //_views_rdf_debug_stop($value);
//      if ($value) {
//        if (is_numeric($value))  { // timestamp
//          //_views_xml_debug_stop($value);
//          $value = format_date(intval($value), 'custom', DATE_ATOM);
//        }
//        else  {    // string date
//          $timestamp = strtotime($value);
//          if ($timestamp) $value = format_date($timestamp, 'custom', DATE_ATOM);
//          //_views_xml_debug_stop($timestamp);
//        }
//        $entry["updated"] = $value;
//        _views_xml_debug_stop($entry["updated"]);
//      }
        $entry["updated"] = $value;
    }

    /* Check if content attribute exists and if not fall back to using node body */
    if (!array_key_exists("content", $entry)) {
      if (array_key_exists("body", $entry)) {
        $entry["content"] = $entry["body"];
      }
      if (array_key_exists("node_body", $entry)) {
        $entry["content"] = $entry["node_body"];
      }
      if (array_key_exists("node_revisions_body", $entry)) {
        $entry["content"] = $entry["node_revisions_body"];
      }
    }

    /* Check if summary attribute exists and if not fall back to using node teader */
    if (!array_key_exists("summary", $entry)) {
      if (array_key_exists("node_teaser", $entry)) {
        $entry["summary"] = $entry["node_teaser"];
      }
      if (array_key_exists("node_revisions_teaser", $entry)) {
        $entry["summary"] = $entry["node_revisions_teaser"];
      }
    }

    if (isset($entry["id"])) $entry["id"] = check_plain($entry["id"]);
    if (isset($entry["title"])) $entry["title"] = check_plain($entry["title"]);
    if (isset($entry["update"])) $entry["updated"] = check_plain($entry["updated"]);
    $entries[] = $entry;

  }//for

  $vars["entries"] = $entries;
}
