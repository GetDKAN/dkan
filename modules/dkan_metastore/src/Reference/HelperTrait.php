<?php

namespace Drupal\dkan_metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_metastore\Service\Uuid5;

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
  protected function setConfigService(ConfigFactoryInterface $configService): void {
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
  protected function getPropertyList() : array {
    if (isset($this->configService)) {
      $list = $this->configService->get('dkan_metastore.settings')->get('property_list');
      return array_values(array_filter($list));
    }
    throw new \Exception("Can't get property list, the config service was not set.");
  }

  /**
   * Read from the config service whether distributions are referenced.
   *
   * @return bool
   *   True if the distribution property is referenced.
   */
  protected function distributionsAreReferenced(): bool {
    $propertyList = $this->getPropertyList();
    return ($propertyList['distribution'] ?? NULL == 'distribution');
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
  protected function emptyPropertyOfSameType(mixed $data) {
    if (is_array($data)) {
      return [];
    }
    return "";
  }

  /**
   * Uuid Service.
   *
   * @return \Drupal\dkan_metastore\Service\Uuid5
   *   Uuid5 object.
   */
  protected function getUuidService() {
    return new Uuid5();
  }

}
