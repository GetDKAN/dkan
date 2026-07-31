<?php

namespace Drupal\dkan_metastore\Drush\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dkan_metastore\MetastoreService;
use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\dkan_metastore\SchemaPropertiesHelper;
use Drupal\dkan_metastore\Storage\DataFactory;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * Drush command to convert referenced distributions to embedded distributions.
 *
 * @codeCoverageIgnore
 */
final class EmbedDistributionsCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(
    private readonly DataFactory $factory,
    private readonly MetastoreService $metastoreService,
    private readonly SchemaPropertiesHelper $schemaPropertiesHelper,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly QueueFactory $queueFactory,
    private readonly Dereferencer $dereferencer,
  ) {
    parent::__construct();
  }

  /**
   * Convert referenced distributions to embedded distributions.
   */
  #[CLI\Command(name: 'dkan:metastore:embed-distributions', aliases: ['dkan:metastore:unref'], description: 'Convert referenced distributions to embedded (non-referenced) distributions.')]
  #[CLI\Argument(name: 'target_property', description: 'Dataset property to embed (e.g. "distribution"). Prompted interactively if omitted.')]
  #[CLI\Option(name: 'delete-orphans', description: 'Immediately delete orphaned referenced entities instead of queuing them.')]
  public function embedDistributions(
    ?string $target_property = NULL,
    array $options = ['delete-orphans' => FALSE],
  ): void {
    $properties = $this->schemaPropertiesHelper->retrieveSchemaProperties();

    if ($target_property === NULL) {
      $target_property = $this->io()->choice(
        'Select the dataset property to embed',
        array_combine(array_keys($properties), array_values($properties)),
      );
    }

    if (!array_key_exists($target_property, $properties)) {
      $this->logger()->error("Unknown property: {$target_property}");
      return;
    }

    // Disable referencing for this property if currently enabled.
    $config = $this->configFactory->getEditable('dkan_metastore.settings');
    $property_list = $config->get('property_list') ?? [];
    if (in_array($target_property, $property_list, TRUE)) {
      $config->set('property_list', array_values(array_diff($property_list, [$target_property])))->save();
      $this->logger()->notice("Disabled referencing for \"{$target_property}\" in dkan_metastore.settings.");
    }

    $delete_orphans = (bool) ($options['delete-orphans'] ?? FALSE);
    $storage = $this->factory->getInstance('dataset');
    $uuids = $this->metastoreService->getIdentifiers('dataset', unpublished: TRUE);

    foreach ($uuids as $uuid) {
      $this->processDataset($uuid, $target_property, $delete_orphans, $storage);
    }
  }

  /**
   * Re-save one dataset with the target property embedded, then handle orphans.
   */
  private function processDataset(string $uuid, string $target_property, bool $delete_orphans, $storage): void {
    try {
      $entity = $storage->getEntityLatestRevision($uuid);
      if (!$entity) {
        return;
      }

      $data = json_decode($storage->retrieve($uuid));
      $orphan_uuids = $this->collectReferenceUuids($data, $target_property);
      $title = $data->title ?? $data->name ?? $uuid;
      $count = count($orphan_uuids);

      if ($count === 0) {
        return;
      }

      $this->output()->writeln(sprintf('[%s] Un-referencing %d %s value(s).', $title, $count, $target_property));

      $original_uid = $entity->getOwnerId();

      // Data from retrieve() is already dereferenced by the node-load hook.
      // Drop the %Ref marker so the property is stored as embedded.
      unset($data->{'%Ref:' . $target_property});
      // store() expects a JSON string, not a decoded object.
      $storage->store(json_encode($data), $uuid);

      // Restore original author and set log message on the new revision.
      $updated = $storage->getEntityLatestRevision($uuid);
      if ($updated instanceof RevisionLogInterface) {
        $updated->setRevisionUserId($original_uid);
        $updated->setRevisionLogMessage('Automatically re-saved to remove distribution references.');
        $updated->save();
      }

      foreach ($orphan_uuids as $orphan_uuid) {
        if ($delete_orphans) {
          try {
            $this->metastoreService->delete($target_property, $orphan_uuid);
          }
          catch (\Exception) {
            // Already gone; safe to continue.
          }
        }
        else {
          $this->queueFactory->get('orphan_reference_processor')
            ->createItem(['uuid' => $orphan_uuid, 'schema_id' => $target_property]);
        }
      }
    }
    catch (\Exception $e) {
      $this->logger()->error("Failed processing dataset {$uuid}: " . $e->getMessage());
    }
  }

  /**
   * Collect UUIDs from the %Ref:<property> key set by the node-load dereferencer.
   *
   * @return string[]
   */
  private function collectReferenceUuids(object $data, string $property): array {
    if (!isset($data->{$property}) || !is_array($data->{$property})) {
      return [];
    }
    $uuids = [];
    foreach ($data->{$property} as $uuid) {
      if (is_string($uuid)) {
        $uuids[] = $uuid;
      }
    }
    return $uuids;
  }

}
