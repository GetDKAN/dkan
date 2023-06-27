<?php

namespace Drupal\metastore\Controller;

use Contracts\FactoryInterface as ContractsFactoryInterface;
use Drupal\common\JsonResponseTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\Storage\MetastoreEntityStorageInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Revision API for metastore items.
 *
 * @codeCoverageIgnore.
 */
class MetastoreRevisionController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  private $service;

  /**
   * Metastore dataset docs service.
   *
   * @var \Drupal\metastore\DatasetApiDocs
   */
  private $docs;

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.api_response'),
      $container->get('dkan.metastore.storage')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(MetastoreApiResponse $apiResponse, ContractsFactoryInterface $storageFactory) {
    $this->apiResponse = $apiResponse;
    $this->storageFactory = $storageFactory;
  }

  /**
   * Get all.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   The identifier for the datastore item.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function getAll(string $schema_id, string $identifier, Request $request) {
    try {

      $storage = $this->storageFactory->getInstance($schema_id);
      $entity = $storage->getEntityPublishedRevision($identifier) ?: $storage->getEntityLatestRevision($identifier);
      if (empty($entity)) {
        throw new MissingObjectException("No $schema_id found with identifier $identifier.");
      }

      foreach ($this->getRevisionIds($entity, $storage) as $revisionId) {
        $revision = $storage->getEntityStorage()->loadRevision($revisionId);
        $output[] = (object) [
          'identifier' => (string) $revisionId,
          'published' => $revisionId == $entity->getRevisionId(),
          'message' => $revision->get('revision_log')->getString(),
          'modified' => date('c', $revision->get('changed')->getString()),
          'state' => $revision->get('moderation_state')->getString(),
        ];
      }
      return $this->apiResponse->cachedJsonResponse($output, 200, [$schema_id], $request->query);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 404);
    }
  }

  /**
   * Implements GET method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Metastore item identifier.
   * @param string $revision_id
   *   Metastore revision identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   *
   * @throws \InvalidArgumentException
   *   When an unpublished or invalid resource is requested.
   */
  public function get(string $schema_id, string $identifier, string $revision_id, Request $request) {
    try {
      $storage = $this->storageFactory->getInstance($schema_id);
      $entity = $storage->getEntityPublishedRevision($identifier) ?: $storage->getEntityLatestRevision($identifier);
      if (empty($entity)) {
        throw new MissingObjectException("No $schema_id found with identifier $identifier.");
      }
      $revision = $storage->getEntityStorage()->loadRevision($revision_id);
      if (empty($revision) || $entity->id() != $revision->id()) {
        throw new MissingObjectException("This $schema_id $identifier has no revision with that identifier.");
      }
      $output = (object) [
        'identifier' => (string) $revision_id,
        'published' => $revision_id == $entity->getRevisionId(),
        'message' => $revision->get('revision_log')->getString(),
        'modified' => date('c', $revision->get('changed')->getString()),
        'state' => $revision->get('moderation_state')->getString(),
      ];
      return $this->apiResponse->cachedJsonResponse($output, 200, [$schema_id => [$identifier]], $request->query);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 404);
    }
  }

  /**
   * Create a new revision.
   *
   * @param string $schema_id
   *   The metastore schema ID (e.g. "dataset").
   * @param string $identifier
   *   Metastore item identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function post(string $schema_id, string $identifier, Request $request) {
    try {
      $storage = $this->storageFactory->getInstance($schema_id);
      $entity = $storage->getEntityPublishedRevision($identifier) ?: $storage->getEntityLatestRevision($identifier);
      if (empty($entity)) {
        throw new MissingObjectException("No $schema_id found with identifier $identifier.");
      }
      $entity->setNewRevision();
      $data = json_decode($request->getContent());
      $entity->set('moderation_state', $data->state);
      $entity->setRevisionLogMessage($data->message);
      $entity->save();
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
    $vid = $entity->getRevisionId();
    return $this->apiResponse->cachedJsonResponse([
      "endpoint" => "{$request->getRequestUri()}/{$vid}",
      "identifier" => (string) $vid,
    ], 201);
  }

  /**
   * Get a list of revision IDs for a metastore entity.
   *
   * @param \Drupal\Core\Entity\RevisionLogInterface $entity
   *   The current metastore entity object.
   * @param \Drupal\metastore\Storage\MetastoreEntityStorageInterface $storage
   *   DKAN metastore storage object.
   *
   * @return int[]
   *   An array of Drupal revision IDs.
   */
  protected function getRevisionIds(RevisionLogInterface $entity, MetastoreEntityStorageInterface $storage) {
    $entityStorage = $storage->getEntityStorage();
    $result = $entityStorage->getQuery()
      ->accessCheck(TRUE)
      ->allRevisions()
      ->condition($entity->getEntityType()->getKey('id'), $entity->id())
      ->sort($entity->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}
