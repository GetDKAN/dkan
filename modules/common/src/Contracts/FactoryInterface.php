<?php

namespace Drupal\common\Contracts;

/**
 * Interface for the factory pattern.
 */
interface FactoryInterface {

  /**
   * Construct or deliver an object of the expected class.
   *
   * For example a MemoryStorage factory should return
   * MemoryStorage objects.
   *
   * @param string $identifier
   *   Some way to discern between different instances of a class.
   * @param array $config
   *   (Optional) Arbitrary configuration passed in to the factory.
   *
   * @return mixed
   *   The desired instance. Generally this wil be an object.
   */
  public function getInstance(string $identifier, array $config = []);

}
