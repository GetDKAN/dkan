<?php

namespace Drupal\dkan_datastore\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\UnstructuredListData;
use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_datastore\DatastoreLookupInterface;
use Drupal\dkan_datastore\DatastoreService;
use Drupal\dkan_datastore\PostImportResultFactory;
use Drupal\dkan_datastore\Service\Info\ImportInfoList;
use Drupal\dkan_datastore\Service\ResourceLocalizer;
use Drupal\dkan_metastore\MetastoreService;
use Drupal\dkan_metastore\ResourceMapper;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Procrastinator\Result;

/**
 * Drush commands for controlling the datastore.
 *
 * @codeCoverageIgnore
 */
final class DatastoreCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(
    protected MetastoreService $metastoreService,
    protected DatastoreService $datastoreService,
    protected ResourceLocalizer $resourceLocalizer,
    protected ResourceMapper $resourceMapper,
    private ImportInfoList $importInfoList,
    protected PostImportResultFactory $postImportResultFactory,
    protected DatastoreLookupInterface $datastoreLookup,
  ) {
    parent::__construct();
  }

  /**
   * Import a datastore resource.
   *
   * Passing simply a resource identifier will immediately run an import for
   * that resource. However, if both the FileFetcher and Import jobs are
   * already recorded as "done" in the jobstore, nothing will happen. To
   * re-import an existing resource, first use the dkan:datastore:drop command
   * then use import. If you want to re-import the file to the datastore
   * without repeating the FileFetcher, make sure to run the drop command with
   * --keep-local. The local file and the FileFetcher status will be preserved,
   * so the import will see them as "done" and go straight to the actual DB
   * import job.
   *
   * @todo pass configurable options for csv delimiter, quote, and escape
   *   characters.
   */
  #[CLI\Command(name: 'dkan:datastore:import', description: 'Import a datastore resource.')]
  #[CLI\Argument(name: 'identifier', description: 'Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".')]
  #[CLI\Option(name: 'deferred', description: 'Add the import to the datastore_import queue, rather than importing now.')]
  public function import(string $identifier, array $options = ['deferred' => FALSE]) {
    $deferred = (bool) $options['deferred'];

    try {
      if ($deferred) {
        $results = $this->datastoreService->importDeferred($identifier);
        foreach ($results as $result) {
          $this->logger->notice($result);
        }
      }
      else {
        $result = $this->datastoreService->import($identifier, $deferred);
        $this->logger->notice('Ran import for ' . $identifier);
        foreach ($result as $jobname => $result_object) {
          /** @var \Procrastinator\Result $result_object */
          $this->logger->notice('[' . $jobname . '] ' . $result_object->getStatus());
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error('No resource found to import with identifier ' . $identifier);
      $this->logger->debug($e->getMessage());
    }
  }

  /**
   * List information about all datastores.
   */
  #[CLI\Command(name: 'dkan:datastore:list', description: 'List information about all datastores.')]
  #[CLI\FieldLabels(labels: [
    'uuid' => 'Resource UUID',
    'fileName' => 'File Name',
    'fileFetcherStatus' => 'FileFetcher',
    'fileFetcherBytes' => 'Processed',
    'importerStatus' => 'Importer',
    'importerBytes' => 'Processed',
  ])]
  #[CLI\DefaultTableFields(fields: [
    'uuid',
    'fileName',
    'fileFetcherStatus',
    'fileFetcherBytes',
    'importerStatus',
    'importerBytes',
  ])]
  #[CLI\Option(name: 'status', description: 'Show imports of the given status.')]
  #[CLI\Option(name: 'uuid-only', description: 'Only the list of uuids.')]
  public function list(
    $options = [
      'format' => 'table',
      'status' => NULL,
      'uuid-only' => FALSE,
    ],
  ) {
    $status = $options['status'];
    $uuid_only = $options['uuid-only'];

    $list = $this->importInfoList->buildList();
    $rows = [];
    foreach ($list as $uuid => $item) {
      $rows[] = $this->createRow($uuid, $item);
    }

    if (!empty($status)) {
      $rows = array_filter($rows, function ($row) use ($status) {
        if ($row['fileFetcherStatus'] == $status || $row['importerStatus'] == $status) {
          return TRUE;
        }
        return FALSE;
      });
    }

    if ($uuid_only) {
      foreach ($rows as $index => $row) {
        $rows[$index] = $row['uuid'];
      }
      return new UnstructuredListData($rows);
    }

    return new RowsOfFields($rows);
  }

  /**
   * Create row helper function.
   *
   * @param string $uuid
   *   The resource uuid.
   * @param \Drupal\dkan_datastore\Service\Info\ImportInfoItem $item
   *   The item to create the row from.
   *
   * @return array
   *   The created row.
   */
  private function createRow($uuid, $item) {
    // Using deprecation helper.
    return [
      'uuid' => $uuid,
      'fileName' => $item->fileName,
      'fileFetcherStatus' => $item->fileFetcherStatus,
      'fileFetcherBytes' => DeprecationHelper::backwardsCompatibleCall(
        \Drupal::VERSION,
        '10.2.0',
        fn() => ByteSizeMarkup::create($item->fileFetcherBytes),
        fn() => ByteSizeMarkup::create($item->fileFetcherBytes)
      ) . " ($item->fileFetcherPercentDone%)",
      'importerStatus' => $item->importerStatus,
      'importerBytes' => DeprecationHelper::backwardsCompatibleCall(
        \Drupal::VERSION, '10.2.0',
        fn() => ByteSizeMarkup::create($item->importerBytes),
        fn() => ByteSizeMarkup::create($item->importerBytes)
      ) . " ($item->importerPercentDone%)",
    ];
  }

  /**
   * Drop a resource from the datastore.
   *
   * If you pass a simple resource identifier, both the database table and the
   * localized resource file (if the file is remote) will be deleted.
   * The post import job status' for the latest version of a resource will
   * also be removed. If you would like to drop the datastore table but keep
   * the localize resource (this may be useful if a large file was successfully
   * localized but the database import failed and you want to redo it) pass the
   * --keep-local argument. In both cases, the appropriate jobstore
   * results (where the status of the import or file-fetch
   * jobs are stored) will be deleted.
   *
   * Note that if you have "Delete local resource" checked in
   * /admin/dkan/resources, the file may already be deleted and therefore
   * --keep-local may not have the desired effect.
   */
  #[CLI\Command(name: 'dkan:datastore:drop', description: 'Drop a resource from the datastore.')]
  #[CLI\Argument(name: 'identifier', description: 'Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".')]
  #[CLI\Option(name: 'keep-local', description: 'Do not remove localized resource, only datastore.')]
  public function drop(string $identifier, array $options = ['keep-local' => FALSE]) {
    $local_resource = $options['keep-local'] ? FALSE : TRUE;
    try {
      $this->datastoreService->drop($identifier, NULL, $local_resource);
      $this->logger->notice('Successfully dropped the datastore for resource ' . $identifier);
    }
    catch (\InvalidArgumentException) {
      // We get an invalid argument exception when the datastore does not exist.
      // This can be because it was never imported, or because the resource
      // is a type that will never be imported, such as a ZIP file.
      $this->logger->warning('Unable to drop datastore for ' . $identifier);
    }
    $post_import_result = $this->postImportResultFactory->initializeFromDistribution(['resource_id' => $identifier]);
    $post_import_result->removeJobStatus();
    $this->logger->notice('Successfully removed the post import job status for resource ' . $identifier);
  }

  /**
   * Drop all datastore tables.
   */
  #[CLI\Command(name: 'dkan:datastore:drop-all', description: 'Drop all datastore tables.')]
  #[CLI\Option(name: 'keep-local', description: 'Do not remove localized resource, only datastore.')]
  public function dropAll(array $options = ['keep-local' => FALSE]) {
    $local_resource = $options['keep-local'] ? FALSE : TRUE;
    $list = $this->importInfoList->buildList();
    foreach ($list as $id => $item) {
      if ($item->fileFetcherStatus === 'done' || $item->importerStatus === 'done') {
        [$id, $version] = explode('_', $id);
        $this->datastoreService->drop($id, $version, $local_resource);
        $this->logger->notice('Successfully dropped the datastore for resource ' . $id);
        $post_import_result = $this->postImportResultFactory->initializeFromDistribution(['resource_id' => $id]);
        $post_import_result->removeJobStatus();
        $this->logger->notice('Successfully removed the post import job status for resource ' . $id);
      }
      else {
        $this->logger->warning('Unable to drop datastore for ' . $id . ' because it was never imported.');
      }
    }
  }

  /**
   * Prepare the local perspective for a resource.
   *
   * Will do the following:
   * - Prepare the directory in the file system.
   * - Add the local_url perspective to the resource mapper. Note this is
   *   missing the file checksum.
   * - Display the info necessary to perform an external file fetch.
   */
  #[CLI\Command(name: 'dkan:datastore:prepare-localized', description: 'Prepare the local perspective for a resource.')]
  #[CLI\Argument(name: 'identifier', description: 'Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".')]
  public function prepareLocalized(string $identifier) {
    $info = $this->resourceLocalizer->prepareLocalized($identifier);
    if ($info) {
      $this->output()->writeln(json_encode($info, JSON_PRETTY_PRINT));
      return DrushCommands::EXIT_SUCCESS;
    }
    $this->output()->writeln('No resource for identifier: ' . $identifier);
    return DrushCommands::EXIT_FAILURE;
  }

  /**
   * Localize a resource (copy from source to the local file system).
   */
  #[CLI\Command(name: 'dkan:datastore:localize', description: 'Localize a resource (copy from source to the local file system).')]
  #[CLI\Argument(name: 'identifier', description: 'Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".')]
  #[CLI\Argument(name: 'version', description: 'Optional version to localize. If not supplied, will use the latest version.')]
  #[CLI\Option(name: 'deferred', description: 'Add the localization to the queue, rather than localizing now.')]
  public function localize(string $identifier, $version = NULL, array $options = ['deferred' => FALSE]) {
    $deferred = $options['deferred'] ? TRUE : FALSE;

    if ($this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version) !== NULL) {
      $result = $this->resourceLocalizer->localizeTask($identifier, $version, $deferred);

      if ($result->getStatus() === Result::DONE) {
        $this->output()->writeln($result->getError());
        return DrushCommands::EXIT_SUCCESS;
      }
      $this->output()->writeln($result->getError());
      return DrushCommands::EXIT_FAILURE;
    }
    $this->output()->writeln('No resource for identifier: ' . $identifier);
    return DrushCommands::EXIT_FAILURE;
  }

  /**
   * Return the dataset uuid associated with the provided datastore table name.
   */
  #[CLI\Command(name: 'dkan:datastore:reverse-dataset-lookup', aliases: ['dkan:datastore:rdl'], description: 'Return the dataset uuid associated with the provided datastore table name.')]
  #[CLI\Argument(name: 'table_name', description: 'Datastore Table name, e.g., "datastore_8b7a21d442d603b113f1a17beac8bcdd".')]
  public function reverseDatasetLookup(string $table_name) {
    $resource_id = '';
    $distribution_uuid = '';
    if ($table_name) {
      $resource_id = $this->datastoreLookup->tableToResourceLookup($table_name);
    }
    if ($resource_id) {
      $distribution_uuid = $this->datastoreLookup->resourceToDistribution($resource_id);
    }
    if ($distribution_uuid) {
      $dataset_uuid = $this->datastoreLookup->distributionToDataset($distribution_uuid);
      // Output to console and end command.
      $this->output()->writeln('Dataset UUID = ' . $dataset_uuid);
      return DrushCommands::EXIT_SUCCESS;
    }
    // @todo This is dead code because if there is no dataset, the command will
    // exit with a failure before this point. We should probably try to catch
    // the exception.
    $this->output()->writeln('Can not map datastore table to dataset: ' . $table_name);
    return DrushCommands::EXIT_FAILURE;
  }

}
