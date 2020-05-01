<?php

namespace Drupal\data_content_type\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\data_content_type\Service\Uuid5;

/**
 * HelperTrait.
 */
trait HelperTrait {
  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configService;

  /**
   * Setter.
   */
  private function setConfigService(ConfigFactoryInterface $configService) {
    $this->configService = $configService;
  }

  /**
   * Get the list of dataset properties being referenced.
   *
   * @return array
   *   List of dataset properties.
   *
   * @Todo: consolidate with wsapi RouteProvider's getPropertyList.
   */
  private function getPropertyList() : array {
    if (isset($this->configService)) {
      $list = $this->configService->get('data_content_type.settings')->get('property_list');
      return array_values(array_filter($list));
    }
    throw new \Exception("Can't get property list, the config service was not set.");
  }

  /**
   * Private.
   *
   * @param mixed $data
   *   Data whose type we want to match.
   *
   * @return array|string
   *   Either the empty string or an empty array.
   */
  private function emptyPropertyOfSameType($data) {
    if (is_array($data)) {
      return [];
    }
    return "";
  }

  /**
   * Uuid Service.
   */
  private function getUuidService() {
    return new Uuid5();
  }

}
