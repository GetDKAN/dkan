<?php

namespace Drupal\dkan_datastore\Commands;

use Drupal\Core\State\StateInterface;
use Drush\Commands\DrushCommands;

/**
 * Datastore-related Drush commands.
 *
 * @codeCoverageIgnore
 */
class DegradedModeCommands extends DrushCommands {

  /**
   * State service.
   */
  protected StateInterface $state;

  /**
   * DegradedModeCommands constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    parent::__construct();
    $this->state = $state;
  }

  /**
   * Enable or disable degraded datastore query mode.
   *
   * @param string|null $state
   *   Optional state: 1 or 0. If omitted, current status shown.
   *
   * @command dkan:datastore:degraded-mode
   */
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
