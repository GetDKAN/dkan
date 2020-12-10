<?php

namespace Drupal\metastore_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\metastore_entity\MetadataSchemaInterface;

/**
 * Defines the Node type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "metadata_schema",
 *   label = @Translation("Metadata schema"),
 *   label_collection = @Translation("Metadata schemas"),
 *   label_singular = @Translation("metadata schema"),
 *   label_plural = @Translation("metadata schemas"),
 *   label_count = @PluralTranslation(
 *     singular = "@count metadata schema",
 *     plural = "@count Metadata schemas",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\metastore_entity\MetadataAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\metastore_entity\Form\MetadataSchemaForm",
 *       "edit" = "Drupal\metastore_entity\Form\MetadataSchemaForm",
 *       "delete" = "Drupal\metastore_entity\Form\MetadataSchemaDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\metastore_entity\Entity\Controller\MetadataSchemaListBuilder",
 *   },
 *   admin_permission = "administer metadata schemas",
 *   config_prefix = "schema",
 *   bundle_of = "metadata",
 *   entity_keys = {
 *     "id" = "schema",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/schemas/manage/{metadata_schema}",
 *     "delete-form" = "/admin/structure/schemas/manage/{metadata_schema}/delete",
 *     "collection" = "/admin/structure/schemas",
 *   },
 *   config_export = {
 *     "name",
 *     "schema",
 *     "description",
 *     "help",
 *     "new_revision",
 *     "preview_mode",
 *     "display_submitted",
 *   }
 * )
 */
class MetadataSchema extends ConfigEntityBundleBase implements MetadataSchemaInterface {

  /**
   * The machine name of this metadata schema.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  protected $schema;

  /**
   * The human-readable name of the node type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  protected $name;

  /**
   * A brief description of this node type.
   *
   * @var string
   */
  protected $description;

  /**
   * Help information shown to the user when creating a Node of this type.
   *
   * @var string
   */
  protected $help;

  /**
   * Default value of the 'Create new revision' checkbox of this node type.
   *
   * @var bool
   */
  protected $new_revision = TRUE;

  /**
   * The preview mode.
   *
   * @var int
   */
  protected $preview_mode = DRUPAL_OPTIONAL;

  /**
   * Display setting for author and date Submitted by post information.
   *
   * @var bool
   */
  protected $display_submitted = TRUE;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('metadata.schema.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    $this->new_revision = $new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function displaySubmitted() {
    return $this->display_submitted;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplaySubmitted($display_submitted) {
    $this->display_submitted = $display_submitted;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewMode() {
    return $this->preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreviewMode($preview_mode) {
    $this->preview_mode = $preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelp() {
    return $this->help;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Clear the node type cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

}
