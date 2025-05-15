<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\metastore\Service\Uuid5;

/**
 * HelperTrait for referencer classes.
 */
trait HelperTrait {
  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configService;

  /**
   * Set the config service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   The config service.
   */
  private function setConfigService(ConfigFactoryInterface $configService): void {
    $this->configService = $configService;
  }

  /**
   * Get the list of dataset properties being referenced.
   *
   * @return array
   *   List of dataset properties.
   *
   * @todo consolidate with common RouteProvider's getPropertyList.
   */
  private function getPropertyList() : array {
    if (isset($this->configService)) {
      $list = $this->configService->get('metastore.settings')->get('property_list');
      return array_values(array_filter($list));
    }
    throw new \Exception("Can't get property list, the config service was not set.");
  }

  /**
   * Normalize an "empty" property against an array.
   *
   * @param mixed $data
   *   Data whose type we want to match.
   *
   * @return array|string
   *   Either the empty string or an empty array.
   */
  private function emptyPropertyOfSameType(mixed $data) {
    if (is_array($data)) {
      return [];
    }
    return "";
  }

  /**
   * Uuid Service.
   *
   * @return \Drupal\metastore\Service\Uuid5
   *   Uuid5 object.
   */
  private function getUuidService() {
    return new Uuid5();
  }

}
