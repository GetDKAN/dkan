<?php

declare(strict_types=1);

namespace Drupal\dkan_common\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Rewrites imported config on the fly to account for module renames.
 *
 * This runs early in the import pipeline, before dependency validation, so
 * config that used to depend on "datastore" will instead depend on
 * "dkan_datastore" during the import.
 */
final class ConfigImportRenameDependenciesSubscriber implements EventSubscriberInterface {

  private const MAP = [
    'common' => 'dkan_common',
    'datastore' => 'dkan_datastore',
    'datastore_mysql_import' => 'dkan_datastore_mysql_import',
    'metastore' => 'dkan_metastore',
    'metastore_search' => 'dkan_metastore_search',
    'metastore_facets' => 'dkan_metastore_facets',
    'metastore_admin' => 'dkan_metastore_admin',
    'harvest' => 'dkan_harvest',
  ];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::STORAGE_TRANSFORM_IMPORT => 'onStorageTransformImport',
    ];
  }

  /**
   * Alters the source storage contents that will be imported.
   */
  public function onStorageTransformImport(StorageTransformEvent $event): void {
    $storage = $event->getStorage();

    // First pass: rename config objects themselves.
    $renames = [];
    foreach ($storage->listAll() as $name) {
      foreach (self::MAP as $old => $new) {
        if (str_starts_with($name, $old . '.')) {
          $new_name = $new . substr($name, strlen($old));
          $renames[$name] = $new_name;
          break;
        }
      }
    }

    // Execute renames: read from old name, write to new name, delete old.
    foreach ($renames as $old_name => $new_name) {
      $data = $storage->read($old_name);
      if (is_array($data)) {
        $storage->write($new_name, $data);
        $storage->delete($old_name);
      }
    }

    // Second pass: update dependencies within all config.
    foreach ($storage->listAll() as $name) {
      $data = $storage->read($name);
      if (!is_array($data)) {
        continue;
      }

      $changed = FALSE;

      // Update dependency declaration: dependencies: { module: [...] }.
      if (!empty($data['dependencies']['module']) && is_array($data['dependencies']['module'])) {
        $before = $data['dependencies']['module'];
        $data['dependencies']['module'] = array_values(array_map(
          static fn(string $m): string => self::MAP[$m] ?? $m,
          $data['dependencies']['module']
        ));
        $changed = $changed || ($before !== $data['dependencies']['module']);
      }

      if ($changed) {
        // Write the modified config back into the import source storage.
        $storage->write($name, $data);
      }
    }
  }

}
