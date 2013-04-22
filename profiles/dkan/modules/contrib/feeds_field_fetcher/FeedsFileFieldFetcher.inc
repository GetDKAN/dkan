<?php

/**
 * @file
 * Home of the FeedsFileFetcher and related classes.
 */

/**
 * Fetches data via file field attached to an entity.
 */
class FeedsFileFieldFetcher extends FeedsFetcher {

  /**
   * Implements FeedsFetcher::fetch().
   */
  public function fetch(FeedsSource $source) {
    $source_config = $source->getConfigFor($this);
    $field = $this->config['file_field'];

    if ($source_config['source']) {
      // Just return a file fetcher result if this is a file.
      if (is_file($source_config['source'])) {
        return new FeedsFileFetcherResult($source_config['source']);
      }
      throw new Exception(t('Resource is not a file or it is an empty directory: %source', array('%source' => $source_config['source'])));
    }
    else {
      throw new Exception(t('File field it empty.'));
    }
  }

  /**
   * Finds fid based on field. Assumes that this is a file field.
   */
  public function getFid($field, $nid) {
    if (db_table_exists('field_data_' . $field)) {
      $table = '{field_data_' . $field . '}';
      $id = $field . '_fid';
      $fid = db_query("SELECT $id FROM $table WHERE entity_id = :feed_nid", array(':feed_nid' => $nid))->fetchField();
      return $fid;
    }
  }

  /**
   * Override parent::sourceSave().
   */
  public function sourceSave(FeedsSource $source) {
    $source_config = $source->getConfigFor($this);
    $field = $this->config['file_field'];
    $source_config['fid'] = $this->getFid($field, $source->feed_nid);
    if ($source_config['fid']) {
      $file = file_load($source_config['fid']);
      $source_config['source'] = $file->uri;
    }
    else {
      $source_config['source'] = FALSE;
    }
    $source->setConfigFor($this, $source_config);
  }

  /**
   * Override parent::configDefaults().
   */
  public function configDefaults() {
    return array(
      'file_field' => FALSE,
    );
  }

  /**
   * Override parent::configForm().
   */
  public function configForm(&$form_state) {
    $form = array();
    if ($content_type = feeds_importer($this->id)->config['content_type']) {
      $fields = field_info_instances('node', $content_type);
      $options = array(
        '' => t('- Select a file field -'),
      );
      foreach ($fields as $field_id => $field_info) {
        $options[$field_id] = $field_info['label'];
      }
      $form['file_field'] = array(
        '#type' => 'select',
        '#options' => $options,
        '#default_value' => $this->config['file_field'],
        '#title' => t('File Field'),
        '#description' => t('Select which file field to use as the source of your data. WARNING: this must be a file field or derivative that stores the fid in its field table.'),
      );
    }
    else {
      $form['no_content_type'] = array(
        '#markup' => t('You must select a content type in Basic Settings.'),
      );
    }
    return $form;
  }

}
