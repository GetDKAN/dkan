<?php

namespace Drupal\metastore\Entity;

use Drupal\Core\Entity\EntityRepository;

class ResourceMappingRepository extends EntityRepository {

  public function loadMappingByIdVersionPerspective($identifier, $version, $perspective) : ?ResourceMapping {
    $query = \Drupal::entityQuery('resource_mapping');
    $query->condition('identifier', $identifier)
      ->condition('version', $version)
      ->condition('perspective', $perspective);
    $map_ids = $query->execute();
    if ($id = reset($map_ids)) {
      return ResourceMapping::load($id);
    }
    return NULL;
  }

}
