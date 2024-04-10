<?php

namespace Drupal\Tests\dkan_js_frontend\Unit;

/**
 * Settings service mock interface.
 */
interface SimpleSitemapSettingsInterface {

  /**
   * Returns a specific setting or a default value if setting does not exist.
   *
   * @param string $name
   *   Name of the setting, like 'max_links'.
   * @param mixed $default
   *   Value to be returned if the setting does not exist in the configuration.
   *
   * @return mixed
   *   The current setting from configuration or a default value.
   */
  public function get(string $name, $default = NULL);

}
