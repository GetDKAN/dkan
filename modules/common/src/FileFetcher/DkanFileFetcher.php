<?php

namespace Drupal\common\FileFetcher;

use FileFetcher\FileFetcher;

/**
 * Allows FileFetcher to be reconfigured for using existing local files.
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
      $this->useCustomProcessor(FileFetcherRemoteUseExisting::class);
    }
    else {
      $this->useDefaultProcessors();
    }
    return $this;
  }

  /**
   * Use a custom file fetcher that will prefer existing local files.
   *
   * @param string $custom_processor_class
   *   Class name of the processor to use.
   */
  protected function useCustomProcessor(string $custom_processor_class) {
    // Set the state/config to use our remote class.
    $this->setProcessors(['processors' => [$custom_processor_class]]);
    $this->setStateProperty('processor', $custom_processor_class);
    // Also check if we can just short-circuit here.
    /** @var \Drupal\common\FileFetcher\FileFetcherRemoteUseExisting $processor */
    $processor = $this->getProcessor();
    $existing_status = $processor->discoverStatusForExistingFile(
      $this->getState(),
      $this->getResult()
    );
    $this->setState($existing_status['state']);
  }

  /**
   * Remove any custom processors from all FileFetcher configuration.
   */
  protected function useDefaultProcessors() {
    // @todo This ignores any other custom processor classes that might have
    //   been configured. Improve this situation.
    // @todo Re-computing the custom processor classes should be in another
    //   method that is in \FileFetcher\FileFetcher.
    // This code block is copied from FileFetcher::__construct().
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
