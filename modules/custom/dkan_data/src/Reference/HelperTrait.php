<?php

namespace Drupal\dkan_data\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\dkan_data\Service\Uuid5;

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
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  private $loggerService;

  /**
   * Setter.
   */
  private function setConfigService(ConfigFactoryInterface $configService) {
    $this->configService = $configService;
  }

  /**
   * Setter.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerService
   *   Injected logger factory service.
   */
  public function setLoggerFactory(LoggerChannelFactory $loggerService) {
    $this->loggerService = $loggerService;
  }

  /**
   * Private.
   */
  private function log($loggerName, $message, $variables) {
    if ($this->loggerService) {
      $this->loggerService->get($loggerName)->error($message, $variables);
    }
  }

  /**
   * Get the list of dataset properties being referenced.
   *
   * @return array
   *   List of dataset properties.
   *
   * @Todo: consolidate with dkan_api RouteProvider's getPropertyList.
   */
  private function getPropertyList() : array {
    if (isset($this->configService)) {
      $list = $this->configService->get('dkan_data.settings')->get('property_list');
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
