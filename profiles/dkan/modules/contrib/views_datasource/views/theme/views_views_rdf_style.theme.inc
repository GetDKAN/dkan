<?php
/**
 * @file
 * View template to render view fields as rdf.
 *
 * - $view: The view in use.
 * - $rows: Array of row objects as rendered by _views_json_render_fields
 * - $attachment: not used currently
 * - $options: The options for the style passed in from the UI.
 *
 * @ingroup views_templates
 * @see views_rdf.views.inc
 */

/**
 * Template preprocess for the FOAF vocabulary
 * @param $vars
 * @return unknown_type
 */
function template_preprocess_views_views_rdf_style_foaf(&$vars) {
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
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'application/atom+rdf' : $options['content_type'];
  $header  = $options["header"];
  if (empty($vars["header"]) || !$vars["header"]) {
    $vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
    $vars["id"] = check_url($view->get_url());
    $vars["updated"] = format_date(time(), 'custom', DATE_ATOM);
    $vars["author"] = _views_rdf_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  }
  else {
    $vars["header"] = $header;
  }
  $persons = array();
  foreach ($rows as $row) {
    $person = array();
    foreach ($row as $field) {
      //_views_rdf_debug_stop($row);
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
        if (!$field->is_multiple) {
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw;
        }
        else {
          $content = array();
          foreach ($field->raw as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          $content = implode(" ", $content);
        }
      }
      //if (($options["skip_empty_fields"] == TRUE) && (is_null($content) || $content === "")) continue;
      if (stripos($label, 'firstname') !== FALSE) {
        $person["firstName"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'surname') !== FALSE) {
        $person["surName"] = check_plain($content);
        continue;
      }
      if ((stripos($label, 'name') !== FALSE) && ((!stripos($label, 'firstname')) && (!stripos($label, 'surname')))) {
        $person["name"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'title') !== FALSE) {
        $person["title"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'nick') !== FALSE) {
        $person["nick"] = check_plain($content);
        continue;
      }
      if ((stripos($label, 'mbox') !== FALSE) && !(stripos($label, 'mbox_sha1sum') !== FALSE)) {
        $person["mbox"] = check_plain($content);
        continue;
      }
      if (((stripos($label, 'mail') !== FALSE) || (stripos($label, 'email') !== FALSE)) && (!array_key_exists('mbox', $person))) {
        //_views_rdf_debug_stop($label);
        $person["mbox"] = check_plain($content);
        $person["mbox_sha1sum"] = md5("mailto:" . check_plain($content));
        continue;
      }
      if (stripos($label, 'mbox_sha1sum') !== FALSE) {
        $person["mbox_sha1sum"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'openid') !== FALSE) {
        $person["openid"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'workplaceHomepage') !== FALSE) {
        $person["workplaceHomepage"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'homepage') !== FALSE) {
        //$rdf .= '  <foaf:homepage rdf:resource="'. $value .'"/>'."\n";
        $person["homepage"] = _views_rdf_strip_illegal_xml_attribute_value_chars(check_plain($content));
        continue;
      }
      if (stripos($label, 'weblog') !== FALSE) {
        //$rdf .= '  <foaf:weblog rdf:resource="'. $value .'"/>'."\n";
        $person["weblog"] = _views_rdf_strip_illegal_xml_attribute_value_chars(check_plain($content));
        continue;
      }
      if (stripos($label, 'img') !== FALSE) {
        //$rdf .= '  <foaf:img rdf:resource="'. $value .'"/>'."\n";
        //$rdf .= '  <foaf:depiction rdf:resource="'. $value .'"/>'."\n";
        $person["img"] = _views_rdf_strip_illegal_xml_attribute_value_chars(check_plain($content));
        continue;
      }
      if (stripos($label, 'member') !== FALSE) {
        //$rdf .= "  <foaf:member>$value</foaf:member>\n";
        $person["member"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'phone') !== FALSE) {
        //$rdf .= "  <foaf:phone>$value</foaf:phone>\n";
        $person["phone"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'jabberID') !== FALSE) {
        $person["jabberID"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'msnChatID') !== FALSE) {
        $person["msnChatID"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'aimChatID') !== FALSE) {
        $person["aimChatID"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'yahooChatID') !== FALSE) {
        $person["yahooChatID"] = check_plain($content);
        continue;
      }
    }
    $persons[] = $person;
  }//for
  $vars["persons"] = $persons;
}

