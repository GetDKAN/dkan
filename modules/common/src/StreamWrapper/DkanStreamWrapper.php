<?php

namespace Drupal\common\StreamWrapper;

use Drupal\Core\StreamWrapper\LocalReadOnlyStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\UnroutedUrlAssembler;

/**
 * [Description DkanStreamWrapper]
 */
class DkanStreamWrapper extends LocalReadOnlyStream implements StreamWrapperInterface {

  const DKAN_API_VERSION = 1;
  
  public function getName() {
    return t('DKAN documents');
  }

  public function getDescription() {
    return t('Simple way to request DKAN schemas and other documents as URIs.');
  }

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::HIDDEN;
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $url = Url::fromUri("internal:/api/1/" . $this->getTarget(), ['absolute' => true]);
    return $url->toString();
  }

  public function getDirectoryPath() {
    $url = Url::fromUri("internal:/api/1/", ['absolute' => true]);
    return $url->toString();
  }

  public function dir_readdir() {
    return FALSE;
  }

  public function dir_rewinddir() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_open($path, $mode, $options, &$opened_path) {
    $allowed_modes = array('r', 'rb');
    if (!in_array($mode, $allowed_modes)) {
      return FALSE;
    }
    $this->uri = $path;
    $url = $this->getExternalUrl();
    $this->handle = ($options && STREAM_REPORT_ERRORS) ? fopen($url, $mode) : @fopen($url, $mode);
    return (bool) $this->handle;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_eof() {
    return feof($this->handle);
  }

  public function stream_stat() {
    return FALSE;
  }

  public function stream_read($count) {
    return fread($this->handle, $count);
  }

}