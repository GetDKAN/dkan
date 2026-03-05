<?php

namespace Drupal\datastore_data_preview\Service;

use Drupal\metastore\MetastoreService;
use Drupal\node\NodeInterface;

/**
 * Resolves distribution identifiers to "identifier__version" resource IDs.
 */
class ResourceIdResolver {

  public function __construct(
    protected MetastoreService $metastore,
  ) {}

  /**
   * Resolve any resource identifier to "identifier__version" format.
   *
   * Accepts already-formatted "identifier__version" strings (passthrough)
   * or distribution UUIDs (resolved via metastore).
   *
   * @param string $resource_id
   *   A distribution UUID or "identifier__version" string.
   *
   * @return string|null
   *   Resolved ID or NULL if unresolvable.
   */
  public function resolve(string $resource_id): ?string {
    if (str_contains($resource_id, '__')) {
      return $resource_id;
    }

    try {
      $json = $this->metastore->get('distribution', $resource_id);
      $metadata = json_decode($json);
      $ref = $metadata->data->{'%Ref:downloadURL'}[0]->data ?? NULL;
      if ($ref && !empty($ref->identifier) && !empty($ref->version)) {
        return $ref->identifier . '__' . $ref->version;
      }
    }
    catch (\Exception $e) {
      // Distribution not found.
    }

    return NULL;
  }

  /**
   * Resolve from a distribution node's metadata.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A distribution node.
   *
   * @return string|null
   *   Resolved ID or NULL if not a valid distribution.
   */
  public function resolveFromNode(NodeInterface $node): ?string {
    $json = $node->get('field_json_metadata')->value ?? '';
    if (!$json) {
      return NULL;
    }

    $metadata = json_decode($json);
    if (!$metadata) {
      return NULL;
    }

    $ref = $metadata->data->{'%Ref:downloadURL'}[0]->data ?? NULL;
    if (!$ref || empty($ref->identifier) || empty($ref->version)) {
      return NULL;
    }

    return $ref->identifier . '__' . $ref->version;
  }

}
