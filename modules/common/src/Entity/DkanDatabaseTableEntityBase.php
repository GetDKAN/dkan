<?php

namespace Drupal\common\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Base class for shimming between Drupal Entity API and DKAN DatabaseTable.
 */
abstract class DkanDatabaseTableEntityBase extends ContentEntityBase {

  /**
   * Data field name.
   *
   * This is the name of the base field where JSON data will be stored,
   * consistent with the AbstractDatabaseTable default behavior.
   *
   * Override for a different field name.
   *
   * @var string
   */
  protected static string $dataFieldName = 'data';

  /**
   * {@inheritDoc}
   *
   * Provides an identifier and a data field, consistent with the default fields
   * for AbstractDatabaseTable.
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
    $base_fields[static::$dataFieldName] = BaseFieldDefinition::create('string_long')
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
