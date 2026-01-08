<?php

namespace Drupal\datastore\Commands;

use Drupal\datastore\Service\ResourcePurger;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;

/**
 * Datastore-related Drush commands.
 *
 * @codeCoverageIgnore
 */
class PurgeCommands extends DrushCommands {

  /**
   * Resource purger.
   *
   * @var \Drupal\datastore\Service\ResourcePurger
   */
  protected $resourcePurger;

  /**
   * PurgeCommands constructor.
   *
   * @param \Drupal\datastore\Service\ResourcePurger $resourcePurger
   *   The dkan.datastore.service.resource_localizer service.
   */
  public function __construct(ResourcePurger $resourcePurger) {
    parent::__construct();
    $this->resourcePurger = $resourcePurger;
  }

  /**
   * Purge unneeded resources from one or more specific datasets.
   *
   * @param string $csvUuids
   *   One or more dataset identifiers, comma-separated, no space.
   * @param array $options
   *   Options array, see @option annotations below.
   *
   * @option deferred
   *   Queue the purge for later processing.
   * @option prior
   *   Consider all prior dataset revisions, instead of the two most recent.
   *
   * @usage dkan:datastore:purge 1111,1112 --deferred --prior
   *   Queue the purging of resources associated with datasets 1111 and 1112,
   *   including all prior revisions.
   *
   * @command dkan:datastore:purge
   */
  public function purge(
    string $csvUuids,
    array $options = ['deferred' => FALSE, 'prior' => FALSE]
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
   * Purge unneeded resources from all datsets.
   *
   * @param array $options
   *   Options array, see @option annotations below.
   *
   * @option deferred
   *   Queue the purge for later processing.
   * @option prior
   *   Consider all prior dataset revisions, instead of the two most recent.
   *
   * @usage dkan:datastore:purge-all --deferred --prior
   *   Queue the purging of every dataset's resources, including all prior
   *   revisions.
   *
   * @command dkan:datastore:purge-all
   */
  public function purgeAll(
    array $options = ['deferred' => FALSE, 'prior' => FALSE]
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
