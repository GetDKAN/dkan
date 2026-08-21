<?php

namespace Drupal\dkan_sample_content\Drush\Commands;

use Drupal\dkan_harvest\Drush\Commands\HelperTrait;
use Drupal\dkan_harvest\HarvestService;
use Drupal\dkan_sample_content\SampleContentService;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Attributes as CLI;

/**
 * Drush commands for the sample content module.
 *
 * @codeCoverageIgnore
 *
 * @todo Figure out why DrushTestTraits don't count as coverage for commands.
 */
final class SampleContentCommands extends DrushCommands {

  use AutowireTrait;
  use HelperTrait;

  protected const HARVEST_ID = 'sample_content';

  /**
   * Constructor for the Sample Content commands.
   */
  public function __construct(
    private SampleContentService $sampleContentService,
    private HarvestService $harvestService,
  ) {
    parent::__construct();
  }

  /**
   * Create sample content.
   */
  #[CLI\Command(name: 'dkan:sample-content:create', description: 'Create sample content.')]
  public function createSampleContent() {
    $this->logger()->notice('Setting up harvest: ' . static::HARVEST_ID);
    $this->sampleContentService->registerSampleContentHarvest(static::HARVEST_ID);
    $this->renderHarvestRunsInfo([
      $this->harvestService->runHarvest(static::HARVEST_ID),
    ]);
    $this->logger()->notice('Run cron a few times to finish the import of this data.');
  }

  /**
   * Remove sample content.
   */
  #[CLI\Command(name: 'dkan:sample-content:remove', description: 'Remove sample content.')]
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
