<?php

/**
 * @file
 * Home of the FeedsFileFetcher and related classes.
 */

/**
 * Fetches data via file field attached to an entity.
 */
class EntityFieldFetcher extends FeedsFetcher {

  /**
   * Implements FeedsFetcher::fetch().
   */
  public function fetch(FeedsSource $source) {
    $source_config = $source->getConfigFor($this);
    $field = $this->config['file_field'];

    if (db_table_exists('field_data_' . $field )) {
      $table = '{field_data_' . $field . '}';
      $id = $field . '_fid';
      $fid = db_query("SELECT $id FROM $table WHERE entity_id = :feed_nid", array(':feed_nid' => $source->feed_nid))->fetchField();
      if ($fid) {
        $file = file_load($fid);
        return new FeedsFileFetcherResult($file->uri);

      }
    }

    // Just return a file fetcher result if this is a file.
    if (is_file($source_config['source'])) {
      return new FeedsFileFetcherResult($source_config['source']);
    }

    // Batch if this is a directory.
    $state = $source->state(FEEDS_FETCH);
    $files = array();
    if (!isset($state->files)) {
      $state->files = $this->listFiles($source_config['source']);
      $state->total = count($state->files);
    }
    if (count($state->files)) {
      $file = array_shift($state->files);
      $state->progress($state->total, $state->total - count($state->files));
      return new FeedsFileFetcherResult($file);
    }

    throw new Exception(t('Resource is not a file or it is an empty directory: %source', array('%source' => $source_config['source'])));
  }

  /**
   * Return an array of files in a directory.
   *
   * @param $dir
   *   A stream wreapper URI that is a directory.
   *
   * @return
   *   An array of stream wrapper URIs pointing to files. The array is empty
   *   if no files could be found. Never contains directories.
   */
  protected function listFiles($dir) {
    $dir = file_stream_wrapper_uri_normalize($dir);
    $files = array();
    if ($items = @scandir($dir)) {
      foreach ($items as $item) {
        if (is_file("$dir/$item") && strpos($item, '.') !== 0) {
          $files[] = "$dir/$item";
        }
      }
    }
    return $files;
  }

  /**
   * Override parent::sourceFormValidate().
   */
  public function sourceFormValidate(&$values) {
    $values['source'] = trim($values['source']);

    $feed_dir = 'public://feeds';
    file_prepare_directory($feed_dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    // If there is a file uploaded, save it, otherwise validate input on
    // file.
    // @todo: Track usage of file, remove file when removing source.
    if ($file = file_save_upload('feeds', array('file_validate_extensions' => array(0 => $this->config['allowed_extensions'])), $feed_dir)) {
      $values['source'] = $file->uri;
      $values['file'] = $file;
    }
    elseif (empty($values['source'])) {
      form_set_error('feeds][source', t('Upload a file first.'));
    }
    // If a file has not been uploaded and $values['source'] is not empty, make
    // sure that this file is within Drupal's files directory as otherwise
    // potentially any file that the web server has access to could be exposed.
    elseif (strpos($values['source'], file_default_scheme()) !== 0) {
      form_set_error('feeds][source', t('File needs to reside within the site\'s file directory, its path needs to start with @scheme://.', array('@scheme' => file_default_scheme())));
    }
  }

  /**
   * Override parent::sourceSave().
   */
  public function sourceSave(FeedsSource $source) {
    $source_config = $source->getConfigFor($this);

    // Need to look if file is present, then add to source?
    //

    // If a new file is present, delete the old one and replace it with the new
    // one.
    if (isset($source_config['file'])) {
      $file = $source_config['file'];
      if (isset($source_config['fid'])) {
        $this->deleteFile($source_config['fid'], $source->feed_nid);
      }
      $file->status = FILE_STATUS_PERMANENT;
      file_save($file);
      file_usage_add($file, 'feeds', get_class($this), $source->feed_nid);

      $source_config['fid'] = $file->fid;
      unset($source_config['file']);
      $source->setConfigFor($this, $source_config);
    }
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
        '#description' => t('Select which file field to use as the source of your data.'),
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
