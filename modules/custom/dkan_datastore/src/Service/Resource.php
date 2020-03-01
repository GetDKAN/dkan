<?php

namespace Drupal\dkan_datastore\Service;

use Drupal\dkan_datastore\Storage\JobStoreFactory;
use Procrastinator\Result;
use FileFetcher\FileFetcher;
use Dkan\Datastore\Resource as R;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\File\FileSystem;
use Drupal\node\NodeInterface;

/**
 * Class Resource.
 */
class Resource {
  const DEFAULT_TIMELIMIT = 50;

  private $uuid;
  private $entityRepository;
  private $fileSystem;
  private $jobStoreFactory;

  /**
   * Constructor.
   */
  public function __construct(
    string $uuid,
    EntityRepository $entityRepository,
    FileSystem $fileSystem,
    JobStoreFactory $jobStoreFactory
  ) {
    $this->uuid = $uuid;
    $this->entityRepository = $entityRepository;
    $this->fileSystem = $fileSystem;
    $this->jobStoreFactory = $jobStoreFactory;
  }

  /**
   * Create a resource object.
   *
   * @param bool $useFileFetcher
   *   If file fetcher was used, get path from the file fetcher.
   * @param bool $runFileFetcher
   *   If file fetcher was used, run it.
   *
   * @return \Dkan\Datastore\Resource
   *   Datastore resource object.
   */
  public function get($useFileFetcher = FALSE, $runFileFetcher = TRUE): ?R {
    $node = $this->entityRepository->loadEntityByUuid('node', $this->uuid);
    if (!$node) {
      return NULL;
    }

    if ($useFileFetcher == TRUE) {
      return $this->getResourceFromFileFetcher($node, $runFileFetcher);
    }
    else {
      return new R($node->id(), $this->getResourceFilePathFromNode($node));
    }
  }

  /**
   * Remove the resource object.
   *
   * Resource objects are dynamic, so they themselves do not need to be removed.
   * However, to create a resource we use the filefetcher. Filefetcher states
   * are stored, and once fetching is finished a file exists in the file system.
   * We need to clean up both of those things.
   */
  public function remove() {
    $fileFetcher = $this->getFileFetcher();
    $filePath = $fileFetcher->getStateProperty('destination');
    if (file_exists($filePath)) {
      unlink($filePath);
    }
    $this->jobStoreFactory->getInstance(FileFetcher::class)->remove($this->uuid);
  }

  /**
   * Get result.
   */
  public function getResult(): Result {
    $fileFetcher = $this->getFileFetcher();
    return isset($fileFetcher) ? $fileFetcher->getResult() : new Result();
  }

  /**
   * Protected.
   *
   * @codeCoverageIgnore
   */
  protected function getFileFetcherInstance($filePath, $tmpDirectory) {
    $fileFetcher = FileFetcher::get($this->uuid, $this->jobStoreFactory->getInstance(FileFetcher::class), [
      'filePath' => $filePath,
      'temporaryDirectory' => $tmpDirectory,
    ]);
    $fileFetcher->setTimeLimit(self::DEFAULT_TIMELIMIT);
    return $fileFetcher;
  }

  /**
   * Get FileFetcher.
   */
  public function getFileFetcher(): FileFetcher {

    $node = $this->entityRepository->loadEntityByUuid('node', $this->uuid);

    if (!$node) {
      throw new \Exception("No node found for uuid {$this->uuid}");
    }

    $filePath = $this->getResourceFilePathFromNode($node);

    $tmpDirectory = $this->fileSystem->realpath("public://") . "/dkan-tmp";
    $this->fileSystem->prepareDirectory($tmpDirectory, FileSystem::CREATE_DIRECTORY | FileSystem::MODIFY_PERMISSIONS);

    return $this->getFileFetcherInstance($filePath, $tmpDirectory);
  }

  /**
   * Given a resource node object, return the path to the resource file.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A Drupal node.
   *
   * @return string
   *   File path.
   *
   * @throws \Exception
   *   Throws exception if validation of entity or data fails.
   */
  private function getResourceFilePathFromNode(NodeInterface $node): string {

    $meta = $node->get('field_json_metadata')->get(0)->getValue();

    if (!isset($meta['value'])) {
      throw new \Exception("Entity for {$node->uuid()} does not have required field `field_json_metadata`.");
    }

    $metadata = json_decode($meta['value']);

    if (!($metadata instanceof \stdClass)) {
      throw new \Exception("Invalid metadata information or missing file information.");
    }

    if (isset($metadata->data->downloadURL)) {
      return $metadata->data->downloadURL;
    }

    throw new \Exception("Invalid metadata information or missing file information.");
  }

  /**
   * Private.
   */
  private function getResourceFromFileFetcher($node, $runFileFetcher) {
    $fileFetcher = $this->getFileFetcher($this->uuid);

    if ($runFileFetcher) {
      $fileFetcher->run();
    }

    if ($fileFetcher->getResult()->getStatus() != Result::DONE) {
      return NULL;
    }

    $json = $fileFetcher->getResult()->getData();
    $fileData = json_decode($json);
    return new R($node->id(), $fileData->destination);
  }

}
