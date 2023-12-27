<?php

namespace Drupal\data_dictionary_widget\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for the Data Dictionary.
 */
class DataDictionary extends ControllerBase {

public static function getDataDictionaries(){
    $exsisting_identifiers = [];
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage
    ->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'data')
    ->condition('field_data_type', 'data-dictionary', '=');
    $nodes_ids = $query->execute();
    $nodes = $node_storage->loadMultiple($nodes_ids);
    foreach ($nodes as $node) {
      $exsisting_identifiers[$node->id()] .= $node->uuid();
    }

    return $exsisting_identifiers;
}

}