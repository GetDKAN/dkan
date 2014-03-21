<?php
/**
 * @file views-views-xhtml-style-hcard.tpl.php
 * Default template for the Views XHTML style plugin using the hCard format
 *
 * Variables:
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields
 * - $hcards Array of hcard arrays as created by template_preprocess_views_views_xhtml_style_hcard
 *
 * @ingroup views_templates
 */

  $xhtml .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
  $xhtml .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr"'.">\r\n";
  $xhtml .= '<head>'."\r\n";
  if (!$header) { //build our own header
    $xhtml .= '  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\r\n";
    $xhtml .= '  <meta name="KEYWORDS" content="hCards" />'."\r\n";
    $xhtml .= '  <title>hCards</title>'."\r\n";
    $xhtml .= "<meta http-equiv=\"Content-Type\" content=$content_type; charset=utf-8/>\r\n";
    $xhtml .= '  <meta name="KEYWORDS" content="hCards" />'."\r\n";
    $xhtml .= '  <title>hCards</title>'."\r\n";
    }
  else {
    $xhtml .= "  $header\n";
  }
  $xhtml .= '</head>'."\r\n";
  $xhtml .= '<body>'."\r\n";
  foreach($hcards as $hcard) {
    $xhtml .= '<div class = "vcard">'."\r\n";
    if ($hcard['photo'] != '')
      $xhtml .= '  <img class="photo" alt="photo" title="photo" style="height:96px;width:96px" src="'. $hcard['photo'] .'"/>'."<br/>\r\n";
    if ($hcard['fn'])
      $xhtml .= '  <span class="fn">'. $hcard['fn'] .'</span>'."<br/>\r\n";
    if ($hcard['nickname'])
      $xhtml .= '  <span class="nickname">'. $hcard['nickname'] .'</span>'."<br/>\r\n";
    $name = $hcard['n'];
    if ($hcard['fn'])
      $xhtml .= '  <span class = "n">'."\r\n";
    else
      $xhtml .= '  <span class = "fn n">'."\r\n";
    if ($name['honorific-prefix'] !== '')
      $xhtml .= '    <span class="honorific-prefix">'. $name['honorific-prefix'] .'</span>'."\r\n";
    if ($name['given-name'] !== '')
      $xhtml .= '    <span class="given-name">'. $name['given-name'] .'</span>'."\r\n";
    if ($name['additional-name'] !== '')
      $xhtml .= '    <span class="additional-name">'. $name['additional-name'] .'</span>'."\r\n";
    if ($name['family-name'] !== '')
      $xhtml .= '    <span class="family-name">'. $name['family-name'] .'</span>'."\r\n";
    if ($name['honorific-suffix'] !== '')
      $xhtml .= '    <span class="honorific-suffix">'. $name['honorific-suffix'] .'</span>'."\r\n";
    $xhtml .= '  </span><br/>'."\r\n";
    if ($hcard['nickname'] !== '')
      $xhtml .= '    <span class="nickname">'. $hcard['nickname'] .'</span><br/>'."\r\n";
    $org = $hcard['org'];
    $xhtml .= '  <span class="org">'."\r\n";
    if ($org['organization-name'] !== '')
      $xhtml .= '    <span class="organization name">'. $org['organization-name'] .'</span><br/>'."\r\n";
    $org_units = $org['organization-unit'];
    foreach ($org_units as $org_unit)
      $xhtml .= '    <span class="organization-unit">'. $org_unit .'</span>'."<br/>\r\n";
    $xhtml .= '  </span>'."\r\n";
    $address = $hcard['adr'];
    $xhtml .= '  <span class = "adr">'."\r\n";
    if ($address['type'] !== '')
      $xhtml .= '    <span class="type">'. $address['type'] .'</span>'."<br/>\r\n";
    if ($address['post-office-box'] !== '')
      $xhtml .= '    <span class="post-office-box">'. $address['post-office-box'] .'</span>'."<br/>\r\n";
    $street_addresses = $address['street-address'];
    foreach ($street_addresses as $street_address)
      $xhtml .= '    <span class="street-address">'. $street_address .'</span>'."<br/>\r\n";
    if ($address['extended-address'] !== '')
      $xhtml .= '    <span class="extended-address">'. $address['extended-address'] .'</span>'."<br/>\r\n";
    if ($address['region'] !== '')
      $xhtml .= '    <span class="region">'. $address['region'] .'</span>'."<br/>\r\n";
    if ($address['locality'] !== '')
      $xhtml .= '    <span class="locality">'. $address['locality'] .'</span>'."<br/>\r\n";
    if ($address['postal-code'] !== '')
      $xhtml .= '    <span class="postal-code">'. $address['postal-code'] .'</span>'."<br/>\r\n";
    if ($address['country-name'] !== '')
      $xhtml .= '    <span class="country-name">'. $address['country-name'] .'</span>'."\r\n";
    $xhtml .= '  </span><br/>'."\r\n";
    $agents = $hcard['agent'];
    foreach ($agents as $agent)
      $xhtml .= '  <span class="agent">'. $agent .'</span>'."<br/>\r\n";
    $birthday =  $hcard['bday'];
    if ($birthday !== '')
      $xhtml .= '  <span class="bday">'. $birthday .'</span>'."<br/>\r\n";
    $class = $hcard['class'];
    if ($class !== '')
      $xhtml .= '  <span class="class">'. $class .'</span>'."<br/>\r\n";
    $categories = $hcard['category'];
    foreach ($categories as $category)
      $xhtml .= '  <span class="category">'. $category .'</span>'."<br/>\r\n";
    if ($hcard['email']) {
      $mail_addrs = $hcard['email'];
      foreach ($mail_addrs as $mail_type => $mail_addr)
        $xhtml .= '  <span class="email">'."\r\n".
                  '    <span class="type">'. $mail_type .': </span>'."\r\n".
                  '    <a class="value" href="mailto:'. $mail_addr .'">'. $mail_addr .'</a>'."\r\n".
                  '  </span>'."<br/>\r\n";

    }
    if ($hcard['tel']) {
      $tel_nos = $hcard['tel'];
      foreach ($tel_nos as $tel_no_type => $tel_no)
        $xhtml .= '  <span class="tel">'.
                    '<span class="type">'. $tel_no_type .': </span>'.
                    '<span class="value">'. $tel_no .'</span>'.
                    '</span>'."<br/>\r\n";
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
