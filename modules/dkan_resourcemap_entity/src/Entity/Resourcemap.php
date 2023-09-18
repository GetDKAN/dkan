<?php

namespace Drupal\dkan_resourcemap_entity\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\dkan_resourcemap_entity\ResourcemapInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the resourcemap entity class.
 *
 * @ContentEntityType(
 *   id = "resourcemap",
 *   label = @Translation("ResourceMap"),
 *   label_collection = @Translation("ResourceMaps"),
 *   label_singular = @Translation("resourcemap"),
 *   label_plural = @Translation("resourcemaps"),
 *   label_count = @PluralTranslation(
 *     singular = "@count resourcemaps",
 *     plural = "@count resourcemaps",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\dkan_resourcemap_entity\ResourcemapListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\dkan_resourcemap_entity\Form\ResourcemapForm",
 *       "edit" = "Drupal\dkan_resourcemap_entity\Form\ResourcemapForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "dkan_metastore_resource_mapper",
 *   admin_permission = "administer resourcemap",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "collection" = "/admin/content/resourcemap",
 *     "add-form" = "/resourcemap/add",
 *     "canonical" = "/resourcemap/{resourcemap}",
 *     "edit-form" = "/resourcemap/{resourcemap}/edit",
 *     "delete-form" = "/resourcemap/{resourcemap}/delete",
 *   },
 * )
 */
class Resourcemap extends ContentEntityBase implements ResourcemapInterface {

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

}