/**
 * Template preprocess for the SIOC vocabulary
 * @param $vars
 * @return unknown_type
 */
function template_preprocess_views_views_rdf_style_sioc(&$vars) {
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
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'application/atom+rdf' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
    $vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
    $vars["id"] = check_url($view->get_url());
    $vars["updated"] = format_date(time(), 'custom', DATE_ATOM);
    $vars["author"] = _views_rdf_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  }
  else {
    $vars["header"] = $header;
  }
  $users = array();
  $nodes = array();
  if ($base == "users") {
    foreach ($rows as $row) {
      $user = array();
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
          if (!$field->is_multiple) {
            $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw;
          }
          else {
            $content = array();
            foreach ($field->raw as $n => $oc) $content[$n] = $plaintext_output ? check_plain(html_entity_decode(strip_tags($oc))) : $oc;
            $content = implode(" ", $content);
          }
        }
        if ((drupal_strtolower($label) == 'id') || (drupal_strtolower($label) == 'uid') && (!array_key_exists("uid", $user))) {
          $user["uid"] = $content;
          continue;
        }
        if ((drupal_strtolower($label) == 'name') || (drupal_strtolower($label) == 'users_name') && (!array_key_exists("name", $user))) {
          $user["name"] = $content;
          continue;
        }
        if ((drupal_strtolower($label) == 'mail') ||(drupal_strtolower($label) == 'email') || (drupal_strtolower($label) == 'users_mail') && (!array_key_exists("email", $user))) {
          $user["mail"] = $content;
          continue;
        }
      }
      $users[] = $user;
    }
  }
  if ($base == 'node') {
    foreach ($rows as $row) {
      $node = array();
      foreach ($row as $field) {
        if ($options["field_output"] == "normal") {
          if ($field->label) {
            $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->label))) : $field->label;
          }
          else {
            $label = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
          }
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content;
        }
        /* rdf:ID */
        if (!array_key_exists("id", $node)) {
          if (drupal_strtolower($label) == "id") {
            $node["id"] = $content;
            continue;
          }
        }

        /* use nid for rdf:ID if none specified */
        if (!array_key_exists("id", $node)) {
          if (drupal_strtolower($label) == "nid") {
            $node["id"] = $content;
            continue;
          }
        }

        /* RDF:created*/
        if (!array_key_exists("created", $node)) {
          if (drupal_strtolower($label) == "created") {
            $node["created"] = $content;
            continue;
          }
        }

        /* rdf:created */
        if (!array_key_exists("created", $node)) {
          if ((drupal_strtolower($label) == "node_created") || (drupal_strtolower($label) == "post date")) {
            //_views_rdf_debug_stop("created");
            $node["created"] = $content;
            continue;
          }
        }

        /* rdf:type */
        if (!array_key_exists("type", $node)) {
          if (drupal_strtolower($label) == "type") {
            $node["type"] = $content;
            continue;
          }
        }

        /* rdf:type */
        if (!array_key_exists("type", $node)) {
          if (drupal_strtolower($label) == "node_type") {
            $node["type"] = $content;
            continue;
          }
        }

        /* rdf:changed */
        if (!array_key_exists("changed", $node)) {
          if (drupal_strtolower($label) == "changed") {
            $node["changed"] = $content;
            continue;
          }
        }

        /* use updatedcommented if no changed*/
          if (!array_key_exists("changed", $node)) {
            if ((drupal_strtolower($label) == "updated/commented date")) {
            $node["changed"] = $content;
            continue;
          }
        }

        /* use node_changed if no changed*/
          if (!array_key_exists("changed", $node)) {
            if ((drupal_strtolower($label) == "node_changed")) {
            $node["changed"] = $content;
            continue;
          }
        }

        /* rdf:last updated */
        if (!array_key_exists("last_updated", $node)) {
          if (drupal_strtolower($label) == "last_updated") {
            $node["last_updated"] = $content;
            continue;
          }
        }

        /* use lastupdateddate if no  last_updated */
        if (!array_key_exists("last_updated", $node)) {
          if ((drupal_strtolower($label) == "updated date")) {
            $node["last_updated"] = $content;
            continue;
          }
        }


        /* rdf:title */
        if (!array_key_exists("title", $node)) {
          if (drupal_strtolower($label) == "title") {
            $node["title"] = $content;
            continue;
          }
        }


        /* rdf title attribute */
        if (!array_key_exists("title", $node)) {
          if (drupal_strtolower($label) == "node_title") {
            $node["title"] = $content;
            continue;
          }
        }

        /* rdf body */
        if (!array_key_exists("body", $node)) {
          if (drupal_strtolower($label) == "body") {
            $node["body"] = $content;
          }
        }

        /* try node_revisions_body */
        if (!array_key_exists("body", $node)) {
          if (drupal_strtolower($label) == "node_revisions_body") {
            $node["body"] = $content;
          }
        }

        /* rdf uid */
        if (!array_key_exists("uid", $node)) {
          if (drupal_strtolower($label) == "uid") {
            $node["uid"] = $content;
          }
        }

        /* try users_uid for uid*/
        if (!array_key_exists("uid", $node)) {
          if (drupal_strtolower($label) == "users_uid") {
            $node["uid"] = $content;
          }
        }
      }
      if (array_key_exists("uid", $node)) {
          $user_obj = user_load($node["uid"]);
          $user["uid"] = $user_obj->uid;
          $user["name"] = $user_obj->name;
          $user["mail"] = $user_obj->mail;
      }
      if ($node["id"]) {
        $nodes[$node["id"]] = $node;
        if ($user) {
          $users[$node["id"]] = $user;
        }
      }

    }//for
  }//if
  $vars["nodes"] = $nodes;
  $vars["users"] = $users;

  //_views_rdf_debug_stop($nodes);
  //_views_rdf_debug_stop($users);

}
function _views_rdf_sioc_xml_user_render($user) {
  $xml = "<foaf:Person rdf:about=\"" . url('user/' . $user["uid"], array('absolute' => TRUE)) . "\">\n";
  $xml .= "  <foaf:name>" . $user["name"] . "</foaf:name>\n";
  $xml .= "  <foaf:mbox_sha1sum>" . md5('mailto:' . $user["mail"]) . "</foaf:mbox_sha1sum>\n";
  $xml .= "  <foaf:holdsAccount>\n";
  $xml .= "    <sioc:User rdf:nodeID=\"" . $user["uid"] . "\">\n";
  $xml .= "      <sioc:name>" . $user["name"] . "</sioc:name>\n";
  $xml .= "      <sioc:email rdf:resource=\"mailto:." . $user["mail"] . "\"/>\n";
  $xml .= "      <sioc:email_sha1>" . md5('mailto:' . $user["mail"]) . "</sioc:email_sha1>\n";
  $xml .= "      <sioc:link rdf:resource=\"" . url('user/' . $user["uid"], array('absolute' => TRUE)) . "\" rdfs:label=\"" . $user["name"] . "\"/>\n";
  $roles = array();
  $roles_query = db_query("SELECT r.name AS name, r.rid AS rid FROM {users_roles} ur, {role} r WHERE ur.uid = %d AND ur.rid = r.rid", $user["uid"]);
  while ($role = db_fetch_object($roles_query)) $roles[$role->rid] = $role->name;
  if (count($roles) > 0) {
    $xml .="      <sioc:has_function>\n";
    foreach ($roles as $rid => $name)
    $xml .="        <sioc:Role><rdfs:label><![CDATA[$name]]></rdfs:label></sioc:Role>\n";
    $xml .="      </sioc:has_function>\n";
  }
  $xml .= "    </sioc:User>\n";
  $xml .= "  </foaf:holdsAccount>\n";
  $xml .= "</foaf:Person>\n";
  return $xml;
}

