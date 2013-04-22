<?php

/**
 * @file
 * Home of the FeedsHTTPFetcher and related classes.
 */

feeds_include_library('PuSHSubscriber.inc', 'PuSHSubscriber');

/**
 * Fetches data via HTTP.
 */
class FeedsLinkFieldFetcher extends FeedsHTTPFetcher {

  /**
   * Implements FeedsFetcher::fetch().
   */
  public function fetch(FeedsSource $source) {
    $source_config = $source->getConfigFor($this);
    // Link fields can be empty. Don't process if there is no value.
    if ($source_config['source']) {
      if ($this->config['use_pubsubhubbub'] && ($raw = $this->subscriber($source->feed_nid)->receive())) {
        return new FeedsFetcherResult($raw);
      }
      $fetcher_result = new FeedsHTTPFetcherResult($source_config['source']);
      // When request_timeout is empty, the global value is used.
      $fetcher_result->setTimeout($this->config['request_timeout']);
      return $fetcher_result;
    }
    else {
      throw new Exception(t('Link field it empty.'));
    }
  }

  /**
   * Finds url based on field. Assumes that this is a link field.
   */
  public function getURL($field, $nid) {
    if (db_table_exists('field_data_' . $field)) {
      $table = '{field_data_' . $field . '}';
      $id = $field . '_url';
      $url = db_query("SELECT $id FROM $table WHERE entity_id = :feed_nid", array(':feed_nid' => $nid))->fetchField();
      return $url;
    }
  }

  /**
   * Override parent::configDefaults().
   */
  public function configDefaults() {
    return array(
      'link_field' => FALSE,
      'auto_detect_feeds' => FALSE,
      'use_pubsubhubbub' => FALSE,
      'designated_hub' => '',
      'request_timeout' => NULL,
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
        '' => t('- Select a link field -'),
      );
      foreach ($fields as $field_id => $field_info) {
        $options[$field_id] = $field_info['label'];
      }
      $form['link_field'] = array(
        '#type' => 'select',
        '#options' => $options,
        '#default_value' => $this->config['link_field'],
        '#title' => t('Link Field'),
        '#description' => t('Select which link field to use as the source of your data. WARNING: this must be a link field or derivative that stores the fid in its field table.'),
      );
      $form['auto_detect_feeds'] = array(
        '#type' => 'checkbox',
        '#title' => t('Auto detect feeds'),
        '#description' => t('If the supplied URL does not point to a feed but an HTML document, attempt to extract a feed URL from the document.'),
        '#default_value' => $this->config['auto_detect_feeds'],
      );
      $form['use_pubsubhubbub'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use PubSubHubbub'),
        '#description' => t('Attempt to use a <a href="http://en.wikipedia.org/wiki/PubSubHubbub">PubSubHubbub</a> subscription if available.'),
        '#default_value' => $this->config['use_pubsubhubbub'],
      );
      $form['designated_hub'] = array(
        '#type' => 'textfield',
        '#title' => t('Designated hub'),
        '#description' => t('Enter the URL of a designated PubSubHubbub hub (e. g. superfeedr.com). If given, this hub will be used instead of the hub specified in the actual feed.'),
        '#default_value' => $this->config['designated_hub'],
        '#dependency' => array(
          'edit-use-pubsubhubbub' => array(1),
        ),
      );
      // Per importer override of global http request timeout setting.
      $form['request_timeout'] = array(
        '#type' => 'textfield',
        '#title' => t('Request timeout'),
        '#description' => t('Timeout in seconds to wait for an HTTP get request to finish.
                            Note: this setting will override the global setting.
                            When left empty, the global value is used.'),
        '#default_value' => $this->config['request_timeout'],
        '#element_validate' => array('element_validate_integer_positive'),
        '#maxlength' => 3,
        '#size' => 30,
      );
    }
    else {
      $form['no_content_type'] = array(
        '#markup' => t('You must select a content type in Basic Settings.'),
      );
    }
    return $form;
  }

  /**
   * Expose source form.
   */
  public function sourceForm($source_config) {
    // Remove URL form from inherited HTTPFetcther.
    $form = array();
    return $form;
  }

  /**
   * Override parent::sourceFormValidate().
   */
  public function sourceFormValidate(&$values) {
    // Remove validation for URL form from inherited HTTPFetcther.
  }

  /**
   * Override sourceSave() - subscribe to hub.
   */
  public function sourceSave(FeedsSource $source) {
    $source_config = $source->getConfigFor($this);
    $field = $this->config['link_field'];
    $source_config['source'] = $this->getURL($field, $source->feed_nid);
    $source->setConfigFor($this, $source_config);
    if ($this->config['use_pubsubhubbub']) {
      // If this is a feeds node we want to delay the subscription to
      // feeds_exit() to avoid transaction race conditions.
      if ($source->feed_nid) {
        $job = array('fetcher' => $this, 'source' => $source);
        feeds_set_subscription_job($job);
      }
      else {
        $this->subscribe($source);
      }
    }
  }

}
