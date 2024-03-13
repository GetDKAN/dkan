<?php

namespace Drupal\sample_content;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\harvest\Commands\Helper;
use Drupal\harvest\HarvestService;
use Drush\Commands\DrushCommands;

/**
 * Class.
 */
class Drush extends DrushCommands {
  use Helper;

  protected const SAMPLE_CONTENT_HARVEST_ID = 'sample_content';

  /**
   * The core extension module list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $moduleExtensionList;

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
   */
  private HarvestService $harvestService;

  /**
   * Absolute app root path.
   *
   * @var string
   */
  private string $appRoot;

  /**
   * Constructor for the Sample Content commands.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   Extension list.
   * @param \Drupal\harvest\HarvestService $harvestService
   *   Harvest service.
   * @param string $appRoot
   *   The app root, equivalent to DRUPAL_ROOT.
   */
  public function __construct(
    ModuleExtensionList $moduleExtensionList,
    HarvestService $harvestService,
    string $appRoot
  ) {
    parent::__construct();
    $this->moduleExtensionList = $moduleExtensionList;
    $this->harvestService = $harvestService;
    $this->appRoot = $appRoot;
  }

  /**
   * Create sample content.
   *
   * @command dkan:sample-content:create
   */
  public function create() {
    $this->logger()->notice('Setting up harvest: ' . static::SAMPLE_CONTENT_HARVEST_ID);
    $this->registerSampleContentHarvest(static::SAMPLE_CONTENT_HARVEST_ID);
    $this->renderHarvestRunsInfo([
      [
        'sample_content',
        $this->harvestService->runHarvest(static::SAMPLE_CONTENT_HARVEST_ID),
      ],
    ]);
    $this->logger()->notice('Run cron a few times to finish the import of this data.');
  }

  /**
   * Remove sample content.
   *
   * @command dkan:sample-content:remove
   */
  public function remove() {
    if (!$this->harvestService->getHarvestPlanObject(static::SAMPLE_CONTENT_HARVEST_ID)) {
      $this->logger()->notice('Harvest plan ' . static::SAMPLE_CONTENT_HARVEST_ID . ' is not available. Re-registering it so we can revert it.');
      $this->registerSampleContentHarvest(static::SAMPLE_CONTENT_HARVEST_ID);
      $this->harvestService->runHarvest(static::SAMPLE_CONTENT_HARVEST_ID);
    }
    $this->logger()->notice('Reverting harvest plan: ' . static::SAMPLE_CONTENT_HARVEST_ID);
    $count = $this->harvestService->revertHarvest(static::SAMPLE_CONTENT_HARVEST_ID);
    $this->logger()->notice($count . " items reverted for the 'sample_content' harvest plan.");
    $this->logger()->notice('Deregistering harvest plan: ' . static::SAMPLE_CONTENT_HARVEST_ID);
    $this->harvestService->deregisterHarvest(static::SAMPLE_CONTENT_HARVEST_ID);
  }

  /**
   * Register our plan as the given harvest plan ID.
   *
   * @param $harvest_plan_id
   *   Harvest plan id.
   */
  protected function registerSampleContentHarvest($harvest_plan_id) {
    $this->createDatasetJsonFromTemplate();
    $plan = $this->getHarvestPlan();
    $plan->identifier = $harvest_plan_id;
    $this->harvestService->registerHarvest($plan);
  }

  /**
   * Get our harvest plan from the file system.
   */
  protected function getHarvestPlan() {
    $module_path = $this->appRoot . '/' . $this->moduleExtensionList->getPath('sample_content');
    $json = file_get_contents($module_path . '/harvest_plan.json');
    $plan = json_decode($json);
    $plan->extract->uri = 'file://' . $module_path . $plan->extract->uri;
    return $plan;
  }

  /**
   * Create dataset JSON, using string substitution for file paths.
   */
  private function createDatasetJsonFromTemplate() {
    $module_path = $this->moduleExtensionList->getPath('sample_content');
    $sample_content_template = $this->appRoot . '/' . $module_path . '/sample_content.template.json';
    $content = file_get_contents($sample_content_template);
    file_put_contents(
      $this->appRoot . '/' . $module_path . '/sample_content.json',
      $this->detokenize($content)
    );
  }

  /**
   * Replace path tokens with an actual file path.
   */
  private function detokenize($content) {
    $absolute_module_path = $this->appRoot . '/' . $this->moduleExtensionList->getPath('sample_content') . '/files';
    return str_replace('<!*path*!>', $absolute_module_path, $content);
  }

}
