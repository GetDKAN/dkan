<?php

namespace Drupal\common\FileFetcher;

use Contracts\FactoryInterface;
use Drupal\common\Storage\JobStoreFactory;
use FileFetcher\FileFetcher;

/**
 * FileFetcher Factory.
 */
class Factory implements FactoryInterface {

  private $factory;
  private $config_default = [
    'keep_original_filename' => True,
  ];

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $factory) {
    $this->factory = $factory;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    $config = array_merge($this->config_default, $config);
    return FileFetcher::get($identifier, $this->getFileFetcherJobStore(), $config);
  }

  /**
   * Private.
   */
  private function getFileFetcherJobStore() {
    /* @var \Drupal\common\Storage\JobStoreFactory $jobStoreFactory */
    $jobStoreFactory = $this->factory;
    return $jobStoreFactory->getInstance(FileFetcher::class);
  }

}