function _views_rdf_sioc_xml_story_render($nid, $title, $type, $created, $changed, $last_updated, $uid, $body) {
  $node_url = url("node/$nid", array('absolute' => TRUE));
  $xml = "";
  $xml .= "<sioc:Post rdf:about=\"$node_url\">\n";
  $xml .= "  <dc:title>$title</dc:title>\n";
  $xml .= "  <sioc:content>\n ";
  $xml .= "    <![CDATA[$body]]>\n";
  $xml .= "  </sioc:content>\n";
  //$xml .= "  <dc:created>". format_date(strtotime($created), 'custom', DATE_ISO8601) ."</dc:created>\n";
  //$xml .= "  <dc:modified>". format_date(strtotime($changed), 'custom', DATE_ISO8601) ."</dc:modified>\n";
  $xml .= "  <dc:created>" . $created . "</dc:created>\n";
  $xml .= "  <dc:modified>" . $changed . "</dc:modified>\n";
  $xml .= "  <sioc:link rdf:resource=\"$node_url\" rdfs:label=\"$title\" />\n";
  $xml .= "  <sioc:has_creator rdf:nodeID=\"$uid\"/>\n";

  /* Add taxonomy terms as SIOC topics */
  $query = db_query('SELECT tn.tid AS tid, td.name AS name FROM {term_node} tn, {term_data} td WHERE td.tid = tn.tid AND tn.nid = %d', $nid);
  while ($term = db_fetch_object($query)) {
    $taxonomy_terms = "  <sioc:topic rdfs:label=\"$term->name\" rdf:resource=\"" . url("taxonomy/term/$term->tid", array('absolute' => TRUE)) . "\" />\n";
  }
  $xml .= $taxonomy_terms;

  /* Add comments as SIOC replies */
  $query_count = 'SELECT COUNT(*) FROM {comments} WHERE nid = %d AND status = %d';
  $query = 'SELECT c.cid as cid, c.pid, c.nid, c.subject, c.comment, c.format, c.timestamp, c.name, c.mail, c.homepage, u.uid, u.name AS registered_name, u.signature, u.picture, u.data, c.thread, c.status FROM {comments} c INNER JOIN {users} u ON c.uid = u.uid WHERE c.nid = %d and c.status = %d ORDER BY SUBSTRING(c.thread, 1, (LENGTH(c.thread) - 1))';
  $query_args = array($nid, COMMENT_PUBLISHED);
  $query = db_rewrite_sql($query, 'c', 'cid');
  $comment_children = 0;
  $num_rows = FALSE;
  $comments = '';
  $result = db_query($query, $query_args);
  while ($comment = db_fetch_object($result)) {
    $comment = drupal_unpack($comment);
//    var_dump($comment);module_invoke_all('exit');return;
//    $comment->depth = count(explode('.', $comment->thread)) - 1;
//    if ($comment->depth > $comment_children) {
//      $comment_children++;
//      $comments .= "  <sioc:has_reply>\n";
//    }
//    else {
//      while ($comment->depth < $comment_children) {
//        $comment_children--;
//        $comments .= "  </sioc:has_reply>\n";
//      }
//    }
//    $comments .="     <sioc:Post rdf:about=\"$node_url#comment-$comment->cid\">\n";
//    while ($comment_children-- > 0) {
//      $num_rows = TRUE;
//      $comments .="       <sioc:content><![CDATA[$comment->comment]]></sioc:content>\n";
//      $comments .="     </sioc:Post>\n";
//      $comments .= "  </sioc:has_reply>\n";
//    }
//  }
    $comments .= "  <sioc:has_reply>\n";
    $comments .= "    <sioc:Post rdf:about=\"$node_url#comment-$comment->cid\">\n";
    if ($comment->subject) $comments .= "      <dc:title>$comment->subject</dc:title>\n";
    if ($comment->timestamp) $comments .= "      <dc:created>" . format_date($comment->timestamp, 'custom', DATE_ISO8601) . "</dc:created>\n";
    if ($comment->uid) {
      $comments .= "    <sioc:has_creator>\n";
      $comments .= "      <sioc:User>\n";
      $comments .= "        <sioc:name>$comment->registered_name</sioc:name>\n";
      $comments .= "        <sioc:email rdf:resource=\"mailto:$comment->mail\"/>\n";
      $comments .="         <sioc:link rdf:resource=\"" . url('user/' . $comment->uid, array('absolute' => TRUE)) . "\" rdfs:label=\"$comment->registered_name\"/>\n";
      $comments .= "      </sioc:User>\n";
      $comments .= "    </sioc:has_creator>\n";
    }
    $comments .= "      <sioc:content><![CDATA[$comment->comment]]></sioc:content>\n";
    $comments .= "    </sioc:Post>\n";
    $comments .= "  </sioc:has_reply>\n";
  }
  $xml .= $comments;

  $xml .= "</sioc:Post>\n";
  return $xml;
}

