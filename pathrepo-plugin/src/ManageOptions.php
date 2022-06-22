<?php

namespace Dkan\Composer\Plugin\Pathrepo;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;

/**
 * Per-project options from the 'extras' section of the composer.json file.
 *
 * Projects that describe scaffold files do so via their scaffold options.
 * This data is pulled from the 'drupal-scaffold' portion of the extras
 * section of the project data.
 *
 * @internal
 */
class ManageOptions {

  /**
   * The Composer service.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * ManageOptions constructor.
   *
   * @param \Composer\Composer $composer
   *   The Composer service.
   */
  public function __construct(Composer $composer) {
    $this->composer = $composer;
  }

  /**
   * Gets the root-level scaffold options for this project.
   *
   * @return \Dkan\Composer\Plugin\Pathrepo\PathrepoOptions
   *   The scaffold options object.
   */
  public function getOptions() {
    return $this->packageOptions($this->composer->getPackage());
  }

  /**
   * Gets the scaffold options for the stipulated project.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to fetch the scaffold options from.
   *
   * @return \Dkan\Composer\Plugin\Pathrepo\PathrepoOptions

   *   The scaffold options object.
   */
  public function packageOptions(PackageInterface $package) {
    return PathrepoOptions::create($package->getExtra());
  }

}
