<?php

namespace Drupal\Tests\common\Traits;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Serialization\Yaml;
use MockChain\Chain;
use MockChain\Options;

/**
 *
 */
trait ServiceCheckTrait {

  /**
   * Private.
   */
  private function getContainerOptionsForService($serviceName): Options {
    $options = (new Options())->index(0);
    $service = $this->checkService($serviceName);
    $arguments = $service['arguments'];
    foreach ($arguments as $arg) {
      $arg = str_replace("@", '', $arg);
      $argService = $this->checkService($arg);
      $class = $argService['class'];
      if ($class[0] == '\\') {
        $class = substr($class, 1);
      }
      $options->add($arg, $class);
    }
    return $options;
  }

  /**
   * Private.
   */
  private function getContainerChainForService($serviceName): Chain {
    $options = $this->getContainerOptionsForService($serviceName);
    return (new Chain($this))->add(Container::class, 'get', $options);
  }

  /**
   * Private.
   */
  private function checkService($serviceName) {
    $dkanModules = [
      'common',
      'datastore',
      'frontend',
      'harvest',
      'metastore',
    ];
    $files = [];

    foreach ($dkanModules as $dkanModule) {
      $files[] = $this->getRelativeDkanModulePath($dkanModule) . "/{$dkanModule}.services.yml";
    }
    $files[] = $this->getRelativeDrupalPath() . "/core/core.services.yml";

    foreach ($files as $file) {
      $content = Yaml::decode(file_get_contents($file));
      $services = array_keys($content['services']);
      if (in_array($serviceName, $services)) {
        $this->assertTrue(TRUE, "{$serviceName} exists in {$file}");
        return $content['services'][$serviceName];
      }
    }
    $this->assertFalse(TRUE, "{$serviceName} does not exist in DKAN or Drupal core.");
  }

  /**
   * Private.
   */
  private function getRelativeDkanModulePath($moduleName, $path = NULL) {
    if (!$path) {
      $path = $this->getRelativeDkanPath();
    }

    foreach (new \DirectoryIterator($path) as $fileInfo) {
      if ($fileInfo->isDir() && !$fileInfo->isDot()) {
        if ($fileInfo->getFilename() == $moduleName) {
          return $fileInfo->getPathname();
        }
        elseif ($fileInfo->getFilename() == "modules") {
          return $this->getRelativeDkanModulePath($moduleName, $fileInfo->getPathname());
        }
      }
    }
  }

  /**
   * Private.
   */
  private function getRelativeDkanPath() {
    $path = __DIR__;

    while (TRUE) {
      $content = glob($path . "/*");
      $content = array_map(function ($item) use ($path) {
        return str_replace($path, "", $item);
      }, $content);

      if (in_array("/dkan.info.yml", $content)) {
        return $path;
      }

      $path .= "/..";
    }
  }

  /**
   * Private.
   */
  private function getRelativeDrupalPath() {
    return getenv('DRUPAL_ROOT');
  }

}
