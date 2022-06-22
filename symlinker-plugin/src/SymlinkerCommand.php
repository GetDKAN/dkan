<?php

namespace Dkan\Composer\Plugin\Symlinker;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The "dkan:makesymlinks" command class.
 *
 * @internal
 */
class SymlinkerCommand extends BaseCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('dkan:makesymlinks')
      ->setAliases(['makesymlinks'])
      ->setDescription('Symlink project directories into Drupal.')
      ->setHelp(
        <<<EOT
Sets up symlink stuff. @todo: Improve this description.
EOT
            );

  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $handler = new Handler($this->getComposer(), $this->getIO(), TRUE);
    $handler->makesymlinks();
    return 0;
  }

}
