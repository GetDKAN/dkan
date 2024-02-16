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
 * ResourceMapping entities store particular "perspectives" on a data file
 * resource. The source perspective will be a URL to the original location of
 * the resource. The local_file and local_url perspectives record local copies
 * of remote files for use by the DKAN datastore.
 *
 * A ResourceMapping entity has three levels of definition: The identifier,
 * which is hash of the source URL, the version, which is a timestamp of when
 * the source URL was accessed/downloaded, and the perspective itself.
 *
 * These mappings allow DKAN to work with local copies of remote files,
 * particularly for import into the datastore, and keep track of which remote
 * files they represent.
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
 *
 * @see \Drupal\common\DataResource
 */
class ResourceMapping extends ContentEntityBase implements ResourceMappingInterface {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Parent will add a base field of 'id', which is a unique id per DB row.
    $base_fields = parent::baseFieldDefinitions($entity_type);
    // Identifier for this resource. This is a hash of the source URL. This is
    // not unique per row.
    $base_fields['identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resource identifier'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    // Mapping version. This is a timestamp of the most recent version of this
    // resource.
    $base_fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Version'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    // File path or URL or URI for this resource, depending on perspective. For
    // source perspective this will be a remote URL. For local_file this will
    // be a local URI. For local_url, this will be a 'hostified' URL.
    // @see \Drupal\common\UrlHostTokenResolver::hostify()
    $base_fields['filePath'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('File Path'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    // The perspective of this resource mapping. Can be one of:
    // - source
    // - local_file
    // - local_url
    $base_fields['perspective'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Perspective'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    // MIME type of the resource.
    $base_fields['mimeType'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('MIME Type'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    // Checksum of the file resource. Can be used to compare local and remote
    // files.
    $base_fields['checksum'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Checksum'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE);
    return $base_fields;
  }

}
