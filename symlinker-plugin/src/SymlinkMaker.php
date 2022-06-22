<?php

namespace Dkan\Composer\Plugin\Symlinker;

use Symfony\Component\Filesystem\Filesystem;
use Composer\IO\IOInterface;

class SymlinkMaker {

  protected $source;

  protected $realSource;

  protected $destination;

  protected $io;

  public function __construct(IOInterface $io, $source, $destination) {
    $this->source = $source;
    $this->realSource = realpath($source);
    $this->destination = $destination;
    $this->io = $io;
  }

  /**
   * @return bool|void
   *   TRUE if the symlink needs to be created in some way. FALSE otherwise.
   */
  public function valid() {
    $fs = new Filesystem();
    if ($fs->exists($this->destination) && $fs->exists($this->realSource)) {
      return !(is_link($this->destination) && readlink($this->destination) == $this->realSource);
    }
    return TRUE;
  }

  public function execute() {
    $fs = new Filesystem();
    $source = realpath($this->source);
    // If the symlink already exists and is correct, don't do any work.
    if ($fs->exists($this->destination) && $fs->exists($source)) {
      if (is_link($this->destination) && readlink($this->destination) == $source) {
        return;
      }
      // The destination exists and is different.
      if (is_dir($this->destination)) {
        $response = $this->io->ask('Path ' . $this->destination . ' exists and is a directory. Copy it into ' . $this->source . ' before linking? (Y/n)', 'Y');
        if (in_array(strtolower($response), ['y', 'yes'])) {
          $fs->mirror($this->destination, $this->source, NULL, [
            'override' => FALSE,
            'delete' => FALSE,
          ]);
        }
        $fs->remove([$this->destination]);
      }
      else {
        throw new \Exception('Path ' . $this->destination . ' should be a directory, so that we can link it from ' . $this->source . '.');
      }
    }

    $this->io->write('Symlinking: ' . $this->source . ' to ' . $this->destination);
    $fs->symlink($source, $this->destination);
  }

}
