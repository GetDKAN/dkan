<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\harvest\HarvestHashInterface;

/**
 * Defines the harvest hash entity class.
 *
 * Hash records are generated when a harvest is run, and uses the harvest plan
 * to generate datasets. We hash the JSON specifying the dataset and store it in
 * one of these entities, keeping it indexed against the Data node UUID that was
 * generated to store the dataset, as well as the plan ID of the harvest.
 *
 * This information is used to determine whether subsequent harvest plan runs
 * must generate more dataset nodes, or whether we can keep the current ones.
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
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   internal = TRUE,
 * )
 *
 * Internal is TRUE so that JSONAPI does not provide a REST API for this entity.
 */
class HarvestHash extends ContentEntityBase implements HarvestHashInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    // Parent will give us an 'id' field because we annotated it as the id key.
    $fields = parent::baseFieldDefinitions($entity_type);

    // Data node UUID. This is a UUID to a Data node, probably of type
    // 'dataset'.
    // Note that this is a UUID by convention, and could be any string.
    // @todo Add uuid constraint.
    $fields['data_uuid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Data node UUID'))
      ->setDescription(t('The Data node UUID.'))
      ->setReadOnly(FALSE)
      // Require the UUID to be set, because the node it references must already
      // exist.
      ->setRequired(TRUE);

    // Harvest plan id.
    $fields['harvest_plan_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Harvest Plan ID'))
      ->setDescription(t('The harvest plan ID.'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ]);

    // Hash for the harvested resource. This is a hash of the JSON that was used
    // to harvest the datastore.
    // @see \Harvest\ETL\Load\Load::itemState()
    // @see \Harvest\Util::generateHash()
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
   *   Remove when we're no longer using that interface for harvest_hashes.
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return (object) [
      'harvest_plan_id' => $this->get('harvest_plan_id')->getString(),
      'hash' => $this->get('hash')->getString(),
    ];
  }

}
