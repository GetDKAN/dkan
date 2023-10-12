<?php

namespace Drupal\common\FileFetcher;

use Contracts\FactoryInterface;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use FileFetcher\FileFetcher;

/**
 * File fetcher Factory.
 */
class FileFetcherFactory implements FactoryInterface {

  /**
   * Job store factory service.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private JobStoreFactory $jobStoreFactory;

  /**
   * The common.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $dkanConfig;

  /**
   * Default file fetcher config.
   *
   * @var array
   */
  private array $configDefault = [
    'keep_original_filename' => TRUE,
  ];

  /**
   * Constructor.
   */
  public function __construct(JobStoreFactory $jobStoreFactory, ConfigFactoryInterface $configFactory) {
    $this->jobStoreFactory = $jobStoreFactory;
    $this->dkanConfig = $configFactory->get('common.settings');
  }

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    $config = array_merge($this->configDefault, $config);
    $config['always_use_existing_local_perspective'] =
      $this->dkanConfig->get('always_use_existing_local_perspective');
    // Use our bespoke file fetcher class that won't overwrite the existing file
    // if we're configured to do so.
    if ($this->dkanConfig->get('always_use_existing_local_perspective') ?? FALSE) {
      $config['processors'] = [FileFetcherRemoteUseExisting::class];
    }
    $ff = DkanFileFetcher::get(
      $identifier,
      $this->jobStoreFactory->getInstance(FileFetcher::class),
      $config
    );
    return $ff;
  }

}
