<?php
/**
 * @file
 * Contains class definition for Dataset.
 */

/**
 * Manages dataset access and manipulation for a single dataset
 */
class Dataset {

  // Class variables.
  protected $title, $dataset;
  protected $resources = array();
  protected $created = FALSE;

  /**
   * Helper function that slugifies strings.
   */
  protected static function slugify($string) {
    return strtolower(preg_replace('/[^a-zA-Z0-9-]+/', '-', $string));
  }

  /**
   * Helper function that cast arrays as objects.
   */
  protected static function asobject($arr) {
    if(is_array($arr)){
      $arr = (object) $arr;
    }
    return $arr;
  }

  /*
   * Helper function that checks if group reference is defined
   * properly and make the necessary fixes if it's not.
   */
  protected static function populate_og_reference(&$fields){
    if(isset($fields['og_group_ref']['und'][0]) && isset($fields['og_group_ref']['und'][0]['title'])){
      // Query group by title 
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', 'node')
          ->entityCondition('bundle', 'group')
          ->propertyCondition('title', $fields['og_group_ref']['und'][0]['title'])
          ->range(0, 1);
      $result = $query->execute();
      if(isset($result['node'])){
        // If OG exists fix the field array so node_save works
        reset($result['node']);
        $fields['og_group_ref']['und'][0]['target_id'] = key($result['node']);
        unset($fields['og_group_ref']['und'][0]['title']);
      }
      else{
        // If OG does not exists remove group reference
        unset($fields['og_group_ref']);
      }
    }
  }

  /**
    * Helper function to upload the file previous to resource saving
    * Adapted from defaultcontent/plugins/file.inc
    */
  protected static function populate_resource_field_upload($resource){
    if(isset($resource->field_upload)){
      foreach($resource->field_upload as $lang => $items){
        foreach($items as $key => $item){
          $file = (object) $item;
          $query = new EntityFieldQuery();
          $fids = db_select('file_managed', 'f')
            ->condition('uri', $file->uri)
            ->fields('f', array('fid'))
            ->execute()
            ->fetchCol();
          if (!empty($fids) && isset($fids[0]) && is_object($fids[0])) {
            file_delete($fid[0]);
          }
          // Ugly hacky workaround around binary/base64 data
          // (two consecutive saves)
          $file = file_save_data($item['file'], $item['uri']);
          $data = base64_decode($item['file']);
          $file = file_save_data($data, $item['uri']);
          $file->display = $item['display'];
          $file->description = $item['description'];
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
   * Instantiate a Dataset object. Use this function instead of new Dataset($slug)
   * to ommit instantiating the same dataset twice
   */
  public static function instance($title) {
    static $datasets;
    $slug = Dataset::slugify($title);
    if (!isset($datasets[$slug])) {
      $datasets[$slug] = new Dataset($title);
    }
    return $datasets[$slug];
  }

  /**
   * Constructor. Do not call directly, but use Dataset::instance($slug) instead.
   */
  protected function __construct($title) {
    // Set our name after sanitizing it.
    $this->title = $title;
    // Try to load dataset instance from database.
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
          ->entityCondition('bundle', 'dataset')
          ->propertyCondition('title', $this->title)
          ->range(0, 1);
    $result = $query->execute();
    if(isset($result['node'])){
      $this->created = TRUE;
    }
  }

  /*
   * 
   */
  public function attachFields($fields){
    Dataset::populate_og_reference($fields);
    $this->dataset = Dataset::asobject($fields);
    // Populate basic properties
    $this->dataset->title = $this->title;
    $this->dataset->status = '1';
    $this->dataset->promote = '0';
    $this->dataset->sticky = '0';
    $this->dataset->type = 'dataset';
    $this->dataset->language = 'und';
    $this->dataset->comment = '1';
    $this->dataset->translate = '0';
  }

  public function attachResource($resource){
    $resource = Dataset::asobject($resource);
    $resource = Dataset::populate_resource_field_upload($resource);
    // Populate basic properties
    $resource->status = '1';
    $resource->promote = '0';
    $resource->sticky = '0';
    $resource->type = 'resource';
    $resource->language = 'und';
    $resource->comment = '0';
    $resource->translate = '0';
    // Attach resource
    $slug = Dataset::slugify($resource->title);
    $this->resources[$slug] = $resource;
  }

  public function save(){
    $this->dataset->title = $this->title;
    node_save($this->dataset);
    $this->dataset->field_resources = array();
    foreach($this->resources as $key => $resource){
      $this->resources[$key]->field_dataset_ref['und'][] = array(
        'target_id' => $this->dataset->nid,
        'target_type' => $this->dataset->type,
      );
      node_save($this->resources[$key]);
      $this->dataset->field_resources['und'][] = array(
        'target_id' => $this->resources[$key]->nid,
        'target_type' => $this->resources[$key]->type,
      );
    }
    if(count($this->dataset->field_resources['und'])){
      node_save($this->dataset);  
    }
  }
}
