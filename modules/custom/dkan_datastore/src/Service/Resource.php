<?php

namespace Drupal\dkan_datastore\Service;

use Procrastinator\Result;
use FileFetcher\FileFetcher;
use Dkan\Datastore\Resource as R;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\File\FileSystem;
use Drupal\node\NodeInterface;
use Drupal\dkan_datastore\Storage\JobStore;

/**
 * Class Resource.
 */
class Resource {
  const DEFAULT_TIMELIMIT = 50;

  private $uuid;
  private $entityRepository;
  private $fileSystem;
  private $jobStore;

  /**
   * Constructor.
   */
  public function __construct(
    string $uuid,
    EntityRepository $entityRepository,
    FileSystem $fileSystem,
    JobStore $jobStore
  ) {
    $this->uuid = $uuid;
    $this->entityRepository = $entityRepository;
    $this->fileSystem = $fileSystem;
    $this->jobStore = $jobStore;
  }

  /**
   * Create a resource object.
   *
   * @param bool $useFileFetcher
   *   If file fetcher was used, get path from the file fetcher.
   *
   * @return \Dkan\Datastore\Resource
   *   Datastore resource object.
   */
  public function get($useFileFetcher = FALSE): ?R {
    $node = $this->entityRepository->loadEntityByUuid('node', $this->uuid);
    if (!$node) {
      return NULL;
    }

    if ($useFileFetcher == TRUE) {
      $fileFetcher = $this->getFileFetcher($this->uuid);
      $fileFetcher->run();
      $this->jobStore->store($this->uuid, $fileFetcher);

      if ($fileFetcher->getResult()->getStatus() != Result::DONE) {
        return NULL;
      }

      $json = $fileFetcher->getResult()->getData();
      $fileData = json_decode($json);
      return new R($node->id(), $fileData->destination);
    }
    else {
      return new R($node->id(), $this->getResourceFilePathFromNode($node));
    }
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
    $fileFetcher = new FileFetcher($filePath, $tmpDirectory);
    $fileFetcher->setTimeLimit(self::DEFAULT_TIMELIMIT);
    return $fileFetcher;
  }

  /**
   * Private.
   */
  private function getFileFetcher(): FileFetcher {
    if (!$fileFetcher = $this->getStoredFileFetcher($this->uuid)) {
      $node = $this->entityRepository->loadEntityByUuid('node', $this->uuid);
      $filePath = $this->getResourceFilePathFromNode($node);

      $tmpDirectory = $this->fileSystem->realpath("public://") . "/dkan-tmp";
      $this->fileSystem->prepareDirectory($tmpDirectory, FileSystem::CREATE_DIRECTORY | FileSystem::MODIFY_PERMISSIONS);

      $fileFetcher = $this->getFileFetcherInstance($filePath, $tmpDirectory);
      $this->jobStore->store($this->uuid, $fileFetcher);
    }
    if (!($fileFetcher instanceof FileFetcher)) {
      throw new \Exception("Could not load file-fetcher for uuid $this->uuid");
    }
    return $fileFetcher;
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
  private function getStoredFileFetcher(): ?FileFetcher {
    if ($fileFetcher = $this->jobStore->retrieve($this->uuid, FileFetcher::class)) {
      return $fileFetcher;
    }
    return NULL;
  }

}
