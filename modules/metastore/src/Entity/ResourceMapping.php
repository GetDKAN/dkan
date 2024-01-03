<?php

namespace Drupal\metastore\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\metastore\ResourceMappingInterface;

/**
 * Defines the resource mapping entity class.
 *
 * Used as storage by \Drupal\metastore\ResourceMapper.
 *
 * @ContentEntityType(
 *   id = "resource_mapping",
 *   label = @Translation("Resource mapping"),
 *   label_collection = @Translation("Resource mappings"),
 *   label_singular = @Translation("resource mapping"),
 *   label_plural = @Translation("resource mappings"),
 *   label_count = @PluralTranslation(
 *     singular = "@count resource mapping",
 *     plural = "@count resource mappings",
 *   ),
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "dkan_metastore_resource_mapper",
 *   admin_permission = "administer resource mapping",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 * )
 */
class ResourceMapping extends ContentEntityBase implements ResourceMappingInterface, \JsonSerializable {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);
    $base_fields['identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resource identifier'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    $base_fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Version'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    $base_fields['filePath'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('File Path'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    $base_fields['perspective'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Perspective'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    $base_fields['mimeType'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('MIME Type'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    $base_fields['checksum'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Checksum'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    return $base_fields;
  }

  /**
   * {@inheritDoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return (object) [
      'identifier' => $this->get('identifier')->getString(),
      'version' => $this->get('version')->getString(),
      'filePath' => $this->get('filePath')->getString(),
      'perspective' => $this->get('perspective')->getString(),
      'mimeType' => $this->get('mimeType')->getString(),
      'checksum' => $this->get('checksum')->getString(),
    ];
  }

}
