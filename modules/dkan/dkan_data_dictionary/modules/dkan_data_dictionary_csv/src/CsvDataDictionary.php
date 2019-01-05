<?php

namespace Dkan\DataDictionary;

use League\Csv\Reader;

/**
 * CsvDataDictionary Class.
 *
 * Validator metadata.
 */
class CsvDataDictionary extends DataDictionaryBase {

  /**
   * {@inheritdoc}
   */
  public static function validateSchema($descriptor) {
    $is_file = filter_var($descriptor, FILTER_VALIDATE_URL);

    try {
      if ($is_file) {
        Reader::createFromPath($descriptor, 'r');
      }
      else {
        Reader::createFromString($descriptor);
      }
    }
    catch (\Exception $e) {
      return array($e->getMessage());
    }

    return array();
  }

  /**
   * {@inheritdoc}
   */
  public static function dictionaryFormatterView($langcode, $item, $display) {
    if ($display['type'] == 'table_data_dictionary') {
      $reader = NULL;
      $descriptor = $item['value'];
      $is_file = filter_var($descriptor, FILTER_VALIDATE_URL);

      try {
        if ($is_file) {
          $reader = Reader::createFromPath($descriptor, 'r');
        }
        else {
          $reader = Reader::createFromString($descriptor);
        }
      }
      catch (\Exception $e) {
        watchdog('CsvDataDictionary', "Failed to parse the CSV discriptor: @descriptor.", WATCHDOG_ERROR);
      }

      if (empty($reader)) {
        return array(
          '#markup' => t("Failed to parse the CSV descriptor"),
        );
      }

      $reader->setHeaderOffset(0);
      $headers = $reader->getHeader();

      $rows = array();

      foreach ($reader as $key => $record) {
        $row = array();

        foreach ($record as $item) {
          $row[] = array('data' => $item, 'class' => array('dkan-datadict-item', 'dkan-datadict-' . $key));
        }

        $rows[] = $row;
      }

      return array(
        '#theme' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
        '#attributes' => array('class' => array('dkan-datadict', 'json-schema')),
      );
    }
    else {
      // If the supplied value isn't valid JSON or it is valid JSON but
      // isn't a schema containing fields - simply output the raw text.
      return array('#markup' => $descriptor);
    }
  }

}
