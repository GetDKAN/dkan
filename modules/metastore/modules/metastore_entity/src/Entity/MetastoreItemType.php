<?php

namespace Drupal\metastore_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Metastore item type entity.
 *
 * @ConfigEntityType(
 *   id = "metastore_item_type",
 *   label = @Translation("Metastore item type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\metastore_entity\MetastoreItemTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\metastore_entity\Form\MetastoreItemTypeForm",
 *       "edit" = "Drupal\metastore_entity\Form\MetastoreItemTypeForm",
 *       "delete" = "Drupal\metastore_entity\Form\MetastoreItemTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\metastore_entity\MetastoreItemTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "metastore_item_type",
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
 *     "canonical" = "/admin/structure/metastore_item_type/{metastore_item_type}",
 *     "add-form" = "/admin/structure/metastore_item_type/add",
 *     "edit-form" = "/admin/structure/metastore_item_type/{metastore_item_type}/edit",
 *     "delete-form" = "/admin/structure/metastore_item_type/{metastore_item_type}/delete",
 *     "collection" = "/admin/structure/metastore_item_type"
 *   }
 * )
 */
class MetastoreItemType extends ConfigEntityBundleBase implements MetastoreItemTypeInterface {

  /**
   * The Metastore item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Metastore item type label.
   *
   * @var string
   */
  protected $label;

}
