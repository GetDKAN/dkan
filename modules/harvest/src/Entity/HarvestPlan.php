<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
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
 *     "route_provider" = {
 *       "html" = "Drupal\harvest\Routing\HarvestPlanHtmlRouteProvider",
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
class HarvestPlan extends ContentEntityBase implements HarvestPlanInterface {

  /**
   * {@inheritDoc}
   *
   * Provides identifier and JSON data base fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);

    // The 'id' field is the unique identifier for each harvest plan row.
    $base_fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Identifier'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    // The 'data' field contains JSON which describes the harvest plan. The plan
    // must be an object with at least a property of 'identifier'.
    $base_fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Data'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 0,
        'settings' => [
          'rows' => 12,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'string',
        'weight' => 0,
        'label' => 'above',
      ])
      ->setDisplayConfigurable('view', TRUE);
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
