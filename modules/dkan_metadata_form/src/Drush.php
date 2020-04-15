<?php

namespace Drupal\dkan_metadata_form;

use Drush\Commands\DrushCommands;
use Masterminds\HTML5;
use Symfony\Component\Yaml\Yaml;

/**
 * Drush commands.
 *
 * @codeCoverageIgnore
 */
class Drush extends DrushCommands {

  private $moduleDirectory;
  private $librariesFilePath;
  private $reactAppPath;
  private $reactAppBuildDirectoryPath;
  private $reactAppBuildStaticJsDirectoryPath;
  private $reactAppBuildStaticCssDirectoryPath;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->moduleDirectory = DRUPAL_ROOT . "/" . drupal_get_path("module", "dkan_metadata_form");
    $this->librariesFilePath = $this->moduleDirectory . "/dkan_metadata_form.libraries.yml";
    $this->reactAppPath = $this->moduleDirectory . "/js/app";
    $this->reactAppBuildDirectoryPath = $this->reactAppPath . "/build";
    $this->reactAppBuildStaticJsDirectoryPath = $this->reactAppBuildDirectoryPath . "/static/js";
    $this->reactAppBuildStaticCssDirectoryPath = $this->reactAppBuildDirectoryPath . "/static/css";
  }

  /**
   * Sync.
   *
   * Synchronize the module with the React app.
   *
   * @command dkan-metadata-form:sync
   */
  public function sync() {
    $this->createLoadMeJs();
    $this->createtLibrariesFile();
  }

  /**
   * Create libraries file.
   */
  private function createtLibrariesFile() {
    $this->removeExistingFile();

    $libraries = $this->getLibrariesBasicStructure();

    $paths = [
      "css" => $this->reactAppBuildStaticCssDirectoryPath,
      "js" => $this->reactAppBuildStaticJsDirectoryPath,
    ];

    foreach ($paths as $type => $path) {
      $base = "js/app/build/static/{$type}/";
      $chunks = $this->getAppChunckFiles($path);
      $libraries = $this->setLibraries($libraries, $chunks, $type, $base);
    }

    $yaml = Yaml::dump($libraries);
    file_put_contents($this->librariesFilePath, $yaml);
  }

  /**
   * Private.
   */
  private function setLibraries($libraries, $chunks, $type, $base) {
    foreach ($chunks as $chunk) {
      if ($type == 'js') {
        $libraries['dkan_metadata_form']['js'][$base . $chunk] = [];
      }
      else {
        $libraries['dkan_metadata_form']['css']['base'][$base . $chunk] = [];
      }
    }
    return $libraries;
  }

  /**
   * Private.
   */
  private function getAppChunckFiles($path) {
    $folderInfo = scandir($path);
    unset($folderInfo[0]);
    unset($folderInfo[1]);
    $chunks = [];
    foreach ($folderInfo as $dirfile) {
      if (!$this->skip($dirfile)) {
        $chunks[] = $dirfile;
      }
    }
    return $chunks;
  }

  /**
   * Private.
   */
  private function skip($dirfile) {
    $skips = ["LICENSE", 'map', 'loadme', 'runtime'];
    $skip = FALSE;
    foreach ($skips as $s) {
      if (substr_count($dirfile, $s) > 0) {
        $skip = TRUE;
        break;
      }
    }
    return $skip;
  }

  /**
   * Private.
   */
  private function removeExistingFile() {
    if (file_exists($this->librariesFilePath)) {
      unlink($this->librariesFilePath);
    }
  }

  /**
   * Private.
   */
  private function getLibrariesBasicStructure() {

    return [
      'dkan_metadata_form' => [
        "version" => "1.x",
        "js" => [
          "js/app/build/static/js/loadme.js" => [],
        ],
        "css" => [
          "base" => [],
        ],
        "dependencies" => [
          "core/drupalSettings",
        ],
      ],
    ];
  }

  /**
   * Create loadMe.js.
   */
  private function createLoadMeJs() {
    $loadMeJsFilePath = $this->reactAppBuildStaticJsDirectoryPath . "/loadme.js";

    if (file_exists($loadMeJsFilePath)) {
      unlink($loadMeJsFilePath);
    }

    $indexFilePath = $this->reactAppBuildDirectoryPath . "/index.html";

    $html = new HTML5();
    $document = $html->parse(file_get_contents($indexFilePath));
    $scriptTags = $document->getElementsByTagName("script");

    /* @var $scriptTag DOMElement */
    foreach ($scriptTags as $scriptTag) {
      $content = $scriptTag->textContent;
      if (!empty($content)) {
        file_put_contents($loadMeJsFilePath, $content);
      }
    }
  }

}
