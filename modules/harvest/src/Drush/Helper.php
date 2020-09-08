<?php

namespace Drupal\harvest\Drush;

use Drupal\harvest\Storage\DatabaseTable;
use Harvest\ETL\Factory;
use Harvest\Harvester;
use Harvest\ResultInterpreter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Trait Helper.
 *
 * @codeCoverageIgnore
 */
trait Helper {

  /**
   * Private..
   */
  private function getHarvester($id) {

    if (!method_exists($this, 'getHarvestPlan')) {
      throw new \Exception("Drupal\harvest\Drush\Helper requires the host to implement the getHarvestPlan method.");
    }

    return new Harvester(new Factory($this->getHarvestPlan($id),
      $this->getStorage($id, "item"),
      $this->getStorage($id, "hash")));
  }

  /**
   * Private..
   */
  private function getPlanStorage() {
    $connection = \Drupal::service('database');
    return new DatabaseTable($connection, "harvest_plans");
  }

  /**
   * Private..
   */
  private function getStorage($id, $type) {
    $connection = \Drupal::service('database');
    return new DatabaseTable($connection, "harvest_{$id}_{$type}");
  }

  /**
   * Private..
   */
  private function renderResult($result) {
    $interpreter = new ResultInterpreter($result);

    $table = new Table(new ConsoleOutput());
    $table->setHeaders(['processed', 'created', 'updated', 'errors']);
    $table->addRow([
      $interpreter->countProcessed(),
      $interpreter->countCreated(),
      $interpreter->countUpdated(),
      $interpreter->countFailed(),
    ]);
    $table->render();
  }

  /**
   * Render table for harvest run item status.
   */
  private function renderStatusTable($harvest_id, $run_id, $run) {
    if (empty($run['status']['extracted_items_ids'])) {
      $extract_status = $run['status']['extract'];

      (new ConsoleOutput())->writeln(
        ["<warning>harvest id $harvest_id and run id $run_id extract status is $extract_status</warning>",
          "<warning>No items were extracted.</warning>",
        ]
      );
    }
    else {
      $table = new Table(new ConsoleOutput());
      $table->setHeaders(["item_id", "extract", "transform", "load"]);

      foreach ($run['status']['extracted_items_ids'] as $item_id) {
        $row = $this->generateItemStatusRow($item_id, $run['status'], $run['errors']);
        $table->addRow($row);
      }

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
    $row['extract'] = "[" . $status['extract'] . "]";

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

    switch ($status['load'][$item_id]) {
      case 'FAILURE':
        $load_status[] = '[FAILURE]';

        $report = json_decode($errors['load'][$item_id], TRUE);

        foreach ($report['errors'] as $error) {
          $load_status[] = '- ' . $error['property'] . ': ' . $error['message'];
        }
        break;

      case 'SUCCESS':
        $load_status[] = '[SUCCESS]';
        break;

      default:
        $load_status[] = '[UNKNOWN]';
        break;
    }

    return implode("\n", $load_status);
  }

}
