<?php

namespace Drupal\common\FileFetcher;

use FileFetcher\FileFetcher;
use FileFetcher\Processor\ProcessorInterface;

/**
 * Our FileFetcher clone.
 *
 * This is mostly the same as \FileFetcher\FileFetcher, except we control the
 * processor using Drupal configuration for
 * always_use_existing_local_perspective.
 */
class DkanFileFetcher extends FileFetcher {

  protected bool $alwaysUseExistingLocalPerspective = FALSE;

  /**
   * Constructor.
   *
   * Mostly the same as FileFetcher::_construct(), except we keep track of the
   * always_use_existing_local_perspective configuration.
   *
   * @param string $identifier
   *   Identifier.
   * @param $storage
   *   Storage object.
   * @param array|null $config
   *   Config.
   */
  protected function __construct(string $identifier, $storage, array $config = NULL) {
    parent::__construct($identifier, $storage, $config);

    $this->alwaysUseExistingLocalPerspective =
      $config['always_use_existing_local_perspective'] ?? FALSE;
  }

  /**
   * Get the processor for this object.
   *
   * Always defer to FileFetcher parent unless we're configured to use the local
   * file.
   *
   * @return \FileFetcher\Processor\ProcessorInterface
   *   The processor object. In practice, this is probably
   *   \FileFetcher\Processor\Remote or FileFetcherRemoteUseExisting.
   *
   * @todo We always use our remote for local files, but we don't check the
   *   opposite. The jobstore persistent state could also tell this file fetcher
   *   to use locals, but maybe it shouldn't.
   *
   * @todo In an ideal world, FileFetcher jobstores would not include the
   *   processor in the persisted state info, and would generate the processor
   *   on the fly.
   *
   * @todo GetProcessors() instantiates one of *every* type of processor and
   *   stores it locally, which seems wasteful. Change FileFetcher to not do
   *   this.
   */
  protected function getProcessor() : ProcessorInterface {
    if ($this->alwaysUseExistingLocalPerspective) {
      return $this->getProcessors()[FileFetcherRemoteUseExisting::class];
    }
    return parent::getProcessor();
  }

}
