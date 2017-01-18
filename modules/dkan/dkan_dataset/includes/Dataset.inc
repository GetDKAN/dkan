<?php

/**
 * @file
 * Contains class definition for Dataset.
 */

/**
 * Manages dataset access and manipulation for a single dataset.
 */
class Dataset {
  public $title;
  public $dataset;
  public $resources = array();
  public $created = FALSE;
  protected $datasetUpdated = FALSE;
  protected $resourcesUpdated = FALSE;

  /**
   * Helper function that slugifies strings.
   *
   * @param string $string
   *   The string to be slugified.
   */
  protected static function slugify($string) {
    return strtolower(preg_replace('/[^a-zA-Z0-9-]+/', '-', $string));
  }

  /**
   * Helper function that cast arrays as objects.
   *
   * @param array $arr
   *   An array to be cast as an object.
   */
  protected static function asobject($arr) {
    if (is_array($arr)) {
      $arr = (object) $arr;
    }
    return $arr;
  }

  /**
   * Helper function that checks if group reference is defined.
   *
   * @param array $fields
   *   An array of dataset fields.
   */
  protected static function populateOgReference(&$fields) {
    if (isset($fields['og_group_ref'][LANGUAGE_NONE][0]) && isset($fields['og_group_ref'][LANGUAGE_NONE][0]['title'])) {
      // Query group by title.
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', 'node')
        ->entityCondition('bundle', 'group')
        ->propertyCondition('title', $fields['og_group_ref'][LANGUAGE_NONE][0]['title'])
        ->range(0, 1);
      $result = $query->execute();
      if (isset($result['node'])) {
        // If OG exists fix the field array so node_save works.
        reset($result['node']);
        $fields['og_group_ref'][LANGUAGE_NONE][0]['target_id'] = key($result['node']);
        unset($fields['og_group_ref'][LANGUAGE_NONE][0]['title']);
      }
      else {
        // If OG does not exists remove group reference.
        unset($fields['og_group_ref']);
      }
    }
  }

  /**
   * Uploads the file previous to resource saving.
   *
   * Adapted from defaultcontent/plugins/file.inc.
   *
   * @param object $resource
   *   An object that represents a file field.
   */
  protected static function populateResourceFieldUpload($resource) {
    if (isset($resource->field_upload)) {
      foreach ($resource->field_upload as $lang => $items) {
        foreach ($items as $key => $item) {
          $file = (object) $item;
          $fids = db_select('file_managed', 'f')
            ->condition('uri', $file->uri)
            ->fields('f', array('fid'))
            ->execute()
            ->fetchCol();
          if (!empty($fids) && isset($fids[0]) && is_object($fids[0])) {
            file_delete($fid[0]);
          }
          // Ugly hacky workaround around binary/base64 data
          // (two consecutive saves).
          $file = file_save_data($item['file'], $item['uri']);
          $data = base64_decode($item['file']);
          $file = file_save_data($data, $item['uri']);
          $file->display = isset($item['display']) ? $item['display'] : NULL;
          $file->description = isset($item['description']) ? $item['description'] : NULL;
          $file->map = $item['map'];
          $file->grid = $item['grid'];
          $file->graph = $item['graph'];
          $file->timeline = $item['timeline'];
          $file->transform = $item['transform'];
          file_save($file);
          $resource->field_upload[$lang][$key] = (array) $file;
        }
      }
    }
    return $resource;
  }

  /**
   * Instantiates a Dataset object.
   *
   * Use this function instead of new Dataset($slug)
   * to ommit instantiating the same dataset twice.
   *
   * @param string $title_or_nid
   *   A string that represents one of the following:
   *   - the node dataset title
   *   - the node id.
   */
  public static function instance($title_or_nid) {
    static $datasets;
    // print_r($title_or_nid)
    if (!is_numeric($title_or_nid)) {
      $title = $title_or_nid;
      $slug = Dataset::slugify($title);
      $dataset = new Dataset($title);
    }
    else {
      $nid = $title_or_nid;
      $dataset = new Dataset($nid);
      $slug = $dataset->created ? Dataset::slugify($dataset->title) : FALSE;
    }
    if ($slug) {
      if (!isset($datasets[$slug])) {
        $datasets[$slug] = $dataset;
      }
      return $datasets[$slug];
    }
    return $slug;
  }

  /**
   * Preloads dataset based on dataset nid.
   *
   * @param string $nid
   *   A string that represents the dataset node id.
   */
  protected function preload($nid) {
    $node = node_load($nid);
    if ($node) {
      $this->title = $node->title;
      $this->dataset = $node;
      // Removed unset($this->dataset->field_resources);.
      $this->created = TRUE;
    }
  }

