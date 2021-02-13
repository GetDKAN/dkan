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
 *     "behaviors",
 *     "json_schema",
 *     "ui_schema",
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

  const BEHAVIOR_DATASET = 1;
  const BEHAVIOR_RESOURCE = 2;

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

  /**
   * The Metastore schema behaviors.
   *
   * @var array
   */
  protected $behaviors;

  /**
   * The main Metastore JSON schema.
   *
   * @var string
   */
  protected $json_schema;

  /**
   * The UI schema for this Metastore schema.
   *
   * @var string
   */
  protected $ui_schema;

  /**
   * Get the JSON schema.
   *
   * @return string
   *   A JSON Schema document.
   */
  public function getSchema() {
    if (is_array($this->json_schema) && isset($this->json_schema['value'])) {
      return $this->json_schema['value'];
    }
    return $this->json_schema;
  }

  /**
   * Get the UI schema.
   *
   * @return string
   *   A JSON document.
   */
  public function getUiSchema() {
    if (is_array($this->ui_schema) && isset($this->ui_schema['value'])) {
      return $this->ui_schema['value'];
    }
    return $this->ui_schema;
  }

  /**
   * Get list of behaviors for this metastore schema.
   *
   * @return array
   *   Array of behaviors
   */
  public function getBehaviors(): array {
    return $this->behaviors ? $this->behaviors : [];
  }

  /**
   * Check if schema has a particular behavior.
   *
   * @param int $behavior
   *   One of the behaviors defined in MetastoreSchema() constants.
   *
   * @return bool
   *   True of false has behavior.
   */
  public function hasBehavior(int $behavior): bool {
    return in_array($behavior, $this->behaviors);
  }

}
