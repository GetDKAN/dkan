<?php

namespace Drupal\datastore\Commands;

use Drupal\datastore\Service\ResourcePurger;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
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
   * @option all-revisions
   *   Consider all dataset revisions.
   *
   * @usage dkan:datastore:purge 1111,1112 --deferred --all-revisions
   *   Queue the purging of resources in 1111 and 1112, consider all revisions.
   *
   * @command dkan:datastore:purge
   */
  public function purge(string $csvUuids, array $options = ['deferred' => FALSE, 'all-revisions' => FALSE]) {
    try {
      $uuids = StringUtils::csvToArray($csvUuids);
      $this->resourcePurger->schedulePurging($uuids, $options['deferred'], $options['all-revisions']);
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
   * @option all-revisions
   *   Consider all dataset revisions.
   *
   * @usage dkan:datastore:purge-all --deferred --all-revisions
   *   Purge resources from every datasets later, consider all revisions.
   *
   * @command dkan:datastore:purge-all
   */
  public function purgeAll(array $options = ['deferred' => FALSE, 'all-revisions' => FALSE]) {
    try {
      $this->resourcePurger->schedulePurging([], $options['deferred'], $options['all-revisions']);
      $messagePrefix = $options['deferred'] ? 'Queued the purging of' : 'Purged';
      $this->logger()->info("{$messagePrefix} resources in every dataset.");
    }
    catch (\Exception $e) {
      $this->logger()->error("Error purging resources in every dataset: " . $e->getMessage());
    }
  }

}
