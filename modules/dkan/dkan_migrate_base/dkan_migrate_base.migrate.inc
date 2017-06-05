<?php

/**
 * @file
 * Migration file for dkan_migrate_base.
 */

/**
 * Implements hook_migrate_api().
 */
function dkan_migrate_base_migrate_api() {
  $api = array(
    // Migrate API, not CKAN's of course.
    'api' => 2,
    'groups' => array(
      'dkan' => array(
        'title' => t('DKAN'),
      ),
    ),
    'migrations' => array(
      'datajson_dataset_base' => array(
        'class_name' => 'MigrateDataJsonDatasetBase',
        'group_name' => 'dkan',
        'title' => t('Data.json Dataset Base'),
      ),
      'ckan_dataset_base' => array(
        'class_name' => 'MigrateCkanDatasetBase',
        'group_name' => 'dkan',
        'title' => t('CKAN Dataset Base'),
      ),
      'ckan_group_base' => array(
        'class_name' => 'MigrateCkanGroupBase',
        'group_name' => 'dkan',
        'title' => t('CKAN Group Base'),
      ),
      'ckan_resource_base' => array(
        'class_name' => 'MigrateCkanResourceBase',
        'group_name' => 'dkan',
        'title' => t('CKAN Resource Base'),
      ),
    ),
  );
  return $api;
}

/**
 * Extends MigrateListJSON.
 */
class CKANListJSON extends MigrateListJSON {

  /**
   * Initialiazes.
   */
  public function __construct($list_url, $http_options = array()) {
    parent::__construct($list_url);
    $this->httpOptions = $http_options;
    $this->page = isset($http_options['page']) ? $http_options['page'] : '';
    $this->offset = isset($http_options['offset']) ? $http_options['offset'] : '';
    $this->ids = isset($http_options['ids']) ? $http_options['ids'] : '';
  }

  /**
   * The default implementation assumes the IDs are top-level array elements.
   */
  protected function getIdsFromJson(array $data) {
    $ids = array();
    $datasets = 0;
    $total = $this->page + $this->offset;
    if (isset($data['result'])) {
      foreach ($data['result'] as $item) {
        if ($datasets < $this->offset) {
          $datasets++;
          continue;
        }
        $ids[] = $item;
        $datasets++;
        if ($total && $datasets >= $total) {
          break;
        }
      }
    }
    return $ids;
  }

  /**
   * Loads the JSON at the given URL, and returns an array of the IDs.
   *
   * @return array
   *   Array of IDs.
   */
  public function getIdList() {
    migrate_instrument_start("Retrieve $this->listUrl");
    if ($this->ids) {
      return $this->ids;
    }

    if (substr($this->listUrl, 0, 6) == 'public') {
      $json = file_get_contents($this->listUrl);
    }
    elseif (!substr($this->listUrl, 0, 7) != 'http://') {
      $json = file_get_contents($this->listUrl);
    }
    else {
      $response = drupal_http_request($this->listUrl, $this->httpOptions);
      if (isset($response->error)) {
        $response = 'See ' . __FUNCTION__ . '() Error: ' . check_plain($response->error);
        watchdog('dkan_migrate_base', $response, NULL, WATCHDOG_ERROR);
      }
      else {
        $json = $response->data;
      }
    }
    migrate_instrument_stop("Retrieve $this->listUrl");
    if (isset($json)) {
      $data = drupal_json_decode($json);
      if ($data) {
        return $this->getIdsFromJson($data);
      }
    }
    Migration::displayMessage(t('Loading of !listurl failed:',
        array('!listurl' => $this->listUrl)));
    return NULL;
  }

