<?php

namespace Drupal\Tests\common\Traits;

use Drupal\Core\Serialization\Yaml;

/**
 *
 */
trait ServiceCheckTrait {

  /**
   *
   */
  private function checkService($serviceName, $dkanModule) {
    $servicesFile = $this->getRelativeDkanModulePath($dkanModule) . "/{$dkanModule}.services.yml";
    $content = Yaml::decode(file_get_contents($servicesFile));
    $services = array_keys($content['services']);
    $this->assertTrue(in_array($serviceName, $services));
  }

  /**
   *
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
   *
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

}
