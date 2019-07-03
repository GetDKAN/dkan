<?php

namespace Drupal\dkan_datastore\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Api.
 *
 * @package Drupal\dkan_datastore\Controller
 */
class Api extends ControllerBase {

  /**
   * Drupal service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Factory to generate various dkan classes.
   *
   * @var \Drupal\dkan_common\Service\Factory
   */
  protected $dkanFactory;

  /**
   * Drupal node dataset storage.
   *
   * @var \Drupal\dkan_api\Storage\DrupalNodeDataset
   */
  protected $storage;

  /**
   * Datastore manager builder.
   *
   * @var \Drupal\dkan_datastore\Manager\DatastoreManagerBuilder
   */
  protected $managerBuilder;

  /**
   * Api constructor.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->dkanFactory = $container->get('dkan.factory');
    $this->storage = $container->get('dkan_api.storage.drupal_node_dataset');
    $this->storage->setSchema('dataset');
    $this->managerBuilder = $container->get('dkan_datastore.manager.datastore_manager_builder');
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
  }

  /**
   * Returns the dataset along with datastore headers and statistics.
   *
   * @param string $uuid
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function datasetWithSummary($uuid) {
    try {
      $dataset = $this->storage->retrieve($uuid);
      $data = json_decode($dataset);

      // For now, use the first resource's uuid or that of the dataset.
      // @Todo: Address datasets with multiple resources once frontend is set.
      if (isset($data->distribution[0]->identifier)) {
        $dist_uuid = $data->distribution[0]->identifier;
      }
      else {
        $dist_uuid = $uuid;
      }
      // Add columns and datastore_statistics to dataset.
      $manager = $this->managerBuilder->buildFromUuid($dist_uuid);
      if ($manager) {
        $headers = $manager->getTableHeaders();
        $data->columns = $headers;
        $data->datastore_statistics = [
          'rows' => $manager->numberOfRecordsImported(),
          'columns' => count($headers),
        ];
      }

      return $this->dkanFactory
        ->newJsonResponse(
          $data,
          200,
          ["Access-Control-Allow-Origin" => "*"]
        );
    }
    catch (\Exception $e) {
      return $this->dkanFactory
        ->newJsonResponse(
          (object) [
            'message' => $e->getMessage(),
          ],
          404
        );
    }
  }

}
