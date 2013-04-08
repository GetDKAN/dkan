<?php
/**
 * @file
 * View template to render view fields as xhtml.
 *
 * - $view: The view in use.
 * - $rows: Array of row objects as rendered by _views_json_render_fields
 * - $attachment: not used currently
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_xhtml.views.inc
 */

function template_preprocess_views_views_xhtml_style_hcard(&$vars) {
  $view = $vars["view"];
  $rows = $vars["rows"];
  $options = $vars["options"];
  $link = $view->display_handler->get_path();
  $url_options = array('absolute' => TRUE);
  if (!empty($view->exposed_raw_input)) {
    $url_options['query'] = $view->exposed_raw_input;
  }
  $base = $view->base_table;
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'text/html' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
    $vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
    $vars["id"] = check_url($view->get_url());
    $vars["author"] = _views_xhtml_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  }
  else {
    $vars["header"] = $header;
  }
  $hcards = array();
  foreach ($rows as $row) {
    $hcard = array(
      'adr' => array(
        'type' => '',
        'post-office-box' => '',
        'street-address' => array(),
        'extended-address' => '',
        'region' => '',
        'locality' => '',
        'postal-code' => '',
        'country-name' => '',
      ),
      'agent' => array(),
      'bday' => '',
      'class' => '',
      'category' => array(),
      'email' => array(),
      'fn' => '',
      'n' => array(
        'honorific-prefix' => '',
        'given-name' => '',
        'additional-name' => '',
        'family-name' => '',
        'honorific-suffix' => '',
      ),
      'nickname' => '',
      'org' => array(
        'organization-name' => '',
        'organization-unit' => array(),
      ),
      'photo' => '',
      'tel' => array(),
    );
    foreach ($row as $field) {
      if ($options["field_output"] == "normal") {
        if ($field->label) {
          $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->label))) : $field->label;
        }
        else {
          $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        }
        if (!$field->is_multiple) {
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content;
        }
        else {
          $content = array();
          foreach ($field->content as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          $content = implode(" ", $content);
        }
      }
      elseif ($options["field_output"] == "raw") {
        $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        if (!$field->is_multiple)
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw;
        else {
          $content = array();
          foreach ($field->raw as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          $content = implode(" ", $content);
        }
      }
      $label = _views_xml_strip_illegal_xml_name_chars(html_entity_decode(check_plain(strip_tags($label))));
      $content = (($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$content]]>": $content);
      //if (($options["skip_empty_fields"] == TRUE) && (is_null($content) || $content === "")) continue;
      if ((stripos($label, 'address_type') !== FALSE) || (stripos($label, 'addresstype') !== FALSE)) {
        $hcard['adr']['type'] = $content;
      }
      if ((stripos($label, 'post_office_box') !== FALSE) || (stripos($label, 'postofficebox') !== FALSE)) {
        $hcard['adr']['post-office-box'] = $content;
      }
      if ((stripos($label, 'street_address') !== FALSE) || (stripos($label, 'strestaddress') !== FALSE)) {
        $hcard['adr']['street-address'][] = $content;
      }
      if ((stripos($label, 'extended_address') !== FALSE) || (stripos($label, 'extendedaddress') !== FALSE)) {
        $hcard['adr']['extended-address'] = $content;
      }
      if (stripos($label, 'region') !== FALSE) {
        $hcard['adr']['region'] = $content;
      }
      if (stripos($label, 'locality') !== FALSE) {
        $hcard['adr']['locality'] = $content;
      }
      if ((stripos($label, 'postal_code') !== FALSE) || (stripos($label, 'postalcode') !== FALSE)) {
        $hcard['adr']['postal-code'] = $content;
      }
      if ((stripos($label, 'country_name') !== FALSE) || (stripos($label, 'countryname') !== FALSE)) {
        $hcard['adr']['country-name'] = $content;
      }
      if (stripos($label, 'agent') !== FALSE) {
        $hcard['agent'][] = $content;
      }
      if (stripos($label, 'bday') !== FALSE) {
//        if (preg_match('/\d/', $content)) {
//          if (strtotime($content))
//            $value = format_date(strtotime($value), 'custom', EXHIBIT_DATE_FORMAT);
//        }
        $hcard['bday'] = $content;
      }
      if (stripos($label, 'class') !== FALSE) {
        $hcard['class'] = $content;
      }
      if (stripos($label, 'category') !== FALSE) {
        $hcard['category'][] = $content;
      }
      if (stripos($label, 'email') !== FALSE) {
        $hcard['email'][$label] = $content;
      }
      if ((stripos($label, 'honorific_prefix') !== FALSE) || (stripos($label, 'honorificprefix') !== FALSE)) {
        $hcard['n']['honorific-prefix'] = $content;
      }
      if ((stripos($label, 'given_name') !== FALSE) || (stripos($label, 'given name') !== FALSE)) {
        $hcard['n']['given-name'] = $content;
      }
      if ((stripos($label, 'additional_name') !== FALSE) || (stripos($label, 'additionalname') !== FALSE)) {
        $hcard['n']['additional-name'] = $content;
      }
      if ((stripos($label, 'family_name') !== FALSE) || (stripos($label, 'familyname') !== FALSE)) {
        $hcard['n']['family-name'] = $content;
      }
      if ((stripos($label, 'honorific_suffix') !== FALSE) || (stripos($label, 'honorificsuffix') !== FALSE)) {
        $hcard['n']['honorific-suffix'] = $content;
      }
      if (stripos($label, 'fn') !== FALSE) {
        $hcard['fn'] = $content;
      }
      if (stripos($label, 'nickname') !== FALSE) {
        $hcard['nickname'] = $content;
      }
      if ((stripos($label, 'organization_name') !== FALSE) || (stripos($label, 'organizationname') !== FALSE)) {
        $hcard['org']['organization-name'] = $content;
      }
      if ((stripos($label, 'organization_unit') !== FALSE) || (stripos($label, 'organizationunit') !== FALSE)) {
        $hcard['org']['organization-unit'][] = $content;
      }
      if (stripos($label, 'photo') !== FALSE) {
        $hcard['photo'] = $content;
      }
      if (stripos($label, 'tel') === 0) {
        $hcard['tel'][$label] = $content;
      }
    } $hcards[] = $hcard;
  }//for
  $vars["hcards"] = $hcards;
}

