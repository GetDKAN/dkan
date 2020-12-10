<?php

namespace Drupal\metastore_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\metastore_entity\MetadataInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Metadata entity.
 *
 * @ingroup metadata_entity
 *
 * [...]
 *
 *  The following annotation is the actual definition of the entity type which
 *  is read and cached. Don't forget to clear cache after changes.
 *
 * @ContentEntityType(
 *   id = "metadata",
 *   label = @Translation("Metadata entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\metastore_entity\Entity\Controller\MetadataListBuilder",
 *     "form" = {
 *       "default" = "Drupal\metastore_entity\Form\MetadataForm",
 *       "add" = "Drupal\metastore_entity\Form\MetadataForm",
 *       "edit" = "Drupal\metastore_entity\Form\MetadataForm",
 *       "delete" = "Drupal\metastore_entity\Form\MetadataDeleteForm",
 *     },
 *     "access" = "Drupal\metastore_entity\MetadataAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "metadata",
 *   admin_permission = "administer metadata entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "schema",
 *     "label" = "schema",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_owner" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   bundle_label = @Translation("Metadata schema"),
 *   common_reference_target = TRUE,
 *   permission_granularity = "bundle",
 *   field_ui_base_route = "metastore_entity.metadata_settings",
 *   links = {
 *     "canonical" = "/metadata/{metastore_entity_metadata}",
 *     "delete-form" = "/metadata/{metastore_entity_metadata}/delete",
 *     "delete-multiple-form" = "/admin/content/node/delete",
 *     "edit-form" = "/metadata/{metastore_entity_metadata}/edit",
 *     "version-history" = "/metadata/{metastore_entity_metadata}/revisions",
 *     "revision" = "/metadata/{metastore_entity_metadata}/revisions/{metadata_revision}/view",
 *   }
 * )
 */
class Metadata extends ContentEntityBase implements MetadataInterface {
  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    // Name field for the Metadata.
    // We set display options for the view as well as the form.
    // Users with correct privileges can change the view and edit configuration.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The name of the Metadata entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('Most likely JSON.'))
      ->setSettings([
        'default_value' => '',
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'basic_string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -5,
        'settings' => ['rows' => 10],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Owner field of the Metadata.
    // Entity reference field, holds the reference to the user object.
    // The view shows the user name field of the user.
    // The form presents a auto complete field for the user name.
    $fields['uid']
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the metadata owner.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of the Metadata entity.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchemaName() {
    return $this->bundle();
  }

}
