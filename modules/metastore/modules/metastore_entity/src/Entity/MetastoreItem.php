<?php

namespace Drupal\metastore_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Metastore item entity.
 *
 * @ingroup metastore_entity
 *
 * @ContentEntityType(
 *   id = "metastore_item",
 *   label = @Translation("Metastore item"),
 *   bundle_label = @Translation("Metastore schema"),
 *   handlers = {
 *     "storage" = "Drupal\metastore_entity\MetastoreItemStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\metastore_entity\MetastoreItemListBuilder",
 *     "views_data" = "Drupal\metastore_entity\Entity\MetastoreItemViewsData",
 *     "translation" = "Drupal\metastore_entity\MetastoreItemTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\metastore_entity\Form\MetastoreItemForm",
 *       "add" = "Drupal\metastore_entity\Form\MetastoreItemForm",
 *       "edit" = "Drupal\metastore_entity\Form\MetastoreItemForm",
 *       "delete" = "Drupal\metastore_entity\Form\MetastoreItemDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\metastore_entity\MetastoreItemHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\metastore_entity\MetastoreItemAccessControlHandler",
 *   },
 *   base_table = "metastore_item",
 *   data_table = "metastore_item_field_data",
 *   revision_table = "metastore_item_revision",
 *   revision_data_table = "metastore_item_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer metastore item entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "schema",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "json_data" = "json_data",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/metastore_item/{metastore_item}",
 *     "add-page" = "/admin/structure/metastore_item/add",
 *     "add-form" = "/admin/structure/metastore_item/add/{metastore_schema}",
 *     "edit-form" = "/admin/structure/metastore_item/{metastore_item}/edit",
 *     "delete-form" = "/admin/structure/metastore_item/{metastore_item}/delete",
 *     "version-history" = "/admin/structure/metastore_item/{metastore_item}/revisions",
 *     "revision" = "/admin/structure/metastore_item/{metastore_item}/revisions/{metastore_item_revision}/view",
 *     "revision_revert" = "/admin/structure/metastore_item/{metastore_item}/revisions/{metastore_item_revision}/revert",
 *     "revision_delete" = "/admin/structure/metastore_item/{metastore_item}/revisions/{metastore_item_revision}/delete",
 *     "translation_revert" = "/admin/structure/metastore_item/{metastore_item}/revisions/{metastore_item_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/metastore_item",
 *   },
 *   bundle_entity_type = "metastore_schema",
 *   field_ui_base_route = "entity.metastore_schema.edit_form"
 * )
 */
class MetastoreItem extends EditorialContentEntityBase implements MetastoreItemEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
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
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the metastore_item owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }


  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->get('schema')->target_id;
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
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->getTitle();
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->setTitle($name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
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
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Metastore item entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
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
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Metastore item.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['json_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Metadata'))
      // ->setDescription(t('Most likely JSON.'))
      ->setSettings([
        'default_value' => '{}',
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'basic_string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
        'settings' => ['rows' => 10],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Metastore item is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