function template_preprocess_views_views_xhtml_style_hcalendar(&$vars) {
  $view = $vars["view"];
  $rows = $vars["rows"];
  $options = $vars["options"];
  $link = $view->display_handler->get_path();
  $url_options = array('absolute' => TRUE);
  if (!empty($view->exposed_raw_input)) {
    $url_options['query'] = $view->exposed_raw_input;
  }
  $base = $view->base_table;
  $plaintext_output = $options["plaintext_output"];
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'text/html' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
    $vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
    $vars["id"] = check_url($view->get_url());
    $vars["author"] = _views_xhtml_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  }
  else {
    $vars["header"] = $header;
  }
  $hcalendars = array();
  foreach ($rows as $row) {
    //_views_rdf_debug_stop($row);
    $hcalendar = array();
    foreach ($row as $field) {
      if ($options["field_output"] == "normal") {
        if ($field->label) {
          $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->label))) : $field->label;
        }
        else {
          $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        }
        if (!$field->is_multiple)
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content;
        else {
          $content = array();
          foreach ($field->content as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          $content = implode(" ", $content);
        }
      }
      elseif ($options["field_output"] == "raw") {
        $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        if (!$field->is_multiple)
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw;
        else {
          $content = array();
          foreach ($field->raw as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          $content = implode(" ", $content);
        }
      }
      $label = _views_xml_strip_illegal_xml_name_chars(html_entity_decode(check_plain(strip_tags($label))));
      $content = (($options['escape_as_CDATA'] == 'yes') ? "<![CDATA[$content]]>": $content);
      if (stripos($label, 'class') !== FALSE) {
        $hcalendar['class'] = $content;
      }
      if (stripos($label, 'category') !== FALSE) {
        $hcalendar['category'][] = $content;
      }
      if (stripos($label, 'description') !== FALSE) {
        $hcalendar['description'] = $content;
      }
      if (stripos($label, 'summary') !== FALSE) {
        $hcalendar['summary'] = $content;
      }
      if ((stripos($label, 'dtstart') !== FALSE) || (stripos($label, 'event_start') !== FALSE) || (stripos($label, 'eventstart') !== FALSE)
        || (stripos($label, 'start'))) {
//        if (preg_match('/\d/', $content)) {
//          if (strtotime($content))
//            $content = format_date(strtotime($content), 'custom', EXHIBIT_DATE_FORMAT);
//        }
        $hcalendar['dtstart'] = $content;
      }
      if ((stripos($label, 'dtend') !== FALSE) || (stripos($label, 'event_end') !== FALSE) || (stripos($label, 'eventendtime') !== FALSE)
      || (stripos($label, 'eventend') !== FALSE)|| (stripos($label, 'end') !== FALSE)) {
//        if (preg_match('/\d/', $content)) {
//          if (strtotime($content))
//            $content = format_date(strtotime($content), 'custom', EXHIBIT_DATE_FORMAT);
//        }
        $hcalendar['dtend'] = $content;
      }
      if (stripos($label, 'duration') !== FALSE) {
        $hcalendar['duration'] = $content;
      }
      if ((stripos($label, 'geo_latitude') !== FALSE) || (stripos($label, 'geolatitude') !== FALSE)) {
        $hcalendar['geo']['latitude'] = $content;
      }
      if ((stripos($label, 'geo_longitude') !== FALSE) || (stripos($label, 'geolongitude') !== FALSE)) {
        $hcalendar['geo']['longitude'] = $content;
      }
      if (stripos($label, 'location') !== FALSE) {
        $hcalendar['location'] = $content;
      }
      if (stripos($label, 'status') !== FALSE) {
        $hcalendar['status'] = $content;
      }
      if (stripos($label, 'uid') !== FALSE) {
        $hcalendar['uid'] = $content;
      }
      if (stripos($label, 'url') !== FALSE) {
        $hcalendar['url'] = $content;
      }
      if ((stripos($label, 'last_modified') !== FALSE) || (stripos($label, 'lastmodified') !== FALSE)) {
//        if (preg_match('/\d/', $content)) {
//          if (strtotime($content))
//            $content = format_date(strtotime($content), 'custom', EXHIBIT_DATE_FORMAT);
//        }
        $hcalendar['last-modified'] = $content;
      }
      if ((stripos($label, 'address_type') !== FALSE) || (stripos($label, 'addresstype') !== FALSE)) {
        $hcalendar['adr']['type'] = $content;
      }
      if ((stripos($label, 'post_office_box') !== FALSE) || (stripos($label, 'postofficebox') !== FALSE)) {
        $hcalendar['adr']['post-office-box'] = $content;
      }
      if ((stripos($label, 'street_address') !== FALSE) || (stripos($label, 'streetaddress') !== FALSE)) {
        $hcalendar['adr']['street-address'][] = $content;
      }
      if ((stripos($label, 'extended_address') !== FALSE) || (stripos($label, 'extended address') !== FALSE)) {
        $hcalendar['adr']['extended-address'] = $content;
      }
      if (stripos($label, 'region') !== FALSE) {
        $hcalendar['adr']['region'] = $content;
      }
      if (stripos($label, 'locality') !== FALSE) {
        $hcalendar['adr']['locality'] = $content;
      }
      if ((stripos($label, 'postal_code') !== FALSE) || (stripos($label, 'postalcode') !== FALSE)) {
        $hcalendar['adr']['postal-code'] = $content;
      }
      if (stripos($label, 'country_name') !== FALSE) {
        $hcalendar['adr']['country-name'] = $content;
      }
    }
    $hcalendars[] = $hcalendar;
  }//for
  $vars["hcalendars"] = $hcalendars;
}
