<?php
/**
 * @file views-views-xhtml-style-hcalendar.tpl.php
 * Default template for the Views XHTML style plugin using the hCalendar format
 *
 * Variables:
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields
 * - $hcalendar Array of hcalendar arrays as created by template_preprocess_views_views_xhtml_style_hcalendar
 *
 * @ingroup views_templates
 */

  $xhtml .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
  $xhtml .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr"'.">\r\n";
  $xhtml .= '<head>'."\r\n";
  if (!$header) { //build our own header
    $xhtml .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
    $xhtml .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr"'.">\r\n";
    $xhtml .= '<head>'."\r\n";
    $xhtml .= "<meta http-equiv=\"Content-Type\" content=$content_type; charset=utf-8/>\r\n";
    $xhtml .= '  <meta name="KEYWORDS" content="hCalendars" />'."\r\n";
    $xhtml .= '  <title>hCalendars</title>'."\r\n";
    $xhtml .= '</head>'."\r\n";
    $xhtml .= '<body>'."\r\n";
    }
  else {
    $xhtml .= "  $header\n";
  }
  $xhtml .= '</head>'."\r\n";
  $xhtml .= '<body>'."\r\n";
  foreach($hcalendars as $hcalendar) {
    $xhtml .= '<div class = "vevent">'."\r\n";
    $class = $hcalendar['class'];
    if ($class) $xhtml .= '  <span class="class">'. $class .'</span>'."<br/>\r\n";
    $categories = $hcalendar['category'];
    if ($categories)
      foreach ($categories as $category) $xhtml .= '  <span class="category">'. $category .'</span>'."<br/>\r\n";
    $dtstart = $hcalendar['dtstart'];
    if ($dtstart)
    $xhtml .= '  <span class="dtstart">'. $dtstart .'</span>'."<br/>\r\n";
    $summary = $hcalendar['summary'];
    if ($summary) $xhtml .= '  <span class="summary">'. $summary .'</span>'."<br/>\r\n";
    $dtend = $hcalendar['dtend'];
    if ($dtend) $xhtml .= '  <span class="dtend">'. $dtend .'</span>'."<br/>\r\n";
    $location = $hcalendar['location'];
    if ($location) $xhtml .= '  <span class="location">'. $location .'</span>'."<br/>\r\n";
    $geo = $hcalendar["geo"];
    if ($geo) {
      $latitude = $geo["latitude"];
      $longitude = $geo['longitude'];
      if ($latitude || $longitude) {
        $xhtml .= "  <div class=\"geo\">\n";
        if ($location) $xhtml  .= "    $location: \n";
        if ($latitude) $xhtml  .= "    <span class=\"latitude\">$latitude</span>\n";
        if ($longitude) $xhtml .= "    <span class=\"longitude\">$longitude</span>\n";
        $xhtml .= "  </div>\n";
      }
    }
    $status = $hcalendar['status'];
    if ($status) $xhtml .= '  <span class="status">'. $status .'</span>'."<br/>\r\n";
    $duration = $hcalendar['duration'];
    if ($duration) $xhtml .= '  <span class="duration">'. $duration .'</span>'."<br/>\r\n";
    $uid= $hcalendar['uid'];
    if ($uid) $xhtml .= '  <span class="uid">'. $uid .'</span>'."<br/>\r\n";
    $url = $hcalendar['url'];
    if ($url) $xhtml .= '  <span class="url">'. $url .'</span>'."<br/>\r\n";
    $last_modified = $hcalendar['last-modified'];
    if ($last_modified) $xhtml .= '  <span class="last-modified">'. $last_modified .'</span>'."<br/>\r\n";
    $description = $hcalendar['description'];
    if ($description) $xhtml .= '  <span class="description">'. $description .'</span>'."<br/>\r\n";
    $adr = $hcalendar['adr'];
    if ($adr) {
      $xhtml .= "  <div class=\"adr\">\n";
      $adr_type = $adr['address-type'];
      if ($adr_type) $xhtml .= '    <span class="address-type">'. $adr_type .'</span>'."<br/>\r\n";
      $xhtml .= "  </div>";
    }
    $xhtml .= '</div>'."\r\n";
  }
  $xhtml .= '</body>'."\r\n";
  $xhtml .= '</html>'."\r\n";
  if ($view->override_path) {       // inside live preview
    print htmlspecialchars($xhtml);
  }
  else if ($options['using_views_api_mode']) {     // We're in Views API mode.
    print $xhtml;
  }
  else {
    drupal_add_http_header("Content-Type", "$content_type; charset=utf-8");
    print $xhtml;
    drupal_page_footer();
    exit;
  }
