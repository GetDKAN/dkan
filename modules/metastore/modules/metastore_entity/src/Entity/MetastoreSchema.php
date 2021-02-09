<?php

namespace Drupal\metastore_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Metastore schema entity.
 *
 * @ConfigEntityType(
 *   id = "metastore_schema",
 *   label = @Translation("Metastore schema"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\metastore_entity\MetastoreSchemaListBuilder",
 *     "form" = {
 *       "add" = "Drupal\metastore_entity\Form\MetastoreSchemaForm",
 *       "edit" = "Drupal\metastore_entity\Form\MetastoreSchemaForm",
 *       "delete" = "Drupal\metastore_entity\Form\MetastoreSchemaDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\metastore_entity\MetastoreSchemaHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "metastore_schema",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "metastore_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "schema",
 *     "description",
 *     "help",
 *     "display_submitted",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/metastore_schema/{metastore_schema}",
 *     "add-form" = "/admin/structure/metastore_schema/add",
 *     "edit-form" = "/admin/structure/metastore_schema/{metastore_schema}/edit",
 *     "delete-form" = "/admin/structure/metastore_schema/{metastore_schema}/delete",
 *     "collection" = "/admin/structure/metastore_schema"
 *   }
 * )
 */
class MetastoreSchema extends ConfigEntityBundleBase implements MetastoreSchemaInterface {

  /**
   * The Metastore schema ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Metastore schema label.
   *
   * @var string
   */
  protected $label;

}
