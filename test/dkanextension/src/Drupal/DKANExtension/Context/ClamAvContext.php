<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\AfterFeatureScope;

/**
 * Defines application features from the specific context.
 */
class ClamAvContext extends RawDKANContext {

  public static $modulesBeforeFeature = array();

  /**
   * @BeforeFeature @clamav
   */
  public static function BeforeFeatureClamav(BeforeFeatureScope $scope) {
    self::$modulesBeforeFeature = module_list(TRUE);

    @module_enable(array(
      'clamav',
    ));
    drupal_flush_all_caches();
  }

  /**
   * @AfterFeature @clamav
   */
  public static function AfterFeatureClamav(AfterFeatureScope $scope) {
    $modules_after_feature = module_list(TRUE);

    $modules_to_disable = array_diff_assoc(
      $modules_after_feature,
      self::$modulesBeforeFeature
    );
    module_disable(array_values($modules_to_disable));
    drupal_uninstall_modules(array_values($modules_to_disable));
    drupal_flush_all_caches();
  }

}