  /**
   * Implements computeCount().
   */
  public function computeCount() {
    if ($this->ids) {
      return count($this->ids);
    }
    $count = 0;
    if (substr($this->listUrl, 0, 6) == 'public') {
      $json = file_get_contents($this->listUrl);
    }
    elseif (substr($this->listUrl, 0, 7) !== 'http://') {
      $json = file_get_contents($this->listUrl);
    }
    else {
      $response = drupal_http_request($this->listUrl, $this->httpOptions);
      if (isset($response->error)) {
        $response = 'See ' . __FUNCTION__ . '() Error: ' . check_plain($response->error);
        watchdog('dkan_migrate_base', $response, NULL, WATCHDOG_ERROR);
      }
      else {
        $json = $response->data;
      }
    }
    if (isset($json)) {
      $data = drupal_json_decode($json);
      if (!empty($data) && isset($data['result'])) {
        $count = count($data['result']);
      }
    }
    // Only return page number if that many actually exist.
    if ($this->page && $count > $this->page) {
      $count = $this->page;
    }
    return $count;
  }

}
/**
 * Extends MigrateItemJSON.
 */
class CKANItemJSON extends MigrateItemJSON {

  /**
   * Parses for 'results' instead of base.
   */
  protected function getIdsFromJson(array $data) {
    $ids = array();
    foreach ($data['result'] as $item) {
      $ids[] = $item;
    }
    return $ids;
  }

  /**
   * Parses for 'results' instead of base.
   */
  public function computeCount() {
    $count = 0;
    if (substr($this->listUrl, 0, 6) == 'public') {
      $json = file_get_contents($this->listUrl);
    }
    elseif (!substr($this->listUrl, 0, 6) == 'http://') {
      $json = file_get_contents($this->listUrl);
    }
    else {
      $response = drupal_http_request($this->listUrl, $this->httpOptions);
      $json = $response->data;
    }
    if ($json) {
      $data = drupal_json_decode($json);
      if ($data) {
        $count = count($data['result']);
      }
    }
    return $count;
  }

  /**
   * Implementors are expected to return an object representing a source item.
   */
  public function getItem($id) {
    $item_url = $this->constructItemUrl($id);
    if (filter_var($item_url, FILTER_VALIDATE_URL)) {
      // Get the JSON object at the specified URL.
      $json = $this->loadJSONUrl($item_url);
      if ($json && isset($json->result)) {
        return $json->result;
      }
      if ($json && isset($json->error) && $json->error->message == 'Access denied') {
        $migration = Migration::currentMigration();
        $message = t('Access denied for !objecturl', array('!objecturl' => $item_url));
        $migration->getMap()->saveMessage(array($id), $message, MigrationBase::MESSAGE_ERROR);
        $result = $this->emptyItem($id);
        return $result;
      }
    }
    else {
      $json = json_decode(file_get_contents($item_url));
      return $json->result;
    }
    $migration = Migration::currentMigration();
    $message = t('Loading of !objecturl failed:', array('!objecturl' => $item_url));
    $migration->getMap()->saveMessage(array($id), $message, MigrationBase::MESSAGE_ERROR);
    return new stdClass();
  }

  /**
   * Creates a stub entry.
   */
  public function emptyItem($id) {
    $result = new stdClass();
    $result->id = substr($id, 0, 35);
    $result->title = t('Access denied for %id', array('%id' => $id));
    $result->name = $id;
    return $result;
  }

}

/**
 * Put our useful functions here.
 */
abstract class MigrateDKAN extends Migration {

  /**
   * Adds default dataset mappings.
   */
  public function addDefaultDatasetMappings() {
    $this->addFieldMapping('title', 'title');
    $this->addFieldMapping('field_license', 'license_title');
    $this->addFieldMapping('created', 'metadata_created');
    $this->addFieldMapping('changed', 'metadata_modified');
    $this->addFieldMapping('field_author', 'author');
    $this->addFieldMapping('field_contact_email', 'author_email');
    $this->addFieldMapping('uid', 'uid');
    $this->addFieldMapping('id', 'uuid');
    $this->addFieldMapping('body', 'notes');
    $this->addFieldMapping('field_spatial_geographical_cover', 'spatialText');
    $this->addFieldMapping('field_spatial', 'spatial');
    $this->addFieldMapping('field_resources', 'resource_ids');
    // $this->addFieldMapping('og_group_ref', 'group_ids');.
    $this->addFieldMapping('field_tags', 'tag_names');
    $this->addFieldMapping('field_additional_info', 'field_additional_info_key');
    $this->addFieldMapping('field_additional_info:second', 'field_additional_info_value');
  }

