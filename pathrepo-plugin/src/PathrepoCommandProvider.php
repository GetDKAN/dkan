<?php

namespace Dkan\Composer\Plugin\Pathrepo;

use Composer\Plugin\Capability\CommandProvider;

/**
 * List of all commands provided by this package.
 *
 * @internal
 */
class PathrepoCommandProvider implements CommandProvider {

  /**
   * {@inheritdoc}
   */
  public function getCommands() {
    return [new PathrepoCommand()];
  }

}
