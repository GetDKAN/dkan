<?php

namespace Dkan\Composer\Plugin\Symlinker;

use Composer\Plugin\Capability\CommandProvider;

/**
 * List of all commands provided by this package.
 *
 * @internal
 */
class SymlinkerCommandProvider implements CommandProvider {

  /**
   * {@inheritdoc}
   */
  public function getCommands() {
    return [new SymlinkerCommand()];
  }

}
