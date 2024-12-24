<?php

namespace Drupal\sample_content;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\harvest\Commands\Helper;
use Drupal\harvest\HarvestService;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the sample content module.
 *
 * @codeCoverageIgnore
 *
 * @todo Figure out why DrushTestTraits don't count as coverage for commands.
 */
class Drush extends DrushCommands {
  use Helper;

  protected const HARVEST_ID = 'sample_content';

  /**
   * The core extension module list service.
   */
  protected ModuleExtensionList $moduleExtensionList;

  /**
   * Harvest service.
   */
  private HarvestService $harvestService;

  /**
   * Sample content service.
   */
  private SampleContentService $sampleContentService;

  /**
   * Constructor for the Sample Content commands.
   *
   * @param \Drupal\sample_content\SampleContentService $sampleContentService
   *   Sample content service.
   * @param \Drupal\harvest\HarvestService $harvestService
   *   Harvest service.
   */
  public function __construct(
    SampleContentService $sampleContentService,
    HarvestService $harvestService
  ) {
    parent::__construct();
    $this->sampleContentService = $sampleContentService;
    $this->harvestService = $harvestService;
  }

  /**
   * Create sample content.
   *
   * @command dkan:sample-content:create
   */
  public function create() {
    $this->logger()->notice('Setting up harvest: ' . static::HARVEST_ID);
    $this->sampleContentService->registerSampleContentHarvest(static::HARVEST_ID);
    $this->renderHarvestRunsInfo([
      $this->harvestService->runHarvest(static::HARVEST_ID),
    ]);
    $this->logger()->notice('Run cron a few times to finish the import of this data.');
  }

  /**
   * Remove sample content.
   *
   * @command dkan:sample-content:remove
   */
  public function remove() {
    if (!$this->harvestService->getHarvestPlanObject(static::HARVEST_ID)) {
      $this->logger()->notice('Harvest plan ' . static::HARVEST_ID . ' is not available. Re-registering it so we can revert it.');
      $this->sampleContentService->registerSampleContentHarvest(static::HARVEST_ID);
      $this->harvestService->runHarvest(static::HARVEST_ID);
    }
    $this->logger()->notice('Reverting harvest plan: ' . static::HARVEST_ID);
    $count = $this->harvestService->revertHarvest(static::HARVEST_ID);
    $this->logger()->notice($count . " items reverted for the 'sample_content' harvest plan.");
    $this->logger()->notice('Deregistering harvest plan: ' . static::HARVEST_ID);
    $this->harvestService->deregisterHarvest(static::HARVEST_ID);
  }

}
