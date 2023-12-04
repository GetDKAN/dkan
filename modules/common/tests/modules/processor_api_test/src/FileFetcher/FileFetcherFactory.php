<?php

namespace Drupal\processor_api_test\FileFetcher;

use Contracts\FactoryInterface;

class FileFetcherFactory implements FactoryInterface {

  /**
   * The decorated file factory service object.
   *
   * @var \Contracts\FactoryInterface
   */
  protected $factory;

  /**
   * Constructor.
   *
   * @param \Contracts\FactoryInterface $factory
   *   The decorated file factory service object.
   */
  public function __construct(FactoryInterface $factory) {
    $this->factory = $factory;
  }

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    // Add NonProcessor as the first custom processor.
    $config['processors'] = array_merge(
      [NonProcessor::class],
      $config['processors'] ?? []
    );
    if (!isset($config['keep_original_filename'])) {
      $config['keep_original_filename'] = TRUE;
    }
    return $this->factory->getInstance($identifier, $config);
  }

}
