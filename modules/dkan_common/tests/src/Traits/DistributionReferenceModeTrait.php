<?php

namespace Drupal\Tests\dkan_common\Traits;

/**
 * Helpers for tests that run with distribution references on/off.
 *
 * Must be used on BrowserTestBase, KernelTestBase or similar classes that
 * implement a config() method.
 */
trait DistributionReferenceModeTrait {

  /**
   * Return an configuration object; ensures we're using on correct class.
   *
   * @param string $name
   *   Configuration name.
   *
   * @return \Drupal\Core\Config\Config
   *   Editable configuration.
   */
  abstract protected function config($name);

  /**
   * Two versions of metastore settings.
   *
   * Setting dkan_metastore.settings.property_list.distribution to "0" means
   * we do not reference distributions.
   */
  public static function distributionReferenceProvider(): array {
    return [
      ['distribution'],
      ['0'],
    ];
  }

  /**
   * Set distribution reference mode in metastore settings.
   *
   * @param string $distribution_reference
   *   The distribution reference mode to set.
   */
  protected function setDistributionReferenceModeFromConfig(string $distribution_reference): void {
    $config = $this->config('dkan_metastore.settings');
    $property_list = $config->get('property_list');
    $property_list['distribution'] = $distribution_reference;
    $config
      ->set('property_list', $property_list)
      ->save();
  }

}
