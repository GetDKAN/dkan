<?php

namespace Drupal\datastore;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\UnstructuredListData;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\MetastoreService;
use Drupal\datastore\Service\PostImport;
use Drupal\metastore\ResourceMapper;
use Drush\Commands\DrushCommands;

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
   * The PostImport service.
   *
   * @var \Drupal\datastore\Service\PostImport
   */
  protected PostImport $postImport;

  /**
   * The datastore resource localizer.
   *
   * @var \Drupal\datastore\Service\ResourceLocalizer
   */
  protected ResourceLocalizer $resourceLocalizer;

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Import info list service.
   *
   * @var \Drupal\datastore\Service\Info\ImportInfoList
   */
  private ImportInfoList $importInfoList;

  /**
   * Constructor for DkanDatastoreCommands.
   */
  public function __construct(
    MetastoreService $metastoreService,
    DatastoreService $datastoreService,
    PostImport $postImport,
    ResourceLocalizer $resourceLocalizer,
    ResourceMapper $resourceMapper,
    ImportInfoList $importInfoList
  ) {
    parent::__construct();
    $this->metastoreService = $metastoreService;
    $this->datastoreService = $datastoreService;
    $this->postImport = $postImport;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->resourceMapper = $resourceMapper;
    $this->importInfoList = $importInfoList;
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
   *
   * @option deferred
   *   Add the import to the datastore_import queue, rather than importing now.
   *
   * @todo pass configurable options for csv delimiter, quote, and escape
   *   characters.
   * @command dkan:datastore:import
   */
  public function import(string $identifier, array $options = ['deferred' => FALSE]) {
    $deferred = $options['deferred'] ? TRUE : FALSE;

    try {
      $result = $this->datastoreService->import($identifier, $deferred);
      if ($deferred) {
        $this->logger->notice('Queued import for ' . $identifier);
      }
      else {
        $this->logger->notice('Ran import for ' . $identifier);
        foreach ($result as $jobname => $result_object) {
          /** @var \Procrastinator\Result $result_object */
          $this->logger->notice('[' . $jobname . '] ' . $result_object->getStatus());
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error("No resource found to import with identifier {$identifier}");
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
  public function list($options = [
    'format' => 'table',
    'status' => NULL,
    'uuid-only' => FALSE,
  ]) {
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
    return [
      'uuid' => $uuid,
      'fileName' => $item->fileName,
      'fileFetcherStatus' => $item->fileFetcherStatus,
      'fileFetcherBytes' => \format_size($item->fileFetcherBytes) . " ($item->fileFetcherPercentDone%)",
      'importerStatus' => $item->importerStatus,
      'importerBytes' => \format_size($item->importerBytes) . " ($item->importerPercentDone%)",
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
   *
   * @option keep-local
   *   Do not remove localized resource, only datastore.
   *
   * @command dkan:datastore:drop
   */
  public function drop(string $identifier, array $options = ['keep-local' => FALSE]) {
    $local_resource = $options['keep-local'] ? FALSE : TRUE;
    $this->datastoreService->drop($identifier, NULL, $local_resource);
    $this->logger->notice("Successfully dropped the datastore for resource {$identifier}");
    $this->postImport->removeJobStatus($identifier);
    $this->logger->notice("Successfully removed the post import job status for resource {$identifier}");
  }

  /**
   * Drop a ALL datastore tables.
   *
   * @command dkan:datastore:drop-all
   */
  public function dropAll() {
    foreach ($this->metastoreService->getAll('distribution') as $distribution) {
      $uuid = $distribution->data->{'%Ref:downloadURL'}[0]->data->identifier;
      $this->drop($uuid);
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
   *
   * @todo Move all this to the ResourceLocalizer so we're responsible for test
   *   coverage.
   */
  public function prepareLocalized(string $identifier) {
    if ($resource = $this->resourceMapper->get($identifier)) {
      // ResourceLocalizer will create the public directory here.
      $public_dir = $this->resourceLocalizer->getPublicLocalizedDirectory($resource);
      $localized_filepath = $this->resourceLocalizer->localizeFilePath($resource);
      $localized_resource = $resource->createNewPerspective(
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $localized_filepath
      );
      try {
        $this->resourceMapper->registerNewPerspective($localized_resource);
      }
      catch (AlreadyRegistered $e) {
        // Catch the already-registered exception so we can continue to show
        // the file info to the user.
        $this->logger()->warning($e->getMessage());
      }
      // @todo inject file system service.
      $file_system = $this->resourceLocalizer->getFileSystem();
      $info = [
        'source' => $resource->getFilePath(),
        'path_uri' => $public_dir,
        'path' => $file_system->realpath($public_dir),
        'file_uri' => $localized_filepath,
        'file' => $file_system->realpath($localized_filepath),
      ];
      $this->output()->writeln(json_encode($info, JSON_PRETTY_PRINT));
      return DrushCommands::EXIT_SUCCESS;
    }
    $this->output()->writeln('No resource for identifier: ' . $identifier);
    return DrushCommands::EXIT_FAILURE;
  }

}
