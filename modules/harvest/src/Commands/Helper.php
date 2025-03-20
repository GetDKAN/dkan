<?php

namespace Drupal\harvest\Commands;

use Harvest\ResultInterpreter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Commands helper trait.
 *
 * @codeCoverageIgnore
 */
trait Helper {

  /**
   * Return Processed, Created, Updated, Failed counts from Harvest Run Result.
   */
  private function getResultCounts(array $result) {
    $interpreter = new ResultInterpreter($result);

    return [
      $interpreter->countProcessed(),
      $interpreter->countCreated(),
      $interpreter->countUpdated(),
      $interpreter->countFailed(),
    ];
  }

  /**
   * Display a list of run IDs and run info.
   *
   * @param array $runInfos
   *   Array of harvest run results, as returned from Harvester::harvest().
   *
   * @see Harvester::harvest()
   */
  private function renderHarvestRunsInfo(array $runInfos) {
    $table = new Table(new ConsoleOutput());
    $table->setHeaders(['run_id', 'processed', 'created', 'updated', 'errors']);
    $errors = [];

    foreach ($runInfos as $runInfo) {
      $run_id = $runInfo['identifier'] ?? NULL;
      $row = array_merge(
        [$run_id],
        $this->getResultCounts($runInfo)
      );
      $table->addRow($row);
      // Store error messages if we have them.
      $errors[$run_id] = $runInfo['errors'] ?? NULL;
    }
    $table->render();
    $this->renderHarvestRunsErrors($errors);
  }

  /**
   * Display errors.
   *
   * @param array $errors
   *   Nested array of error messages and the systems they belong to.
   *
   * @see self::renderHarvestRunsInfo()
   */
  private function renderHarvestRunsErrors(array $errors) {
    if ($errors) {
      foreach ($errors ?? [] as $run_id => $run_errors) {
        foreach ($run_errors ?? [] as $type => $messages) {
          foreach ($messages ?? [] as $id => $message) {
            $this->logger()->error('[' . $run_id . '][' . $type . '][' . $id . '] ' . $message);
          }
        }
      }
    }
  }

  /**
   * Render table for harvest run item status.
   */
  private function renderStatusTable($harvest_id, $run_id, $run) {
    $consoleOutput = new ConsoleOutput();

    if (empty($run['status']['extracted_items_ids'])) {
      $extract_status = $run['status']['extract'];

      $consoleOutput->writeln([
        '<warning>harvest id ' . $harvest_id . ' and run id ' . $run_id . ' extract status is ' . $extract_status . '</warning>',
        '<warning>No items were extracted.</warning>',
      ]);
    }
    else {
      $table = new Table($consoleOutput);
      $table->setHeaders(['item_id', 'extract', 'transform', 'load']);

      foreach ($run['status']['extracted_items_ids'] as $item_id) {
        $row = $this->generateItemStatusRow($item_id, $run['status'], $run['errors'] ?? []);
        $table->addRow($row);
      }

      $consoleOutput->writeln([
        '<info>' . $harvest_id . ' run id ' . $run_id . ' status </info>',
      ]);
      $table->render();
    }
  }

  /**
   * Generate a table row for harvest run item status.
   */
  private function generateItemStatusRow($item_id, $status, $errors) {
    $row = [];

    $row['item_id'] = $item_id;

    /* Extract */
    $row['extract'] = '[' . $status['extract'] . ']';

    /* transform */

    $row['transform'] = $this->generateItemTransformStatusRow($item_id, $status, $errors);

    /* load */
    $row['load'] = $this->generateItemLoadStatusRow($item_id, $status, $errors);

    return $row;
  }

  /**
   * Return a transform status for a harvest run item.
   */
  private function generateItemTransformStatusRow($item_id, $status, $errors) {
    /* transform */
    $transform_status = [];

    if (!empty($status['transform'])) {
      foreach ($status['transform'] as $class => $ids_status) {
        $transform_status[] = '- ' . $class . ': ' . $ids_status[$item_id];
      }
    }

    if (!empty($errors['transform'][$item_id])) {
      $transform_status[] = 'Errors: ';
      $transform_status[] = '- ' . $errors['transform'][$item_id];
    }

    if (!empty($errors['transform']['loading'])) {
      $transform_status[] = 'Loading Errors: ';
      $transform_status[] = '- ' . $errors['transform']['loading'];
    }

    return implode("\n", $transform_status);
  }

  /**
   * Return a load status for a harvest run item.
   */
  private function generateItemLoadStatusRow($item_id, $status, $errors) {
    $load_status = [];

    $load_status[] = '[' . $status['load'][$item_id] . ']';

    if ($status['load'][$item_id] == 'FAILURE') {

      $report = json_decode((string) $errors['load'][$item_id], TRUE);

      if (empty($report['errors'])) {
        // Probably a string and not a json object.
        $report['errors'] = [
          [
            'property' => '',
            'message' => $errors['load'][$item_id],
          ],
        ];
      }

      foreach ($report['errors'] as $error) {
        $load_status[] = '- ' . $error['property'] . ': ' . $error['message'];
      }
    }

    return implode("\n", $load_status);
  }

}