/**
 * Template preprocess for the DOAP vocabulary
 * @param $vars
 * @return none
 */
function template_preprocess_views_views_rdf_style_doap(&$vars) {
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
  $vars["content_type"] = ($options['content_type'] == 'default') ? 'application/atomrdf' : $options['content_type'];
  $header  = $options["header"];
  if (!$vars["header"]) {
    $vars["title"] = check_plain(($view->get_title() ? $view->get_title(): $view->name));
    $vars["id"] = check_url($view->get_url());
    $vars["updated"] = format_date(time(), 'custom', DATE_ATOM);
    $vars["author"] = _views_rdf_format_author($options["author"]);
    $vars['link'] = check_url(url($view->display_handler->get_path(), $url_options));
  }
  else {
    $vars["header"] = $header;
  }
  $projects = array();
  foreach ($rows as $row) {
    if (array_key_exists($row["nid"], $projects)) {
      $project = $projects[$row["nid"]];
    }
    else {
      $project = array();
      $project["repositories"] = array();
      $project["developers"] = array();
      $project["nid"] = $row["nid"];
    }
    foreach ($row as $field) {
      if ($options["field_output"] == "normal") {
        if ($field->label) {
          $label = $plaintext_output ?
            check_plain(html_entity_decode(strip_tags($field->label))) : $field->label;
        }
        else {
          $label = $plaintext_output ?
            check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        }
        if (!$field->is_multiple) {
          $content = $plaintext_output ? check_plain(html_entity_decode(strip_tags($field->content))) : $field->content;
        }
        else {
          $content = array();
          foreach ($field->content as $n => $oc) {
            $content[$n] = $plaintext_output ?
              check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          }
          $content = implode(" ", $content);
        }
      }
      elseif ($options["field_output"] == "raw") {
        $label = $plaintext_output ?
          check_plain(html_entity_decode(strip_tags($field->id))) : $field->id;
        if (!$field->is_multiple) {
          $content = $plaintext_output ?
            check_plain(html_entity_decode(strip_tags($field->content))) : $field->raw;
        }
        else {
          $content = array();
          foreach ($field->raw as $n => $oc) {
            $content[$n] = $plaintext_output ?
              check_plain(html_entity_decode(strip_tags($oc))) : $oc;
          }
          $content = implode(" ", $content);
        }
      }
      if (stripos($label, 'homepage') !== FALSE) {
        $project["homepage"] = _views_rdf_strip_illegal_xml_attribute_value_chars($content);
        continue;
      }
      if (stripos($label, 'title') !== FALSE) {
        $project["name"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'shortdesc') !== FALSE) {
        $project["shortdesc"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'language') !== FALSE) {
        $project["language"] = check_plain($content);
        continue;
      }
      if (stripos($label, 'license') !== FALSE) {
        $project["license"] = _views_rdf_strip_illegal_xml_attribute_value_chars($content);
        continue;
      }
      if (stripos($label, 'repository') !== FALSE) {
        $repo = _views_rdf_strip_illegal_xml_attribute_value_chars($content);
        if (!in_array($repo, $project["repositories"])) {
          $project["repositories"][] = $repo;
        }
        continue;
      }
      if (stripos($label, 'authors') !== FALSE) {
        $dev = check_plain($content);
        if (!in_array($dev, $project["developers"])) {
          $project["developers"][] = $dev;
        }
        continue;
      }
    }
    $projects[$project["nid"]] = $project;
  }//for
  $vars["projects"] = $projects;
}
