<?php

namespace Drupal\dkan_api\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dataset.
 */
class Dataset extends Api {

  use \Drupal\dkan_common\Util\ParentCallTrait;

  /**
   *
   * @var \Drupal\dkan_api\Storage\DrupalNodeDataset
   */
  protected $nodeDataset;

  /**
   * {@inheritdocs}.
   */
  public function __construct(ContainerInterface $container) {
    $this->parentCall(__FUNCTION__, $container);
    $this->nodeDataset = $container->get('dkan_api.storage.drupal_node_dataset');
  }

  /**
   * Get Storage.
   *
   * @return \Drupal\dkan_api\Storage\DrupalNodeDataset Dataset
   */
  protected function getStorage() {
    return $this->nodeDataset;
  }

  /**
   * Get Json Schema.
   *
   * @return string
   */
  protected function getJsonSchema() {

    /** @var \JsonSchemaProvider\Provider $provider */
    $provider = $this->container
      ->get('dkan_schema.json_schema_provider');
    return $provider->retrieve('dataset');
  }

}
