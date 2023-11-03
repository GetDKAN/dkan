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
    // @todo Re-computing the custom processor classes should be in another
    //   method that is in the parent class.
    if ($use_local_file) {
      $this->dkanUseLocalFileProcessor();
    }
    else {
      $this->dkanUseDefaultFileProcessor();
    }
    return $this;
  }

  /**
   * Configure the processor to respect the local file if it already exists.
   */
  protected function dkanUseLocalFileProcessor() {
    // Set the state/config to use our remote class.
    $this->setProcessors(['processors' => [FileFetcherRemoteUseExisting::class]]);
    $this->setStateProperty('processor', FileFetcherRemoteUseExisting::class);
    // At this very early stage, update the status if the file already exists.
    /** @var \Drupal\common\FileFetcher\FileFetcherRemoteUseExisting $processor */
    $processor = $this->getProcessor();
    $existing_status = $processor->discoverStatusForExistingFile(
      $this->getState(),
      $this->getResult()
    );
    $this->setState($existing_status['state']);
  }

  /**
   * Configure the processor to use its default behavior.
   */
  protected function dkanUseDefaultFileProcessor() {
    // @todo This ignores any other custom processor classes that might have
    //   been configured. Improve this situation.
    $this->customProcessorClasses = [];
    $state = $this->getState();
    foreach ($this->getProcessors() as $processor) {
      if ($processor->isServerCompatible($state)) {
        $state['processor'] = get_class($processor);
        break;
      }
    }
    $this->getResult()->setData(json_encode($state));
  }

}
