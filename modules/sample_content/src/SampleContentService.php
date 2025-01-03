<?php

namespace Drupal\sample_content;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\harvest\HarvestService;

/**
 * Manage sample content for both demo purposes and for testing.
 */
class SampleContentService {

  /**
   * Harvest service.
   */
  private HarvestService $harvestService;

  /**
   * Absolute path to the sample_content module.
   */
  private string $modulePath;

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
    $this->modulePath = $appRoot . '/' . $moduleExtensionList->getPath('sample_content');
    $this->harvestService = $harvestService;
  }

  /**
   * Register our plan as the given harvest plan ID.
   *
   * @param string $harvest_plan_id
   *   Harvest plan id.
   */
  public function registerSampleContentHarvest(string $harvest_plan_id): string {
    $this->createDatasetJsonFileFromTemplate();
    $plan = $this->getHarvestPlan();
    $plan->identifier = $harvest_plan_id;
    return $this->harvestService->registerHarvest($plan);
  }

  /**
   * Create dataset JSON, using string substitution for file paths.
   *
   * @return string
   *   Absolute path to the sample content JSON file.
   */
  public function createDatasetJsonFileFromTemplate(): string {
    $sample_content_template = $this->modulePath . '/sample_content.template.json';
    $content = file_get_contents($sample_content_template);
    $sample_content_json = $this->modulePath . '/sample_content.json';
    file_put_contents($sample_content_json, $this->detokenize($content));
    return $sample_content_json;
  }

  /**
   * Get our harvest plan from the file system.
   */
  public function getHarvestPlan(): object {
    $json = file_get_contents($this->modulePath . '/harvest_plan.json');
    $plan = json_decode($json);
    $plan->extract->uri = 'file://' . $this->modulePath . $plan->extract->uri;
    return $plan;
  }

  /**
   * Replace path tokens with an actual file path.
   */
  protected function detokenize($content): string {
    $absolute_file_path = $this->modulePath . '/files';
    return str_replace('<!*path*!>', $absolute_file_path, $content);
  }

}
