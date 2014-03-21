<?php
/**
 * @file views-views-rdf-style-sioc.tpl.php
 * Default template for the Views RDF style plugin using the SIOC vocabulary
 *
 * Variables:
 * - $view: The View object.
 * - $rows: Array of row objects as rendered by _views_xml_render_fields
 * - $nodes, $users Array of user and node objects created by template_preprocess_views_views_rdf_style_sioc
 *
 * @ingroup views_templates
 */

global $base_url;
$content_type = ($options['content_type'] == 'default') ? 'application/rdf+xml' : $options['content_type'];
if (!$header) { //build our own header
  $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  $xml .= '<!-- generator="Drupal Views Datasource Module" -->'."\n";
  $xml .= "<rdf:RDF\r\n";
  $xml .= "  xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\r\n";
  $xml .= "  xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"\r\n";
  $xml .= "  xmlns:sioc=\"http://rdfs.org/sioc/ns#\"\r\n";
  $xml .= "  xmlns:sioct=\"http://rdfs.org/sioc/terms#\"\r\n";
  $xml .= "  xmlns:dc=\"http://purl.org/dc/elements/1.1/\"\r\n";
  $xml .= "  xmlns:dcterms=\"http://purl.org/dc/terms/\"\r\n";
  $xml .= "  xmlns:admin=\"http://webns.net/mvcb/\"\r\n";
  $xml .= "  xmlns:foaf=\"http://xmlns.com/foaf/0.1/\">\r\n";
}
if ($users) {
 if (!$nodes) { //Document is about users
   $xml .= "<foaf:Document rdf:about=\"". url($view->name, array('absolute' => TRUE)) ."\">\n";
   $xml .= "  <dc:title>SIOC user profiles for: ". variable_get('site_name', 'drupal') ."</dc:title>\n";
   $xml .= "  <dc:description>\n";
   $xml .= "    A User is an online account of a member of an online community. ";
   $xml .= "It is connected to Items and Posts that a User creates or edits, ";
   $xml .= "to Containers and Forums that it is subscribed to or moderates and ";
   $xml .= "to Sites that it administers. Users can be grouped for purposes of ";
   $xml .= "allowing access to certain Forums or enhanced community site features (weblogs, webmail, etc.).";
   $xml .= "A foaf:Person will normally hold a registered User account on a Site ";
   $xml .= "(through the property foaf:holdsAccount), and will use this account ";
   $xml .= "to create content and interact with the community. sioc:User describes ";
   $xml .= "properties of an online account, and is used in combination with a ";
   $xml .= "foaf:Person (using the property sioc:account_of) which describes ";
   $xml .= "information about the individual itself.\n";
   $xml .= "  </dc:description>\n";
   $xml .= "####foaf_topics####\n";
   $xml .= "  <admin:generatorAgent rdf:resource=\"http://drupal.org/project/views_datasource\"/>\n";
   $xml .= "</foaf:Document>\n";
   foreach($users as $user) {
     $uid = $user["uid"];
     $user_name = $user["name"];
     $email = $user["mail"];
     $xml .="<foaf:Person rdf:about=\"". url('user/'. $uid, array('absolute' => TRUE)) ."\">\n";
     $xml .="  <foaf:name>$user_name</foaf:name>\n";
     $xml .="  <foaf:mbox_sha1sum>". md5('mailto:'. $email) ."</foaf:mbox_sha1sum>\n";
     $xml .="  <foaf:holdsAccount>\n";
     $xml .="    <sioc:User rdf:nodeID=\"$uid\">\n";
     $xml .="      <sioc:name>$user_name</sioc:name>\n";
     $xml .="      <sioc:email rdf:resource=\"mailto:$email\"/>\n";
     $xml .="      <sioc:email_sha1>". md5('mailto:'. $email) ."</sioc:email_sha1>\n";
     $xml .="      <sioc:link rdf:resource=\"". url('user/'. $uid, array('absolute' => TRUE)) ."\" rdfs:label=\"$user_name\"/>\n";
     $roles = array();
     $roles_query = db_query("SELECT r.name AS name, r.rid AS rid FROM {users_roles} ur, {role} r WHERE ur.uid = %d AND ur.rid = r.rid", $uid);
     while ($role = db_fetch_object($roles_query)) $roles[$role->rid] = $role->name;
       if (count($roles) > 0) {
         $xml .="      <sioc:has_function>\n";
         foreach ($roles as $rid => $name) $xml .="        <sioc:Role><rdfs:label><![CDATA[$name]]></rdfs:label></sioc:Role>\n";
         $xml .="      </sioc:has_function>\n";
       }
       $xml .="    </sioc:User>\n";
       $xml .="  </foaf:holdsAccount>\n";
       $xml .="</foaf:Person>\n";
    }
  }
}

if ($nodes) {
  $users_xml = "";
  $nodes_xml = "";
  $users_done = array();
  $count = 0;
  foreach($nodes as $node) {

    if ((array_key_exists("id", $node)) && (array_key_exists("title", $node)) && (array_key_exists("type", $node))
      && (array_key_exists("created", $node)) && (array_key_exists("changed", $node)) && (array_key_exists("last_updated", $node))
      && (array_key_exists("uid", $node)) && (array_key_exists("body", $node))) {
      if (array_key_exists($node["id"], $users) && (!array_key_exists($node["uid"], $users_done))) {
        $user = $users[$node["id"]];
        $users_done[$node["uid"]] = $user;
        $users_xml .=  _views_rdf_sioc_xml_user_render($user);
      }
      $nodes_xml .= _views_rdf_sioc_xml_story_render($node["id"], $node["title"], $node["type"], $node["created"], $node["changed"], $node["last_updated"], $node["uid"], $node["body"]);
    }
    else {
      $nid = $node["id"];
      $nodes_xml .= "<missing> node $nid is missing one or more of the id, title, type, created, changed, last_updated, uid, or body attributes.</missing>";
//      if ($view->override_path)
//        print '<b style="color:red">One of the id, title, type, created, changed, lasty_updated, uid, and body attributes is missing.</b>';
//      elseif ($options['using_views_api_mode'])
//        print "One of the id, title, type, created, changed, lasty_updated, uid, and body attributes is missing.";
//      else drupal_set_message(t('One of the id, title, type, created, changed, lasty_updated, uid, and body attributes is missing.'), 'error');
//      return;
    }
  }//for
}//if

$xml .= $users_xml.$nodes_xml;
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
