<?php

namespace Drupal\harvest;

use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Controller.
 */
class DashboardController {

  use StringTranslationTrait;

  const HARVEST_HEADERS = [
    'Harvest ID',
    'Extract Status',
    'Last Run',
    '# of Datasets',
  ];

  /**
   * Harvest service.
   *
   * @var \Drupal\harvest\HarvestService
   */
  protected $harvest;

  /**
   * Controller constructor.
   */
  public function __construct() {
    $this->harvest = \Drupal::service('dkan.harvest.service');
  }

  /**
   * A list of harvests and some status info.
   */
  public function harvests(): array {
    // Display dates using the site timezone.
    date_default_timezone_set(date_default_timezone_get());

    $rows = [];
    foreach ($this->harvest->getAllHarvestIds() as $harvestPlanId) {
      if ($runId = $this->harvest->getLastHarvestRunId($harvestPlanId)) {
        // There is a run identifier, so we should get that info.
        $info = json_decode($this->harvest->getHarvestRunInfo($harvestPlanId, $runId));

        $rows[] = $this->buildHarvestRow($harvestPlanId, $runId, $info);
      }
      else {
        // There is no recent run identifier, so we should display some default
        // info to the user.
        $rows[] = $this->buildHarvestRow($harvestPlanId, '', NULL);
      }
    }

    return [
      '#theme' => 'table',
      '#header' => self::HARVEST_HEADERS,
      '#rows' => $rows,
      '#attributes' => ['class' => 'dashboard-harvests'],
      '#attached' => ['library' => ['harvest/style']],
      '#empty' => 'No harvests found',
    ];
  }

  /**
   * Private.
   */
  private function buildHarvestRow(string $harvestId, string $runId, $info): array {
    $url = Url::fromRoute(
      'datastore.datasets_import_status_dashboard',
      ['harvest_id' => $harvestId]
    );

    // Default values if there is no run information. This will show the harvest
    // in the list, even if there is no run status to report.
    $row = [
      'harvest_link' => Link::fromTextAndUrl($harvestId, $url),
      'extract_status' => [
        'data' => 'REGISTERED',
        'class' => 'registered',
      ],
      'last_run' => 'never',
      'dataset_count' => 'unknown',
    ];
    // Add run information if available.
    if ($info) {
      $row['extract_status'] = [
        'data' => $info->status->extract,
        'class' => strtolower($info->status->extract),
      ];
      $row['last_run'] = date('m/d/y H:m:s T', $runId);
      $row['dataset_count'] = count(array_keys((array) $info->status->load));
    }
    return $row;
  }

}
