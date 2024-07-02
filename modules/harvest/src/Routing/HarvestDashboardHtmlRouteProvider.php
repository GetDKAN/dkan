<?php

namespace Drupal\harvest\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides HTML routes for entities with administrative pages.
 *
 * We override for harvest-oriented dashboards so that they have consistent
 * permissions handling.
 */
class HarvestDashboardHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritDoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
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