  /**
   * Preloads Dataset's Resources.
   */
  protected function preloadResources() {
    if ($this->created) {
      $this->resources = array();
      // Load resources.
      foreach ($this->dataset->field_resources[$this->dataset->language] as $key => $resource) {
        $this->resources[] = node_load($resource['target_id']);
      }
    }
  }

  /**
   * Constructor.
   *
   * Do not call directly, but use Dataset::instance($title_or_nid) instead.
   *
   * @param string $title_or_nid
   *   A string that represents one of the following:
   *   - the node dataset title
   *   - the node id.
   */
  protected function __construct($title_or_nid) {
    // Set our name after sanitizing it.
    if (!is_numeric($title_or_nid)) {
      $this->title = $title_or_nid;
      // Try to load dataset instance from database.
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', 'node')
        ->entityCondition('bundle', 'dataset')
        ->propertyCondition('title', $this->title)
        ->range(0, 1);
      $result = $query->execute();
      if (isset($result['node'])) {
        $this->preload($result['node']);
      }
    }
    else {
      $this->preload($title_or_nid);
    }
  }

  /**
   * Increments revision for the dataset and sets a log for the resource.
   *
   * @param string $log
   *   A string to be used as the log message for the revision instance.
   */
  public function incrementRevision($log) {
    if (isset($this->dataset->nid)) {
      $this->dataset->revision = 1;
      $this->dataset->log = $log;
      $this->datasetUpdated = TRUE;
    }
  }

  /**
   * Populates dataset fields.
   *
   * @param array $fields
   *   An associative array containing the dataset node fields.
   */
  public function attachFields($fields) {
    Dataset::populateOgReference($fields);
    $this->dataset = Dataset::asobject($fields);
    // Populate basic properties.
    $this->dataset->title = $this->title;
    $this->dataset->status = '1';
    $this->dataset->promote = '0';
    $this->dataset->sticky = '0';
    $this->dataset->type = 'dataset';
    $this->dataset->language = LANGUAGE_NONE;
    $this->dataset->comment = '1';
    $this->dataset->translate = '0';
    $this->datasetUpdated = TRUE;
  }

  /**
   * Populates resource fields.
   *
   * @param array $resource
   *   An associative array containing the resource node fields.
   */
  public function attachResource($resource) {
    $resource = Dataset::asobject($resource);
    $resource = Dataset::populateResourceFieldUpload($resource);
    // Populate basic properties.
    $resource->status = '1';
    $resource->promote = '0';
    $resource->sticky = '0';
    $resource->type = 'resource';
    $resource->language = LANGUAGE_NONE;
    $resource->comment = '0';
    $resource->translate = '0';
    // Attach resource.
    $slug = Dataset::slugify($resource->title);
    $this->resources[$slug] = $resource;
    $this->resourcesUpdated = TRUE;
  }

  /**
   * Save the populated datasets and resources.
   */
  public function save() {
    if ($this->datasetUpdated) {
      $this->dataset->title = $this->title;
      node_save($this->dataset);
      $this->datasetUpdated = FALSE;
      $this->created = TRUE;
    }
    if ($this->resourcesUpdated) {
      $this->dataset->field_resources = array();
      foreach ($this->resources as $key => $resource) {
        $this->resources[$key]->field_dataset_ref[LANGUAGE_NONE][] = array(
          'target_id' => $this->dataset->nid,
          'target_type' => $this->dataset->type,
        );
        node_save($this->resources[$key]);
        $this->dataset->field_resources[LANGUAGE_NONE][] = array(
          'target_id' => $this->resources[$key]->nid,
          'target_type' => $this->resources[$key]->type,
        );
      }
      if (count($this->dataset->field_resources[LANGUAGE_NONE])) {
        node_save($this->dataset);
      }
      $this->resourcesUpdated = FALSE;
    }
  }

  /**
   * Delete the populated dataset and resources.
   *
   * @param bool $delete_resources
   *   Boolean to indicate if resources should be deleted.
   */
  public function delete($delete_resources = FALSE) {
    if ($this->created) {
      $to_delete = array();
      $to_delete[] = $this->dataset->nid;
      if ($delete_resources) {
        foreach ($this->resources as $key => $resource) {
          $to_delete[] = $resource->nid;
        }
      }
      node_delete_multiple($to_delete);
    }
    return count(node_load_multiple($to_delete)) === 0 ? TRUE : FALSE;
  }

}
