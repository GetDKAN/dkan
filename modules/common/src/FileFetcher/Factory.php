<?php

namespace Drupal\common\FileFetcher;

use Contracts\FactoryInterface;
use Drupal\common\Storage\JobStoreFactory;
use FileFetcher\FileFetcher;

/**
 * File fetcher Factory.
 */
class Factory implements FactoryInterface {

  /**
   * Job store factory service.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private $factory;

  /**
   * Default file fetcher config.
   *
   * @var array
   */
  private $configDefault = [
    'keep_original_filename' => TRUE,
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
    $config = array_merge($this->configDefault, $config);
    return FileFetcher::get($identifier, $this->getFileFetcherJobStore(), $config);
  }

  /**
   * Private.
   */
  private function getFileFetcherJobStore() {
    /** @var \Drupal\common\Storage\JobStoreFactory $jobStoreFactory */
    $jobStoreFactory = $this->factory;
    return $jobStoreFactory->getInstance(FileFetcher::class);
  }

}
