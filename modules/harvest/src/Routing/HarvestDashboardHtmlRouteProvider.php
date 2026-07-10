<?php

namespace Drupal\harvest\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides HTML routes for entities with administrative pages.
 *
 * We override for harvest-oriented dashboards so that they have consistent
 * permissions handling.
 */
class HarvestDashboardHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritDoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    // Get the ones Drupal can do.
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();
    if ($entity_type_id === 'harvest_plan') {
      if ($run_route = $this->getOperationFormRoute($entity_type, 'run')) {
        $collection->add('entity.' . $entity_type_id . '.run_form', $run_route);
      }
    }
    return $collection;
  }

  /**
   * Generalized operation route generator.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param string $operation
   *   Operation name.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available. NULL otherwise.
   */
  protected function getOperationFormRoute(EntityTypeInterface $entity_type, string $operation): ?Route {
    if ($entity_type->hasLinkTemplate($operation . '-form')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate($operation . '-form'));
      $route
        ->addDefaults([
          '_entity_form' => $entity_type_id . '.' . $operation,
        ])
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ])
        ->setRequirement('_entity_access', $entity_type_id . '.' . $operation);
      return $route;
    }
    return NULL;
  }

}
