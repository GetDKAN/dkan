<?php

namespace Drupal\common\FileFetcher;

use FileFetcher\FileFetcher;
use FileFetcher\Processor\Remote;

/**
 * Our FileFetcher clone.
 *
 * This is mostly the same as \FileFetcher\FileFetcher, except we hijack the
 * processor using Drupal configuration for
 * always_use_existing_local_perspective.
 */
class DkanFileFetcher extends FileFetcher {

  /**
   * Tell this file fetcher whether to use local files if they exist.
   *
   * @param bool $use_local_file
   *   (optional) Whether to use the local file. If TRUE, we'll use the file
   *   processor that prefers to use local files. Defaults to TRUE.
   *
   * @return self
   *   Fluent interface.
   *
   * @see https://dkan.readthedocs.io/en/2.x/user-guide/guide_local_files.html
   */
  public function setAlwaysUseExistingLocalPerspective(bool $use_local_file = TRUE) : self {
    if ($use_local_file) {
      $this->setProcessors(['processors' => [FileFetcherRemoteUseExisting::class]]);
      $this->setStateProperty('processor', FileFetcherRemoteUseExisting::class);
    }
    else {
      $this->setProcessors(['processors' => []]);
      $this->setStateProperty('processor', Remote::class);
    }
    return $this;
  }

}
