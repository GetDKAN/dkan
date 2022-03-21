<?php

namespace Drupal\common\StreamWrapper;

use Drupal\Core\StreamWrapper\LocalReadOnlyStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * DKAN stream wrapper for creating domain-agnostic URLs to DKAN API endpoints.
 */
class DkanStreamWrapper extends LocalReadOnlyStream implements StreamWrapperInterface {

  use StringTranslationTrait;

  const DKAN_API_VERSION = 1;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->t('DKAN documents');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Simple way to request DKAN schemas and other documents as URIs.');
  }

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::READ_VISIBLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $url = Url::fromUserInput("/api/1/" . $this->getTarget(), ['absolute' => TRUE]);
    return $url->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    $url = Url::fromUserInput("/api/1/", ['absolute' => TRUE]);
    return $url->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function stream_open($path, $mode, $options, &$opened_path) {
    $allowed_modes = ['r', 'rb'];
    if (!in_array($mode, $allowed_modes)) {
      return FALSE;
    }
    $this->uri = $path;
    $url = $this->getExternalUrl();
    $this->handle = ($options && STREAM_REPORT_ERRORS) ? fopen($url, $mode) : @fopen($url, $mode);
    return (bool) $this->handle;
  }

}