  /**
   * Prepare() for Datasets.
   */
  public function datasetPrepare($node, $row) {
    $node->uuid = $row->id;
    // Converts geojson spatial for saving field.
    if (isset($node->field_spatial[$node->language][0]['wkt']) && $spatial = $node->field_spatial[$node->language][0]['wkt']) {
      $geophp = geophp_load();
      $geometry = geoPHP::load($spatial, 'json');
      $node->field_spatial[$node->language][0] = geofield_get_values_from_geometry($geometry);
    }
  }

  /**
   * Prepare row for Datasets.
   */
  public function datasetPrepareRow(&$row) {
    $row->uid = isset($row->creator_user_id) ? $this->getUser($row->creator_user_id) : 1;
    $row->resource_ids = array();
    // If there is no revision timestamp an error is thrown as it is the
    // highwater mark. Put in a low highwater mark instead.
    $row->revision_timestamp = isset($row->revision_timestamp) ? $row->revision_timestamp : 0;

    if (isset($row->resources)) {
      foreach ($row->resources as $resource) {
        $row->resource_ids[] = $this->getResourceId($resource->id);
      }
    }

    $tags = taxonomy_vocabulary_machine_name_load('tags');
    if (isset($row->tags)) {
      foreach ($row->tags as $tag) {
        $tag = $this->createTax($tag->name, 'tags', $tags->vid);
        $row->tag_names[] = $tag->name;
      }
    }

    // Get unix timestamp values for dates.
    $row->metadata_created = isset($row->metadata_created) ? $this->StringToTime($row->metadata_created) : '';
    $row->metadata_modified = isset($row->metadata_modified) ? $this->StringToTime($row->metadata_modified) : '';
    $row->spatialText = NULL;
    if (isset($row->extras)) {
      foreach ($row->extras as $extra) {
        if ($extra->key == 'spatial-text') {
          $row->spatialText = $extra->value;
        }
        elseif ($extra->key == 'spatial') {
          $row->spatial = $extra->value;
        }
        else {
          $row->field_additional_info_key[] = substr($extra->key, 0, 254);
          $row->field_additional_info_value[] = substr($extra->value, 0, 254);
        }
      }
    }
  }

  /**
   * Creates new Groups and adds it to the map.
   */
  public function createAndMapGroups(&$row) {
    if (isset($row->groups)) {
      if (!isset($row->group_ids)) {
        $row->group_ids = array();
      }
      foreach ($row->groups as $num => $group) {
        if ($group_nid = $this->getGroupId($group->id)) {
          $row->group_ids[] = $group_nid;
        }
        else {
          $group_node = $this->createGroupNode($group);
          // Adds the node to the map. This means that when the migration is
          // rolled back that the resources are removed as well. This adds the
          // newly created uuids and nids to the migrate_map_data_json table.
          $this->map->saveIDMapping($group_node, array($group_node->nid), 0, 0, '');
          $row->group_ids[] = $group_node->nid;
        }
      }
      $row->group_ids = array_unique($row->group_ids);
    }
  }

  /**
   * Creates new resources and adds it to the map.
   */
  public function createAndMapResources(&$row) {
    if (isset($row->resources)) {
      foreach ($row->resources as $num => $resource) {
        // Node already exists.
        if ($nid = $this->getResourceId($resource->id)) {
          $resource_node = node_load($nid);
        }
        else {
          $resource_node = $this->createResourceNode($resource);
        }
        // Adds the node to the map. This means that when the migration is
        // rolled back that the resources are removed as well. This adds the
        // newly created uuids and nids to the migrate_map_data_json table.
        $this->map->saveIDMapping($resource_node, array($resource_node->nid), 0, 0, '');
      }
    }
  }

