<?php

namespace Drupal\common\FileFetcher;

use FileFetcher\FileFetcher;

/**
 * Allows FileFetcher to be reconfigured for using existing local files.
 *
 * This DKAN-specific extension of the FileFetcher (which comes from an
 * external library) applies the DKAN configuration
 * common.settings.always_use_existing_local_perspective
 * when selecting the processor. The configuration itself is retrieved
 * in FileFetcherFactory and passed to DkanFileFetcher on getInstance().
 */
class DkanFileFetcher extends FileFetcher {

  /**
   * Tell this file fetcher whether to use local files if they exist.
   *
   * @param bool $use_localized_file
   *   (optional) Whether to use the localized file. If TRUE, we'll use the file
   *   processor that prefers to use localized files. Defaults to TRUE.
   *
   * @return self
   *   Fluent interface.
   *
   * @see https://dkan.readthedocs.io/en/2.x/user-guide/guide_local_files.html
   */
  public function setAlwaysUseExistingLocalPerspective(bool $use_localized_file = TRUE) : self {
    if ($use_localized_file) {
      $this->addProcessors(['processors' => [FileFetcherRemoteUseExisting::class]]);
    }
    else {
      $this->dkanUseDefaultFileProcessor();
    }
    return $this;
  }

  /**
   * Configure the processor to use its default behavior.
   */
  protected function dkanUseDefaultFileProcessor() {
    // In FileFetcher 5.0.2+, we have the unsetDuplicateCustomProcessorClasses
    // method, but it's private. In PR #4074 we formalize the custom processor
    // API for DKAN, which removes this class (DkanFileFetcher). Therefore,
    // rather than making unsetDuplicateCustomProcessorClasses protected and
    // making a new release, we'll use reflection here, for this effectively
    // deprecated class.
    // @todo Remove this along with everything else when we merge it with #4074
    //   or vice-versa.
    $ref_unset_duplicate = new \ReflectionMethod($this, 'unsetDuplicateCustomProcessorClasses');
    $ref_unset_duplicate->setAccessible(TRUE);

    $ref_unset_duplicate->invoke($this, [FileFetcherRemoteUseExisting::class]);
    $ref_unset_duplicate->setAccessible(FALSE);
  }

}
