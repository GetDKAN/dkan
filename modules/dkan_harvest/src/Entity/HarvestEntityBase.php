<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Some common code for Harvest entities.
 */
class HarvestEntityBase extends ContentEntityBase {

  /**
   * Generic field definition for a string identifier.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   Entity label.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   (Optional) Entity description.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field we want.
   */
  protected static function getBaseFieldIdentifier(
    TranslatableMarkup $label,
    ?TranslatableMarkup $description = NULL
  ): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel($label)
      ->setDescription($description)
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);
  }

  /**
   * Generic field definition for JSON blob string.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   Entity label.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   (Optional) Entity description.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field we want.
   */
  protected static function getBaseFieldJsonData(
    TranslatableMarkup $label,
    ?TranslatableMarkup $description = NULL
  ): BaseFieldDefinition {
    return BaseFieldDefinition::create('string_long')
      ->setLabel($label)
      ->setDescription($description)
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
  }

  /**
   * Generic field definition for a UUID field with unlimited cardinality.
   *
   * Note: DKAN currently uses the fields defined here as UUIDs only by
   * convention. Any string could be specified in the harvest.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   Entity label.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   (Optional) Entity description.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field we want.
   */
  protected static function getBaseFieldUnlimitedCardinalityUuidField(
    TranslatableMarkup $label,
    ?TranslatableMarkup $description = NULL
  ): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel($label)
      ->setDescription($description)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setReadOnly(FALSE)
      ->setStorageRequired(FALSE)
      ->setRequired(FALSE);
  }

}