  /**
   * Create a dkan group node from the values provided.
   *
   * @param object $group
   *   Values for the group node to be created.
   *
   * @return object
   *   Group node object.
   */
  public function createGroupNode(stdClass $group) {
    // TODO: Check high water mark.
    global $user;
    // This is necessary for group permissions. If there is not a properly
    // permissioned user, ie if done from the command line, the group creation
    // fails.
    $user = user_load(1);
    $node = new stdClass();
    $node->is_new = TRUE;
    $node->language = 'und';
    $node->uid = $user->uid ? $user->uid : 1;
    $node->type = 'group';
    $node->title = $group->title;
    $node->body['und'][0]['safe_value'] = isset($group->description) ? $group->description : '';
    if (isset($group->image_display_url) && $group->image_display_url) {
      $name = explode('/', $group->image_display_url->url);
      $name = $name[count($name) - 1];
      $uri = 'public://' . $name;
      $file = $this->downloadExternalFile($group->image_display_url, $uri);
      $node->field_image['und'][0] = $file;
    }
    $node->uuid = isset($group->id) ? $group->id : '';
    node_save($node);
    return $node;
  }

  /**
   * Create the term ID.
   */
  public function createFormatTid($format) {
    $format_vocab = taxonomy_vocabulary_machine_name_load('format');
    $this->createTax($format, 'format', $format_vocab->vid);
    $tax = taxonomy_get_term_by_name($format, 'format', $format_vocab->vid);
    $tid = array_keys($tax);
    return $tid[0];
  }

  /**
   * Create a dkan resource node from the values provided.
   *
   * @param object $res
   *   Values for the resources node to be created.
   *        Formated with the self::prepareResourceHelper() method.
   *
   * @return object
   *   Resource node object.
   */
  public function createResourceNode(stdClass $res) {

    // TODO: Check high water mark.
    // TODO: Check if UUID exits.
    // Creates a new resource for every linked file.
    // Linked files contain title, format, and accessURL.
    global $user;
    $node = new stdClass();
    $node->is_new = TRUE;
    $node->language = 'und';
    $node->uid = $user->uid ? $user->uid : 1;
    $node->type = 'resource';
    $node->title = $res->name ? $res->name : $res->url;
    $node->uuid = $res->id;
    $node->body = $res->description;
    $res->format = strtolower($res->format);
    $node->changed = $res->created;
    if (isset($res->format)) {
      $node->field_format['und'][0]['tid'] = $this->createFormatTid($res->format);
    }

    // Create a link to file for certain formats.
    if ($res->format == 'csv' || $res->format == 'zip') {
      $file = remote_stream_wrapper_file_load_by_uri($res->url);
      if (!$file) {
        $req = drupal_http_request($res->url, array('timeout' => 5));
        if ($req->code == 200) {
          $mime = explode(";", $req->headers['content-type'])[0];
          if ($type = recline_get_data_type($mime)) {
            $link_field = field_info_instance('node', 'field_link_remote_file', 'resource');
            $ext = explode(' ', $link_field['settings']['file_extensions']);
            if (in_array($type, $ext) && isset($req->headers['content-length'])
              // Html can be listed as a file type for the remote file field but
              // shouldn't be imported.
              && $type != 'html') {
              if ($fid = $this->checkExistingFile($res->url)) {
                $file = file_load($fid);
              }
              else {
                $file = remote_stream_wrapper_file_create_by_uri($res->url);
                $file->status = FALSE;
                file_save($file);
              }
            }
          }
        }
      }
      if ($file) {
        $node->field_link_remote_file['und'][0]['fid'] = $file->fid;
        $node->field_link_remote_file['und'][0]['display'] = 1;
      }
    }
    else {
      $req = drupal_http_request($res->url, array('timeout' => 5));
      if ($req->code == 200) {
        $mime = substr($req->headers['content-type'], 0, strpos($req->headers['content-type'], ";"));
        if ($type = recline_get_data_type($mime)) {
          $node->field_format['und'][0]['tid'] = $this->createFormatTid($type);
          $link_field = field_info_instance('node', 'field_link_remote_file', 'resource');
          $ext = explode(' ', $link_field['settings']['file_extensions']);
          // Html can be listed as a file type for the remote file field but
          // shouldn't be imported.
          if (in_array($type, $ext) && isset($req->headers['content-length']) && $type != 'html') {
            $file = remote_stream_wrapper_file_load_by_uri($res->url);
            if (!$file) {
              $file = remote_stream_wrapper_file_create_by_uri($res->url);
              $file->status = FALSE;
              if (isset($req->headers['content-disposition']) && $req->headers['content-disposition'] && $cd = explode('; ', $req->headers['content-disposition'])) {
                if (is_array($cd)) {
                  foreach ($cd as $item) {
                    if ($name = str_replace('filename=', '', $item)) {
                      $file->filename = $name;
                    }
                  }
                }
              }
              file_save($file);
            }
            $node->field_link_remote_file['und'][0]['fid'] = $file->fid;
            $node->field_link_remote_file['und'][0]['display'] = 1;
          }
        }
      }
    }
    if (!isset($node->field_link_remote_file['und'][0]['fid'])) {
      // Link to API for all others.
      $node->field_link_api['und'][0]['url'] = $res->url;
    }
    if (isset($res->api) && $res->api) {
      // Link to API for all others.
      $node->field_link_api['und'][0]['url'] = $res->api;
    }
    node_save($node);
    return $node;
  }

