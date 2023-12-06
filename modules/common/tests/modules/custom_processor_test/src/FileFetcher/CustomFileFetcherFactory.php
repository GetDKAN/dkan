<?php

namespace Drupal\custom_processor_test\FileFetcher;

use Contracts\FactoryInterface;

/**
 * Creates new file fetcher objects with NonProcessor as a custom processor.
 *
 * @see modules/common/tests/modules/custom_processor_test/custom_processor_test.services.yml
 * @see \Drupal\custom_processor_test\FileFetcher\NonProcessor
 */
class CustomFileFetcherFactory implements FactoryInterface {

  /**
   * The decorated file factory service object.
   *
   * @var \Contracts\FactoryInterface
   */
  protected $decoratedFactory;

  /**
   * Constructor.
   *
   * @param \Contracts\FactoryInterface $decoratedFactory
   *   The decorated file factory service object.
   */
  public function __construct(FactoryInterface $decoratedFactory) {
    $this->decoratedFactory = $decoratedFactory;
  }

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    // Add NonProcessor as a custom processor.
    $config['processors'] = array_merge(
      [NonProcessor::class],
      $config['processors'] ?? []
    );
    // Get the instance from the decorated factory, using our modified config.
    return $this->decoratedFactory->getInstance($identifier, $config);
  }

}
