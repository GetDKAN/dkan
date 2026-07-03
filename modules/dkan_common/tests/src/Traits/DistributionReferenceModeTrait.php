<?php

namespace Drupal\Tests\dkan_common\Traits;

use Drupal\Core\Config\Config;

/**
 * Helpers for tests that run with distribution references on/off.
 */
trait DistributionReferenceModeTrait {

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
   */
  protected static function setDistributionReferenceModeFromConfig(Config $config, string $distribution_reference): void {
    $property_list = $config->get('property_list');
    $property_list['distribution'] = $distribution_reference;
    $config
      ->set('property_list', $property_list)
      ->save();
  }

}