  /**
   * Checks file_managed for existing URI and returns fid if present.
   */
  public function checkExistingFile($uri) {
    if ($fid = db_query("SELECT fid from {file_managed} WHERE uri = :uri", array(":uri" => $uri))->fetchField()) {
      return $fid;
    }
    return FALSE;
  }

  /**
   * Creates list of fields for CKAN Dataset.
   */
  public function getCkanDatasetFields() {
    return array(
      "license_title" => "License Title",
      "maintainer" => "Maintaier",
      "relationships_as_object" => "",
      "private" => "Private",
      "maintainer_email" => "",
      "revision_timestamp" => "Revision Date",
      "id" => "UUID",
      "metadata_created" => "Created Date",
      "metadata_modified" => "Modified Date",
      "author" => "Author",
      "author_email" => "Author Email",
      "state" => "State",
      "version" => "Version",
      "creator_user_id" => "Author UUID",
      "type" => "Node Type",
      "resources" => "Resources",
      "num_resources" => "Number of Resources",
      "tag_names" => "Tags",
      "tracking_summary" => "Tracking Summary",
      "groups" => "Groups",
      "license_id" => "Licence ID",
      "relationships_as_subject" => "",
      "num_tags" => "Number of Tags",
      "organization" => "Organization",
      "name" => "Name slug",
      "isopen" => "Is Open (bollean)",
      "url" => "URL",
      "notes" => "Description",
      "owner_org" => "Owner Organization",
      "extras" => "Extras",
      "title" => "Title",
      "revision_id" => "Revision ID",
      // This doesn't actually exist but we are adding it later in prepareRow.
      "uid" => "User ID",
      "resource_ids" => "Resource IDS",
      "group_ids" => "Group IDS",
    );
  }

  /**
   * Looks up format if exists, if not creates it.
   *
   * @return object
   *   Format of the file.
   */
  public function createTax($name, $vocab_name, $vid) {
    if ($term = taxonomy_get_term_by_name($name, $vocab_name, $vid)) {
      $term = array_pop($term);
      return $term;
    }
    else {
      $new_term = new stdClass();
      $new_term->name = $name;
      $new_term->vid = $vid;
      $new_term->path['pathauto'] = FALSE;
      $term = taxonomy_term_save($new_term);
      if ($term == SAVED_NEW) {
        $term = taxonomy_get_term_by_name($name, $vocab_name);
        $term = array_pop($term);
        return $term;
      }
      else {
        return NULL;
      }
    }
  }

  /**
   * Converts times with edge cases.
   */
  public function stringToTime($date) {
    if ($date = str_replace('-', '', $date)) {
      return strtotime($date);
    }
    else {
      return strtotime($date);
    }
  }

  /**
   * Gets Group Nid by title if exists.
   */
  public function getGroupNidByTitle($title) {
    $type = 'group';
    $result = db_query("SELECT n.nid FROM {node} n WHERE n.title = :title AND n.type = :type", array(":title" => $title, ":type" => $type));
    $nid = $result->fetchField();
    if ($nid) {
      return $nid;
    }
    else {
      return NULL;
    }
  }

