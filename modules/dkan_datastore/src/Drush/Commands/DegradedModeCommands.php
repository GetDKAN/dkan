<?php

namespace Drupal\dkan_datastore\Drush\Commands;

use Drupal\Core\State\StateInterface;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Attributes as CLI;

/**
 * Datastore-related Drush commands.
 *
 * @codeCoverageIgnore
 */
final class DegradedModeCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(protected StateInterface $state) {
    parent::__construct();
  }

  /**
   * Enable or disable degraded datastore query mode.
   */
  #[CLI\Command(name: 'dkan:datastore:degraded-mode', description: 'Enable or disable degraded datastore query mode.')]
  #[CLI\Argument(name: 'state', description: 'Set to 1 to enable degraded mode, or 0 to disable. Omit to check current status.')]
  public function degradedMode(?string $state = NULL) {
    $current = (bool) $this->state->get('dkan_datastore.degraded_performance', FALSE);

    if ($state === NULL) {
      $this->output()->writeln('Datastore degraded performance mode: ' . ($current ? 'enabled' : 'disabled'));
      return DrushCommands::EXIT_SUCCESS;
    }

    if ($state !== '1' && $state !== '0') {
      $this->output()->writeln('Invalid value. Use 1 or 0.');
      return DrushCommands::EXIT_FAILURE;
    }

    $value = $state === '1';
    $this->state->set('dkan_datastore.degraded_performance', $value);
    $this->output()->writeln('Datastore degraded performance mode ' . ($value ? 'enabled' : 'disabled') . '.');
    return DrushCommands::EXIT_SUCCESS;
  }

}
