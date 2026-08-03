<?php

namespace Drupal\dkan_metastore\Drush\Commands;

use Drupal\dkan_metastore\Storage\MetastoreEntityStorageInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_metastore\MetastoreService;
use Drupal\dkan_metastore\SchemaPropertiesHelper;
use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\dkan_metastore\Storage\DataFactory;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * Drush command to unreference dataset properties.
 */
final class UnreferenceDatasetCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Metastore storage object.
   *
   * @var \Drupal\dkan_metastore\Storage\MetastoreEntityStorageInterface
   */
  private MetastoreEntityStorageInterface $storage;

  /**
   * Constructor.
   */
  public function __construct(
    private readonly DataFactory $factory,
    private readonly MetastoreService $metastoreService,
    private readonly SchemaPropertiesHelper $schemaPropertiesHelper,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly Dereferencer $dereferencer,
  ) {
    parent::__construct();
  }

  /**
   * Convert referenced dataset property values to embedded values.
   */
  #[CLI\Command(name: 'dkan:metastore:unreference-datasets', description: 'Convert referenced dataset property values to embedded (non-referenced) values.', aliases: ['dkan:unref'])]
  #[CLI\Argument(name: 'target_property', description: 'Dataset property to unreference (e.g. "distribution"). Prompted interactively if omitted.')]
  #[CLI\Option(name: 'delete-orphans', description: 'Immediately delete orphaned referenced entities instead of queuing them.')]
  public function unrefDatasets(
    ?string $target_property = 'distribution',
    array $options = ['delete-orphans' => FALSE],
  ): void {
    $properties = $this->schemaPropertiesHelper->retrieveSchemaProperties();
    $this->storage = $this->factory->getInstance('dataset');

    if (!array_key_exists($target_property, $properties)) {
      $this->logger()->error("Unknown property: {$target_property}");
      return;
    }

    // Warn the user that this operation is irreversible.
    $this->io()->warning("You are about to overwrite references for the \"{$target_property}\" property for all datasets. This operation is irreversible.");
    if (!$this->io()->confirm('Do you want to continue?', FALSE)) {
      $this->logger()->notice('Operation cancelled.');
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
    $uuids = $this->metastoreService->getIdentifiers('dataset', unpublished: TRUE);

    foreach ($uuids as $uuid) {
      $this->processDataset($uuid, $target_property, $delete_orphans);
    }
  }

  /**
   * Re-save one dataset with the target property embedded, then handle orphans.
   */
  private function processDataset(string $uuid, string $target_property, bool $delete_orphans): void {
    $entity = $this->storage->getEntityLatestRevision($uuid);
    if (!$entity) {
      return;
    }

    $data = json_decode($this->storage->retrieve($uuid));
    $orphan_uuids = $this->collectReferenceUuids($data, $target_property);
    $title = $data->title ?? $data->name ?? $uuid;
    $count = count($orphan_uuids);

    $this->output()->writeln(sprintf('[%s] Un-referencing %d %s value(s).', $title, $count, $target_property));
    if ($count === 0) {
      return;
    }

    // Dereference the target property and re-save the dataset.
    $this->dereferencer->dereferenceProperty($target_property, $data);
    $dataset = $this->metastoreService->getValidMetadataFactory()->get(json_encode($data), 'dataset');
    $this->metastoreService->removeReferences($dataset);
    $this->storage->store((string) $dataset, $uuid);

    foreach ($orphan_uuids as $orphan_uuid) {
      if ($delete_orphans) {
        $this->metastoreService->delete($target_property, $orphan_uuid);
        $this->output()->writeln(sprintf('[%s] Deleting orphaned %s: %s', $title, $target_property, $orphan_uuid));
      }
      else {
        $ref_storage = $this->factory->getInstance($target_property);
        // Use OrphanReferenceProcessor directly on the referenced entity.
        $this->output()->writeln(sprintf('[%s] Orphaning %s: %s', $title, $target_property, $orphan_uuid));
        $ref_storage->orphan($orphan_uuid);
      }
    }
  }

  /**
   * Collect UUIDs from reference key.
   *
   * @param object $data
   *   The dataset data object.
   * @param string $property
   *   The property to check for references.
   *
   * @return string[]
   *   An array of UUIDs.
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
