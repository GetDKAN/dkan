<?php

declare(strict_types = 1);

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\harvest\HarvestRunInterface;

/**
 * Defines the harvest run entity class.
 *
 * The harvest run entity connects three categories of information:
 * - The timestamp/id of the harvest run.
 * - The plan id for the harvest that was run.
 * - The resulting status information for the run.
 *
 * Status information is normalized out of the harvest result array.
 * UUID-oriented results are stored in unlimited cardinality fields, such as
 * 'extracted_uuid', which tells us the UUIDs of resources extracted from the
 * catalog JSON.
 *
 * Information not normalized out of the result array is JSON-encoded and stored
 * in the 'data' column.
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
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\harvest\ContentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\harvest\Routing\HarvestDashboardHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "harvest_runs",
 *   admin_permission = "administer harvest_run",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "canonical" = "/harvest-run/{harvest_run}",
 *   },
 * )
 */
final class HarvestRun extends HarvestEntityBase implements HarvestRunInterface {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);

    $base_fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Harvest Run entity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 0,
      ])
      ->setReadOnly(TRUE);

    $base_fields['timestamp'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('timestamp'))
      ->setDescription(t('The timestamp of when this harvest was run.'))
      ->setRequired(TRUE);

    $base_fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The unique identifier for this harvest_run'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    // Harvest plan id. This is the name of the harvest plan as seen in the UI.
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
    $base_fields['data'] = static::getBaseFieldJsonData(
      new TranslatableMarkup('Data')
    );

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
    $base_fields['extracted_uuid'] = static::getBaseFieldUnlimitedCardinalityUuidField(
      new TranslatableMarkup('Extracted nodes')
    );
    // UUIDs of datastore entities that were loaded.
    $base_fields['load_new_uuid'] = static::getBaseFieldUnlimitedCardinalityUuidField(
      new TranslatableMarkup('New loaded nodes')
    );
    // UUIDs of datastore entities that were loaded and updated.
    $base_fields['load_updated_uuid'] = static::getBaseFieldUnlimitedCardinalityUuidField(
      new TranslatableMarkup('Updated loaded nodes')
    );
    // UUIDs of datastore entities that were loaded and didn't need to be
    // changed.
    $base_fields['load_unchanged_uuid'] = static::getBaseFieldUnlimitedCardinalityUuidField(
      new TranslatableMarkup('Unchanged loaded nodes')
    );
    // UUIDs of entity that was orphaned.
    $base_fields['orphan_uuid'] = static::getBaseFieldUnlimitedCardinalityUuidField(
      new TranslatableMarkup('Orphaned data nodes')
    );
    return $base_fields;
  }

  /**
   * {@inheritDoc}
   */
  public function toResult(): array {
    $result = json_decode($this->get('data')->getString(), TRUE);

    $result['status']['extract'] = $this->get('extract_status')->getString();

    foreach ($this->get('extracted_uuid') as $field) {
      $result['status']['extracted_items_ids'][] = $field->getString();
    }

    $result['status']['orphan_ids'] = [];
    foreach ($this->get('orphan_uuid') as $field) {
      $result['status']['orphan_ids'][] = $field->getString();
    }

    foreach ($this->get('load_new_uuid') as $field) {
      $result['status']['load'][$field->getString()] = 'NEW';
    }
    foreach ($this->get('load_updated_uuid') as $field) {
      $result['status']['load'][$field->getString()] = 'UPDATED';
    }
    foreach ($this->get('load_unchanged_uuid') as $field) {
      $result['status']['load'][$field->getString()] = 'UNCHANGED';
    }
    return $result;
  }

}
