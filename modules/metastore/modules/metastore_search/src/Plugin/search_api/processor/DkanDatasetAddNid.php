<?php

namespace Drupal\metastore_search\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds nid property to the indexed datasets.
 *
 * @SearchApiProcessor(
 *   id = "dkan_dataset_add_nid",
 *   label = @Translation("DKAN Dataset nid"),
 *   description = @Translation("Adds the item's nid to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */

class DkanDatasetAddNid extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function isValid(string $dataset_id): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if ($datasource) {
      $definition = [
        'label' => $this->t('nid'),
        'description' => $this->t('The node id of the dataset.'),
        'type' => 'string',
        'is_list' => FALSE,
        'processor_id' => $this->getPluginId(),
      ];
      $properties['search_api_nid'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $id = $item->getId();
    if ($id) {
      $uuid = str_replace("dkan_dataset/", "", $id);
      $nid = \Drupal::service('entity.repository')->loadEntityByUuid('node', $uuid)->id();

      $fields = $item->getFields(FALSE);
      $fields = $this->getFieldsHelper()->filterForPropertyPath($fields, $item->getDatasourceId(), 'search_api_nid');

      foreach ($fields as $field) {
        $field->addValue($nid);
      }
    }
  }

}
