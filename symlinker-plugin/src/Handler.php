<?php

namespace Dkan\Composer\Plugin\Symlinker;

use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Core class of the plugin.
 *
 * Contains the primary logic which determines the files to be fetched and
 * processed.
 *
 * @internal
 */
class Handler {

  protected $calledByCommand = FALSE;

  /**
   * The Composer service.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * Composer's I/O service.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * The scaffold options in the top-level composer.json's 'extra' section.
   *
   * @var \Dkan\Composer\Plugin\Symlinker\SymlinkerOptions
   */
  protected $manageOptions;

  /**
   * Handler constructor.
   *
   * @param \Composer\Composer $composer
   *   The Composer service.
   * @param \Composer\IO\IOInterface $io
   *   The Composer I/O service.
   */
  public function __construct(Composer $composer, IOInterface $io, $called_by_command = FALSE) {
    $this->composer = $composer;
    $this->io = $io;
    $this->manageOptions = new ManageOptions($composer);
    $this->calledByCommand = $called_by_command;
  }

  public function makesymlinks() {
    $symlink_makers = [];
    $symlinker_options = $this->manageOptions->getOptions();
    // Don't do anything if config says not to.
    if ($symlinker_options->symlinkOnInstallUpdate() || $this->calledByCommand) {
      if ($mappings = $symlinker_options->fileMapping()) {
        foreach ($mappings as $destination => $source) {
          $dest_path = $this->locationSubtitution($destination, $symlinker_options->locations());
          $src_path = $this->locationSubtitution($source, $symlinker_options->locations());
          $maker = new SymlinkMaker($this->io, $src_path, $dest_path);
          if ($maker->valid()) {
            $symlink_makers[] = $maker;
          }
        }
      }
      foreach ($symlink_makers as $maker) {
        $maker->execute();
      }
    }
  }

  protected function locationSubtitution($path, $locations) {
    foreach ($locations as $name => $location_path) {
      $path = str_replace("[$name]", $location_path, $path);
    }
    // Handle empty path elements.
    $path_items = array_filter(explode('/', $path));
    // Normalize to current directory.
    if ($path_items[0] !== '.') {
      array_unshift($path_items, '.');
    }
    return implode('/', $path_items);
  }

  /**
   * Tell the user whether all the symlinking occurred.
   *
   * @todo This needs more work.
   *
   * @param $performed_symlink
   *
   * @return void
   */
  public function notifyUser($performed_symlink) {
    if ($performed_symlink) {
      return;
    }
    $symlinker_options = $this->manageOptions->getOptions();
    $this->io->alert(implode("\n", $symlinker_options->notSymlinkedMessage()));
  }

}
