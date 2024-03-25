<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\harvest\HarvestPlanInterface;

/**
 * Defines the harvest plan entity class.
 *
 * The entity stores an identifier for the plan ('id') and a blob of JSON to
 * represent the plan ('data'). This is not the JSON of the harvest, but the
 * DKAN harvest plan. See components.schemas.harvestPlan within
 * modules/harvest/docs/openapi_spec.json for the schema of a plan.
 *
 * The plan JSON must contain an object with a property named 'identifier'. The
 * 'id' field of this entity must contain the same value as that identifier.
 *
 * @ContentEntityType(
 *   id = "harvest_plan",
 *   label = @Translation("Harvest Plan"),
 *   label_collection = @Translation("Harvests"),
 *   label_singular = @Translation("harvest plan"),
 *   label_plural = @Translation("harvest plans"),
 *   label_count = @PluralTranslation(
 *     singular = "@count harvest plans",
 *     plural = "@count harvest plans",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\harvest\HarvestPlanListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\harvest\ContentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\harvest\Routing\HarvestDashboardHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "harvest_plans",
 *   admin_permission = "administer harvest plan",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "collection" = "/admin/dkan/harvest",
 *     "canonical" = "/harvest-plan/{harvest_plan}",
 *   },
 *   internal = TRUE,
 * )
 *
 * Canonical must be supplied for the route builder, but is not currently used.
 *
 * Internal = TRUE tells JSON:API not to expose this entity. We have our own
 * harvest API, so we don't want this.
 *
 * @todo Add fields for each element of the harvestPlan schema.
 * @todo Add links and handlers for register, run, and deregister operations.
 */
final class HarvestPlan extends HarvestEntityBase implements HarvestPlanInterface {

  /**
   * {@inheritDoc}
   *
   * Provides identifier and JSON data base fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);

    // The 'id' field is the unique identifier for each harvest plan row.
    $base_fields['id'] = static::getBaseFieldIdentifier(
      new TranslatableMarkup('Identifier')
    );
    // The 'data' field contains JSON which describes the harvest plan. The plan
    // must be an object with at least a property of 'identifier'.
    $base_fields['data'] = static::getBaseFieldJsonData(new TranslatableMarkup('Data'));

    return $base_fields;
  }

  /**
   * {@inheritDoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return $this->getPlan();
  }

  /**
   * {@inheritDoc}
   */
  public function getPlan(): object {
    return json_decode($this->get('data')->getString());
  }

}
