<?php

namespace Drupal\access_level_non_public\Plugin\DataModifier;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\common\Plugin\DataModifierBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a data modifier plugin to protect non-public datasets' resources.
 *
 * @DataModifier(
 *   id = "non_public_resource_protector",
 *   label = @Translation("Protects resources of non-public datasets"),
 *   result = @Translation("Resource hidden since dataset access level is non-public."),
 *   code = "401",
 * )
 */
class NonPublicResourceProtector extends DataModifierBase implements ContainerFactoryPluginInterface {

  /**
   * List of schemas to protect.
   *
   * @var array
   */
  private $schemasToModify = [
    'dataset',
    'distribution',
  ];

  /**
   * The route matching service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * NonPublicResourceProtector constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->database = $database;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('current_route_match')
    );
  }

  /**
   * Check if a resource needs to be protected.
   *
   * @param string $schema
   *   The schema id.
   * @param object|string $data
   *   Object, json or identifier string representing a dataset or distribution.
   *
   * @return bool
   *   TRUE if the data requires modification, FALSE otherwise.
   */
  public function requiresModification(string $schema, $data) {
    return in_array($schema, $this->schemasToModify)
      && !$this->alternateEndpoint()
      && $this->accessLevel($schema, $data) === 'non-public';
  }

  /**
   * Check if user requests one of the alternate dkan_alt_api endpoint.
   *
   * @return bool
   *   TRUE from alternate endpoints, FALSE otherwise.
   */
  private function alternateEndpoint() {
    $routeName = $this->routeMatch->getRouteName();
    return strpos($routeName, 'dkan_alt_api.') === 0;
  }

  /**
   * Check if a dataset or a distribution's parent has non-public access level.
   *
   * @param string $schema
   *   The schema id.
   * @param mixed $data
   *   Object, json or identifier string representing a dataset or distribution.
   *
   * @return bool
   *   TRUE if non-public, FALSE otherwise.
   */
  private function accessLevel(string $schema, $data) : string {
    // For distributions, check their parent dataset's access level.
    if ('distribution' === $schema) {
      return $this->parentDatasetAccessLevel($data);
    }
    return $this->datasetAccessLevel($data);
  }

  /**
   * Returns a dataset's access level.
   *
   * @param object|string $data
   *   A dataset object or json string.
   *
   * @return string
   *   The access level of the dataset.
   */
  private function datasetAccessLevel($data) {
    if (is_string($data)) {
      $data = json_decode($data);
    }
    return $data->accessLevel;
  }

  /**
   * Get the access level of a distribution's parent dataset.
   *
   * @param object|string $dist
   *   Object, json or identifier string representing a distribution.
   *
   * @return string
   *   The parent dataset's access level.
   */
  private function parentDatasetAccessLevel($dist) {
    $identifier = $this->getIdentifier($dist);
    $parentDataset = $this->getParentDataset($identifier);
    return $this->datasetAccessLevel($parentDataset);
  }

  /**
   * Get a distribution's identifier, from its object or json string.
   *
   * @param object|string $dist
   *   Object, json or identifier string representing a distribution.
   *
   * @return string
   *   The distribution's identifier.
   */
  private function getIdentifier($dist) : string {
    if (is_string($dist)) {
      if ($decoded = json_decode($dist)) {
        $dist = $decoded;
      }
      else {
        return $dist;
      }
    }
    return $dist->identifier;
  }

  /**
   * Get the metadata of a distribution's parent dataset.
   *
   * @param string $identifier
   *   The distribution's identifier.
   *
   * @return string|bool
   *   The dataset's metadata, or FALSE.
   */
  private function getParentDataset($identifier) {
    $datasets = $this->database->select('node__field_json_metadata', 'm')
      ->condition('m.field_json_metadata_value', '%accessLevel%', 'LIKE')
      ->condition('m.field_json_metadata_value', "%{$identifier}%", 'LIKE')
      ->fields('m', ['field_json_metadata_value'])
      ->execute()
      ->fetchCol();

    return reset($datasets);
  }

  /**
   * Protect potentially sensitive data in a dataset or distribution.
   *
   * @param string $schema
   *   The schema id.
   * @param object|string $data
   *   Object, json or identifier string representing a dataset or distribution.
   *
   * @return mixed
   *   Modified data, or FALSE.
   */
  public function modify(string $schema, $data) {
    if ('distribution' === $schema) {
      return $this->protectDistribution($data);
    }
    return $this->protectDataset($data);
  }

  /**
   * Protect dataset object or string.
   *
   * @param object|string $dataset
   *   A dataset object or json string.
   *
   * @return object|string
   *   The protected dataset.
   */
  private function protectDataset($dataset) {
    if (is_string($dataset)) {
      $datasetObj = json_decode($dataset);
      $datasetObj = $this->protectDatasetObject($datasetObj);
      return json_encode($datasetObj);
    }
    return $this->protectDatasetObject($dataset);
  }

  /**
   * Protect dataset object.
   *
   * @param object $dataset
   *   The dataset object.
   *
   * @return object
   *   The protected dataset object
   */
  private function protectDatasetObject($dataset) {
    if (isset($dataset->distribution) && is_array($dataset->distribution)) {
      foreach ($dataset->distribution as $key => &$dist) {
        $dataset->distribution[$key] = $this->protectDistributionObject($dist);
      }
    }
    return $dataset;
  }

  /**
   * Protect distribution, both object and json strings.
   *
   * @param object|string $dist
   *   A distribution object or json string.
   *
   * @return false|mixed|string
   *   A protected distribution.
   */
  private function protectDistribution($dist) {
    if (is_string($dist)) {
      $distObj = json_decode($dist);
      $distObj = $this->protectDistributionObject($distObj);
      return json_encode($distObj);
    }
    return $this->protectDistributionObject($dist);
  }

  /**
   * Protect distribution object.
   *
   * @param object $dist
   *   A distribution object.
   *
   * @return object
   *   A protected distribution object, with an explanation.
   */
  private function protectDistributionObject($dist) {
    unset($dist);
    return (object) ['title' => $this->message()];
  }

}
