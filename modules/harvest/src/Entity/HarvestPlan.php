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
 * @ContentEntityType(
 *   id = "harvest_plan",
 *   label = @Translation("Harvest Plan"),
 *   label_collection = @Translation("Harvest Plans"),
 *   label_singular = @Translation("harvest plan"),
 *   label_plural = @Translation("harvest plans"),
 *   label_count = @PluralTranslation(
 *     singular = "@count harvest plans",
 *     plural = "@count harvest plans",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\harvest\HarvestPlanListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\harvest\Form\HarvestPlanForm",
 *       "edit" = "Drupal\harvest\Form\HarvestPlanForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
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
 *     "collection" = "/admin/content/harvest-plan",
 *     "add-form" = "/harvest-plan/add",
 *     "canonical" = "/harvest-plan/{harvest_plan}",
 *     "edit-form" = "/harvest-plan/{harvest_plan}",
 *     "delete-form" = "/harvest-plan/{harvest_plan}/delete",
 *   },
 * )
 */
class HarvestPlan extends ContentEntityBase implements HarvestPlanInterface {

  /**
   * {@inheritDoc}
   *
   * Provides identifier and JSON data base fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);
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
    // String_long is a blob.
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
    // In the case of this entity, we only want the serialized 'data' property,
    // which is already serialized. Therefore, we have to DECODE it, so that
    // json_encode() can then RE-ENCODE it.
    return json_decode($this->get('data')->getString());
  }

}
