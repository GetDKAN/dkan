<?php

namespace Drupal\dkan_datastore\Drush\Commands;

use Drupal\dkan_datastore\Service\ResourcePurger;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;

/**
 * Datastore-related Drush commands.
 *
 * @codeCoverageIgnore
 */
final class PurgeCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(protected ResourcePurger $resourcePurger) {
    parent::__construct();
  }

  /**
   * Purge unneeded resources from one or more specific datasets.
   *
   * Queue the purging of resources associated with datasets 1111 and 1112,
   * including all prior revisions.
   */
  #[CLI\Command(name: 'dkan:datastore:purge', description: 'Purge unneeded resources from one or more specific datasets.')]
  #[CLI\Argument(name: 'csvUuids', description: 'One or more dataset identifiers, comma-separated, no space.')]
  #[CLI\Option(name: 'deferred', description: 'Queue the purge for later processing.')]
  #[CLI\Option(name: 'prior', description: 'Consider all prior dataset revisions, instead of the two most recent.')]
  #[CLI\Usage(name: 'dkan:datastore:purge 1111,1112 --deferred --prior', description: 'Queue the purging of resources associated with datasets 1111 and 1112, including all prior revisions.')]
  public function purge(
    string $csvUuids,
    array $options = ['deferred' => FALSE, 'prior' => FALSE],
  ) {
    try {
      $uuids = StringUtils::csvToArray($csvUuids);
      $this->resourcePurger->schedule($uuids, $options['deferred'], $options['prior']);
      $messagePrefix = $options['deferred'] ? 'Queued the purging of' : 'Purged';
      $this->logger()->info("{$messagePrefix} resources in {$csvUuids}.");
    }
    catch (\Exception $e) {
      $this->logger()->error("Error purging resources in {$csvUuids}: " . $e->getMessage());
    }
  }

  /**
  * Purge unneeded resources from all datasets.
   *
  * Queue the purging of every dataset's resources, including all prior
  * revisions.
   */
  #[CLI\Command(name: 'dkan:datastore:purge-all', description: 'Purge unneeded resources from all datasets.')]
  #[CLI\Option(name: 'deferred', description: 'Queue the purge for later processing.')]
  #[CLI\Option(name: 'prior', description: 'Consider all prior dataset revisions, instead of the two most recent.')]
  #[CLI\Usage(name: 'dkan:datastore:purge-all --deferred --prior', description: "Queue the purging of every dataset's resources, including all prior revisions.")]
  public function purgeAll(
    array $options = ['deferred' => FALSE, 'prior' => FALSE],
  ) {
    try {
      $this->resourcePurger->scheduleAllUuids($options['deferred'], $options['prior']);
      $messagePrefix = $options['deferred'] ? 'Queued the purging of' : 'Purged';
      $this->logger()->info("{$messagePrefix} resources in every dataset.");
    }
    catch (\Exception $e) {
      $this->logger()->error("Error purging resources in every dataset: " . $e->getMessage());
    }
  }

}
