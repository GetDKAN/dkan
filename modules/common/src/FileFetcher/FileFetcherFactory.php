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
   * JobStore instances keyed by unique identifiers.
   *
   * @var \Drupal\common\Storage\JobStore[]
   */
  private $instances = [];

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
    // Use the instances array to make sure we return the same file fetcher for
    // the given resource id and config, also accounting for the
    // use-existing-file config since it results in different types of config
    // later.
    $config = array_merge($this->configDefault, $config);
    $use_existing = $this->dkanConfig->get('always_use_existing_local_perspective') ?? FALSE;
    $hash = $identifier . print_r($config, TRUE) . $use_existing;
    if (!isset($this->instances[$hash])) {
      // Use our bespoke file fetcher class that uses the existing file if we're
      // configured to do so.
      if ($use_existing) {
        $config['processors'] = [FileFetcherRemoteUseExisting::class];
      }
      $this->instances[$hash] = FileFetcher::get(
        $identifier,
        $this->jobStoreFactory->getInstance(FileFetcher::class),
        $config
      );
    }
    return $this->instances[$hash];
  }

}
