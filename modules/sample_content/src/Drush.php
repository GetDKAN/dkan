<?php

namespace Drupal\sample_content;

use Drupal\Core\Extension\ModuleExtensionList;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Output\ConsoleOutput;
use Drupal\harvest\Commands\Helper;

/**
 * Class.
 */
class Drush extends DrushCommands {
  use Helper;

  /**
   * The core extension module list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * Constructor for the Sample Content commands.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   Extension list.
   */
  public function __construct(ModuleExtensionList $extension_list_module) {
    $this->extensionListModule = $extension_list_module;
  }

  /**
   * Create sample content.
   *
   * @command dkan:sample-content:create
   */
  public function create() {
    $this->createJson();
    $harvester = $this->getHarvester("sample_content");
    $result = $harvester->harvest();

    $this->renderHarvestRunsInfo([['sample_content', $result]]);
  }

  /**
   * Remove sample content.
   *
   * @command dkan:sample-content:remove
   */
  public function remove() {
    $harvester = $this->getHarvester("sample_content");
    $result = $harvester->revert();

    $count = $result;

    $output = new ConsoleOutput();
    $output->write("{$count} items reverted for the 'sample_content' harvest plan.");
  }

  /**
   * Protected.
   */
  protected function getHarvestPlan() {
    $module_path = DRUPAL_ROOT . "/" . $this->extensionListModule->getPath('sample_content');

    $plan_path = $module_path . "/harvest_plan.json";
    $json = file_get_contents($plan_path);
    $plan = json_decode($json);

    $plan->extract->uri = "file://" . $module_path . $plan->extract->uri;

    return $plan;
  }

  /**
   * Private.
   */
  private function createJson() {
    $sample_content_path = $this->extensionListModule->getPath('sample_content');
    $sample_content_template = DRUPAL_ROOT . "/" . $sample_content_path . "/sample_content.template.json";
    $content = file_get_contents($sample_content_template);
    $new = $this->detokenize($content);
    file_put_contents(DRUPAL_ROOT . "/" . $sample_content_path . "/sample_content.json", $new);
  }

  /**
   * Private.
   */
  private function detokenize($content) {
    $absolute_module_path = DRUPAL_ROOT . "/" . $this->extensionListModule->getPath('sample_content') . "/files";
    return str_replace("<!*path*!>", $absolute_module_path, $content);
  }

}
