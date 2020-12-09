<?php

namespace Drupal\harvest_dashboard\Controller;

use Drupal\Core\Url;

/**
 * Controller.
 */
class Controller {

  /**
   * A list of harvests and some status info.
   */
  public function harvests(): array {

    /** @var \Drupal\harvest\Service $harvestService */
    $harvestService = \Drupal::service('dkan.harvest.service');

    $headers = ["Harvest ID", "Extract Status", "Last Run", "# of Datasets"];
    $renderArray = $this->table($headers);

    foreach ($harvestService->getAllHarvestIds() as $harvestId) {
      $tds = [$harvestId, 'Registered', 'Never', 'N/A'];
      $renderArray[$harvestId] = $this->tr($tds);

      $runIds = $harvestService->getAllHarvestRunInfo($harvestId);
      $runId = end($runIds);

      if ($runId) {
        $json = $harvestService->getHarvestRunInfo($harvestId, $runId);
        $info = json_decode($json);

        date_default_timezone_set('EST');
        $time = date('m/d/y H:m:s T', $runId);

        $datasets = array_keys((array) $info->status->load);

        $tds = ["", $info->status->extract, $time, count($datasets)];
        $renderArray[$harvestId] = $this->tr($tds);

        $url = Url::fromRoute("dkan.harvest.dashboard.datasets",
          ['harvestId' => $harvestId]);

        $renderArray[$harvestId][1] = array_merge($this->html('<td>'),
          ['#type' => 'link', '#url' => $url, '#title' => $harvestId]);
      }
    }

    return $renderArray;
  }

  /**
   * Datasets information for a specific harvest.
   */
  public function harvestDatasets($harvestId) {
    $load = $this->getHarvestLoadStatus($harvestId);
    $datasets = array_keys($load);

    $headers = array_merge(["Dataset ID", "Title", "Modified Date (Metadata)"],
      ["Modified Date (DKAN)", "Status", "Resources"]);

    $renderArray = $this->table($headers);

    foreach ($datasets as $datasetId) {
      $dataset = $this->getDataset($datasetId);
      $renderArray[$datasetId] = $this->html('<tr>');

      $renderArray[$datasetId][1] = $this->td($datasetId);
      $renderArray[$datasetId][2] = [
        '#prefix' => '<td colspan="5">',
        '#suffix' => '</td>',
        '#markup' => 'Not Published',
      ];

      if (!empty($dataset)) {
        $dkanModified = $dataset->{"%modified"};

        $tds = array_merge([$datasetId, $dataset->title, $dataset->modified],
          [$dkanModified, $load[$datasetId]]);

        $renderArray[$datasetId] = $this->tr($tds);

        $renderArray[$datasetId][6] = $this->html("<td>");
        $renderArray[$datasetId][6][] = $this->resourcesTable(
          $this->getResources($datasetId));
      }
    }

    return $renderArray;
  }

  /**
   * Private.
   */
  private function resourcesTable($resources) {
    if (empty($resources)) {
      return ['#markup' => "No Resources"];
    }

    $headers = ["Identifier", "Local File", "", "Datastore", ""];
    $renderArray = $this->table($headers);

    $service = \Drupal::service('dkan.datastore.import_info');
    foreach ($resources as $resource) {
      $identifier = $resource->identifier;

      $data = $resource->data->{"%Ref:downloadURL"}[0]->data;

      $tds = [$identifier, "N/A", "", "N/A", ""];

      $renderArray[$identifier] = $this->tr($tds);

      if ($data) {
        $info = $service->getItem($data->identifier, $data->version);

        $tds = [
          $identifier,
          $info->fileFetcherStatus,
          "{$info->fileFetcherPercentDone}%",
          $info->importerStatus,
          "{$info->importerPercentDone}%",
        ];

        $renderArray[$identifier] = $this->tr($tds);
      }
    }

    return $renderArray;
  }

  /**
   * Private.
   */
  private function getHarvestLoadStatus($harvestId): array {
    $harvest = \Drupal::service('dkan.harvest.service');

    $runIds = $harvest->getAllHarvestRunInfo($harvestId);
    $runId = end($runIds);

    $json = $harvest->getHarvestRunInfo($harvestId, $runId);
    $info = json_decode($json);
    $status = $info->status;
    return (array) $status->load;
  }

  /**
   * Private.
   */
  private function getDataset($datasetId) {
    $metastore = \Drupal::service('dkan.metastore.service');

    try {
      $datasetJson = $metastore->get('dataset', $datasetId);
      $dataset = json_decode($datasetJson);
      return $dataset;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Private.
   */
  private function getResources($datasetId) {
    $dataset = $this->getDataset($datasetId);
    return isset($dataset->{"%Ref:distribution"})
      ? $dataset->{"%Ref:distribution"} : [];
  }

  /**
   * Private.
   */
  private function table($headers): array {
    $renderArray = $this->html('<table>');

    $renderArray['table_header'] = $this->html('<tr>');

    foreach ($headers as $header) {
      $renderArray['table_header'][] = $this->th($header);
    }

    return $renderArray;
  }

  /**
   * Private.
   */
  private function tr($tds): array {
    $renderArray = $this->html("<tr>");
    $counter = 1;
    foreach ($tds as $td) {
      $renderArray[$counter] = $this->td($td);
      $counter++;
    }
    return $renderArray;
  }

  /**
   * Private.
   */
  private function th(string $content) {
    $html = $this->html('<th>');
    $html['#markup'] = $content;
    return $html;
  }

  /**
   * Private.
   */
  private function td(string $content) {
    $html = $this->html('<td>');
    $html['#markup'] = $content;
    return $html;
  }

  /**
   * Private.
   */
  private function html(string $tag) {
    $closing = str_replace("<", "</", $tag);
    return [
      '#prefix' => $tag,
      '#suffix' => $closing,
    ];
  }

}
