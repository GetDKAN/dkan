<?php

declare(strict_types = 1);

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\harvest\HarvestRunInterface;

/**
 * Defines the harvest run entity class.
 *
 * The harvest run entity connects three things:
 * - The timestamp/id of the harvest run.
 * - The plan id for the harvest that was run.
 * - The resulting status information for the run, as a blob of JSON.
 *
 * @ContentEntityType(
 *   id = "harvest_run",
 *   label = @Translation("Harvest Run"),
 *   label_collection = @Translation("Harvest Runs"),
 *   label_singular = @Translation("harvest run"),
 *   label_plural = @Translation("harvest runs"),
 *   label_count = @PluralTranslation(
 *     singular = "@count harvest runs",
 *     plural = "@count harvest runs",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\harvest\HarvestRunListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "harvest_runs",
 *   admin_permission = "administer harvest_run",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "collection" = "/admin/dkan/harvest/runs",
 *     "canonical" = "/harvest-run/{harvest_run}",
 *   },
 * )
 */
final class HarvestRun extends ContentEntityBase implements HarvestRunInterface {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);

    // The id is the unique ID for the harvest run, and also the timestamp at
    // which the run occurred, generated by time().
    $base_fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Harvest Run'))
      ->setReadOnly(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    // Harvest plan id.
    $base_fields['harvest_plan_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Harvest Plan ID'))
      ->setDescription(t('The harvest plan ID.'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('view', [
        'type' => 'basic_string',
        'weight' => 0,
        'label' => 'inline',
      ]);

    // The 'data' field contains JSON which describes the result of the harvest
    // run not explicitly stored in other fields here. This is an arbitrary
    // array created by Drupal\harvest\HarvestService::runHarvest() and
    // Harvest\Harvester::harvest().
    // @see \Drupal\harvest\HarvestService::runHarvest()
    // @see \Harvest\Harvester::harvest()
    $base_fields['data'] = BaseFieldDefinition::create('string_long')
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

    // Status string from the extract phase of the harvest.
    $base_fields['extract_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Extract status'))
      ->setDescription(t('The extraction status.'))
      ->setRequired(FALSE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('view', [
        'type' => 'basic_string',
        'weight' => -4,
        'label' => 'inline',
      ]);

    // UUIDs of entities that were extracted. Note: These are UUIDs only by
    // convention. Any string could be specified in the harvest.
    $base_fields['extracted_uuid'] = static::createUnlimitedCardinalityUuidField()
      ->setLabel(t('Extracted nodes'));

    // UUIDs of datastore entities that were loaded.
    $base_fields['load_new_uuid'] = static::createUnlimitedCardinalityUuidField()
      ->setLabel(t('New loaded nodes'));

    // UUIDs of datastore entities that were loaded and updated.
    $base_fields['load_updated_uuid'] = static::createUnlimitedCardinalityUuidField()
      ->setLabel(t('Updated loaded nodes'));

    // UUIDs of datastore entities that were loaded and didn't need to be
    // changed.
    $base_fields['load_unchanged_uuid'] = static::createUnlimitedCardinalityUuidField()
      ->setLabel(t('Unchanged loaded nodes'));

    // UUIDs of entity that was orphaned.
    $base_fields['orphan_uuid'] = static::createUnlimitedCardinalityUuidField()
      ->setLabel(t('Orphaned data nodes'));

    return $base_fields;
  }

  /**
   * Generic field definition for a field with unlimited cardinality.
   *
   * Note: DKAN currently uses the fields defined here as UUIDs only by
   * convention. Any string could be specified in the harvest.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function createUnlimitedCardinalityUuidField(): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRevisionable(FALSE)
      ->setReadOnly(FALSE)
      ->setStorageRequired(FALSE)
      ->setRequired(FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function toResult(): array {
    $result = json_decode($this->get('data')->getString(), TRUE);

    $result['status']['extract'] = $this->get('extract_status')->getString();

    foreach ($this->get('extracted_uuid') as $item) {
      $result['status']['extracted_items_ids'][] = $item->getString();
    }

    foreach ($this->get('orphan_uuid') as $item) {
      $result['status']['orphan_ids'][] = $item->getString();
    }

    foreach ($this->get('load_new_uuid') as $item) {
      $result['status']['load'][$item->getString()] = 'NEW';
    }
    foreach ($this->get('load_updated_uuid') as $item) {
      $result['status']['load'][$item->getString()] = 'UPDATED';
    }
    foreach ($this->get('load_unchanged_uuid') as $item) {
      $result['status']['load'][$item->getString()] = 'UNCHANGED';
    }
    return $result;
  }

}
