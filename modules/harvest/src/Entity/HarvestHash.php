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
 *     "list_builder" = "Drupal\harvest\HarvestHashListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\harvest\Form\HarvestHashForm",
 *       "edit" = "Drupal\harvest\Form\HarvestHashForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "harvest_hashes",
 *   admin_permission = "administer harvest hash",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   links = {
 *     "collection" = "/admin/content/harvest-hash",
 *     "add-form" = "/harvest-hash/add",
 *     "canonical" = "/harvest-hash/{harvest_hash}",
 *     "edit-form" = "/harvest-hash/{harvest_hash}/edit",
 *     "delete-form" = "/harvest-hash/{harvest_hash}/delete",
 *   },
 * )
 */
class HarvestHash extends ContentEntityBase implements HarvestHashInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    // The node id is an integer, using the IntegerItem field item class.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Harvest hash ID'))
      ->setDescription(t('The harvest hash ID for this record.'))
      ->setReadOnly(TRUE);

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

    // @todo Do we want an entity reference here?
//    $fields['dataset'] = BaseFieldDefinition::create('entity_reference')
//      ->setLabel(t('Dataset node ID'))
//      ->setDescription(t('The node ID of the referenced dataset.'))
//      ->setSettings([
//        'target_type' => 'node',
//      ]);

    return $fields;
  }

}
