<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\harvest\HarvestHashInterface;

/**
 * Defines the harvest hash entity class.
 *
 * @ContentEntityType(
 *   id = "harvest_hash",
 *   label = @Translation("Harvest Hash"),
 *   label_collection = @Translation("Harvest Hashes"),
 *   label_singular = @Translation("harvest hash"),
 *   label_plural = @Translation("harvest hashes"),
 *   label_count = @PluralTranslation(
 *     singular = "@count harvest hashes",
 *     plural = "@count harvest hashes",
 *   ),
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "harvest_hashes",
 *   admin_permission = "administer harvest hash",
 *   entity_keys = {
 *     "id" = "dataset_uuid",
 *   },
 *   internal = TRUE,
 * )
 *
 * Internal is TRUE so that JSONAPI does not provide a REST API for this entity.
 */
class HarvestHash extends ContentEntityBase implements HarvestHashInterface, \JsonSerializable {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    // The UUID field uses the uuid_field type which ensures that a new UUID will automatically be generated when an entity is created.
    $fields['dataset_uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The node UUID.'))
      ->setReadOnly(FALSE)
        // Require the UUID to be set, because the node it references must already
        // exist.
      ->setRequired(TRUE);

    // The title is StringItem, the default value is an empty string and defines a property constraint for the
    // value to be at most 255 characters long.
    $fields['harvest_plan_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Harvest Plan ID'))
      ->setDescription(t('The harvest plan ID.'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ]);

    // The title is StringItem, the default value is an empty string and defines a property constraint for the
    // value to be at most 255 characters long.
    $fields['hash'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Harvest hash'))
      ->setDescription(t('Harvest hash.'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ]);

    return $fields;
  }

  /**
   * {@inheritDoc}
   *
   * @todo This is for backwards compatibility with DatabaseTableInterface.
   *   Remote when we're no longer using that interface for harvest_hashes.
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return (object) [
      'harvest_plan_id' => $this->get('harvest_plan_id')->getString(),
      'hash' => $this->get('hash')->getString(),
    ];
  }

}
