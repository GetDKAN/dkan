<?php

namespace Drupal\datastore;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\UnstructuredListData;
use Drupal\common\DataResource;
use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\ResourceMapper;
use Drush\Commands\DrushCommands;
use Procrastinator\Result;

/**
 * Drush commands for controlling the datastore.
 *
 * @codeCoverageIgnore
 */
class Drush extends DrushCommands {

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastoreService;

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  protected $datastoreService;

  /**
   * The datastore resource localizer.
   */
  protected ResourceLocalizer $resourceLocalizer;

  /**
   * Resource mapper service.
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Import info list service.
   */
  private ImportInfoList $importInfoList;

  /**
   * The PostImportResultFactory service.
   *
   * @var \Drupal\datastore\PostImportResultFactory
   */
  protected PostImportResultFactory $postImportResultFactory;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct(
    MetastoreService $metastoreService,
    DatastoreService $datastoreService,
    ResourceLocalizer $resourceLocalizer,
    ResourceMapper $resourceMapper,
    ImportInfoList $importInfoList,
    PostImportResultFactory $postImportResultFactory
  ) {
    parent::__construct();
    $this->metastoreService = $metastoreService;
    $this->datastoreService = $datastoreService;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->resourceMapper = $resourceMapper;
    $this->importInfoList = $importInfoList;
    $this->postImportResultFactory = $postImportResultFactory;
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
   * @param string $identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   * @param array $options
   *   Command line options.
   *
   * @option deferred
   *   Add the import to the datastore_import queue, rather than importing now.
   *
   * @todo pass configurable options for csv delimiter, quote, and escape
   *   characters.
   * @command dkan:datastore:import
   */
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
   *
   * @field-labels
   *   uuid: Resource UUID
   *   fileName: File Name
   *   fileFetcherStatus: FileFetcher
   *   fileFetcherBytes: Processed
   *   importerStatus: Importer
   *   importerBytes: Processed
   *
   * @options format The format of the data.
   * @options status Show imports of the given status.
   * @options uuid-only Only the list of uuids.
   *
   * @command dkan:datastore:list
   */
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
   * Private.
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
        fn() => \format_size($item->fileFetcherBytes)
      ) . " ($item->fileFetcherPercentDone%)",
      'importerStatus' => $item->importerStatus,
      'importerBytes' => DeprecationHelper::backwardsCompatibleCall(
        \Drupal::VERSION, '10.2.0',
        fn() => ByteSizeMarkup::create($item->importerBytes),
        fn() => \format_size($item->importerBytes)
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
   *
   * @param string $identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   * @param array $options
   *   Options array.
   *
   * @option keep-local
   *   Do not remove localized resource, only datastore.
   *
   * @command dkan:datastore:drop
   */
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
   * Drop a ALL datastore tables.
   *
   * @command dkan:datastore:drop-all
   */
  public function dropAll() {
    /** @var \RootedData\RootedJsonData $distribution*/
    foreach ($this->metastoreService->getAll('distribution') as $distribution) {
      if ($uuid = $distribution->get('$[data]["%Ref:downloadURL"][0][data][identifier]') ?? FALSE) {
        $this->drop($uuid);
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
   *
   * @param string $identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   *
   * @command dkan:datastore:prepare-localized
   */
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
   *
   * @param string $identifier
   *   Datastore resource identifier, e.g., "b210fb966b5f68be0421b928631e5d51".
   * @param string $version
   *   (Optional) The version to localize. If not supplied, will use the latest
   *   version.
   * @param array $options
   *   Command options.
   *
   * @command dkan:datastore:localize
   *
   * @option deferred
   *   Add the localization to the  queue, rather than localizing now.
   */
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

}
