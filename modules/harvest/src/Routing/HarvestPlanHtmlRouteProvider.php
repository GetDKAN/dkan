<?php

namespace Drupal\harvest\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides HTML routes for entities with administrative pages.
 */
class HarvestPlanHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type) {
    return $this->getEditFormRoute($entity_type);
  }

  /**
   * {@inheritDoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    // Require dkan.harvest.dashboard permission to view the collection.
    $route = NULL;
    if ($route = parent::getCollectionRoute($entity_type)) {
      $required_permissions = ['dkan.harvest.dashboard'];
      if ($permission = $route->getRequirement('_permission')) {
        $required_permissions += [$permission];
      }
      // Use + to specify OR logic in permissions.
      $route->setRequirement('_permission', implode('+', $required_permissions));
    }
    return $route;
  }

}
