<?php

namespace Drupal\common\FileFetcher;

use Contracts\FactoryInterface;
use Drupal\common\Storage\FileFetcherJobStoreFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use FileFetcher\FileFetcher;

/**
 * File fetcher Factory.
 */
class FileFetcherFactory implements FactoryInterface {

  /**
   * File fetcher job store factory.
   */
  private FileFetcherJobStoreFactory $fileFetcherJobStoreFactory;

  /**
   * The common.settings config.
   */
  private ImmutableConfig $dkanConfig;

  /**
   * Default file fetcher config.
   */
  private array $configDefault = [
    'keep_original_filename' => TRUE,
  ];

  /**
   * Constructor.
   */
  public function __construct(FileFetcherJobStoreFactory $fileFetcherJobStoreFactory, ConfigFactoryInterface $configFactory) {
    $this->fileFetcherJobStoreFactory = $fileFetcherJobStoreFactory;
    $this->dkanConfig = $configFactory->get('common.settings');
  }

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    return FileFetcher::get(
      $identifier,
      $this->fileFetcherJobStoreFactory->getInstance(),
      $this->getFileFetcherConfig($config)
    );
  }

  /**
   * Adjust the provided config for our defaults and DKAN configuration.
   *
   * @param array $config
   *   Configuration provided by the caller to getInstance().
   *
   * @return array
   *   Modified configuration array.
   */
  protected function getFileFetcherConfig(array $config): array {
    // Merge in our defaults.
    $config = array_merge($this->configDefault, $config);
    // Add our special custom processor to the config if we're configured to
    // always use the local perspective file.
    if ($this->dkanConfig->get('always_use_existing_local_perspective')) {
      $processors = [FileFetcherRemoteUseExisting::class] + ($config['processors'] ?? []);
      $config['processors'] = $processors;
    }
    return $config;
  }

}