  /**
   * Looks up nid for resource.
   */
  public function getResourceId($uuid) {
    if ($nid = entity_get_id_by_uuid('node', array($uuid))) {
      return $nid[$uuid];
    }
  }

  /**
   * Looks up nid for group.
   */
  public function getGroupId($uuid) {
    if ($nid = entity_get_id_by_uuid('node', array($uuid))) {
      return $nid[$uuid];
    }
  }

  /**
   * Looks up user if they exist, if not creates them.
   *
   * @param string $creator_user_id
   *   UUID for user from CKAN.
   *
   * @return string
   *   Drupal user_id (uid).
   */
  public function getUser($creator_user_id) {
    if (isset($creator_user_id) && $creator_user_id) {
      if ($uid = entity_get_id_by_uuid('user', array($creator_user_id))) {
        return $uid[$creator_user_id];
      }
      else {
        $new_user = array(
          'uuid' => $creator_user_id,
        );
        // Get User name from CKAN API.
        $response = drupal_http_request($this->endpoint . 'user_show?id=' . $creator_user_id);
        $json = $response->data;
        $data = drupal_json_decode($json);
        if ($name = $data['result']['name']) {
          $current_uid = db_query("SELECT uid from {users} WHERE name = :name", array(":name" => $name))->fetchField();
          if ($current_uid) {
            $account = user_load($current_uid);
            return $account->uid;
          }
          else {
            $new_user['name'] = $name;
            $account = user_save(NULL, $new_user);
            return $account->uid;
          }
        }
        else {
          return 1;
        }
      }
    }
  }

  /**
   * Saves file locally.
   *
   * @param string $url
   *   Full url to file to download.
   * @param string $uri
   *   Drupal uri of where to save file public://archive/test.pdf.
   * @param int $save_mode
   *   File save mode from drupal core, ex FILE_EXISTS_REPLACE.
   * @param bool $manage_file
   *   Set to FALSE to save the file as an unmanaged file.
   */
  public function downloadExternalFile($url, $uri, $save_mode = FILE_EXISTS_RENAME, $manage_file = TRUE) {

    $url_info = parse_url($url);
    $url_path_info = pathinfo($url_info['path']);

    // This helps with filenames with spaces.
    $url = $url_info['scheme'] . '://' . $url_info['host'] . $url_path_info['dirname'] . '/' . rawurlencode($url_path_info['basename']);

    // Need to remove the filename from the uri.
    $uri_target = file_uri_target($uri);
    $uri_scheme = file_uri_scheme($uri);
    $uri_path_info = pathinfo($uri_target);
    $directory = file_stream_wrapper_uri_normalize($uri_scheme . "://" . $uri_path_info['dirname']);

    if (file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
      $drupal_result = drupal_http_request($url);
      if (!empty($drupal_result->data)) {
        $path = file_stream_wrapper_uri_normalize($uri);
        if ($manage_file) {
          $new_file = file_save_data($drupal_result->data, $path, $save_mode);
        }
        else {
          return file_unmanaged_save_data($drupal_result->data, $path, $save_mode);
        }
      }
      else {
        drupal_set_message("Error downloading file, no data recieved for " . $url);
        return FALSE;
      }
      $new_file->display = 1;
      return (array) $new_file;
    }
    else {
      drupal_set_message(t("Could not create directory"));
    }
  }

}
/**
 * Extends MigrateDKAN.
 */
abstract class MigrateCkanBase extends MigrateDKAN {

  /**
   * Override MigrateDKAN.
   */
  public function prepare($node, $row) {
    $node->path = is_array($node->path) ? $node->path : array();
  }

  /**
   * Construct endpoint.
   */
  public function __construct($arguments) {
    $this->endpoint = isset($arguments['endpoint']) ? $arguments['endpoint'] : 'http://demo.getdkan.com/api/3/action/';
    parent::__construct($arguments);
  }

}
