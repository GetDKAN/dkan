<?php

namespace Drupal\dkan_datastore\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\Queue\QueueInterface;

/**
 * Fetches the file for a given resource.
 *
 * @QueueWorker(
 *   id = "dkan_datastore_file_fetcher_queue",
 *   title = @Translation("Fetches the file if necessary."),
 *   cron = {"time" = 1200}
 * )
 */
class FileFetcherQueue extends QueueWorkerBase {

  /**
   * {@inheritdocs}.
   */
  public function processItem($data) {
    $uuid         = $data['uuid'];
    $resourceId   = $data['resource_id'];
    $filePath     = $data['file_path'];
    $importConfig = $data['import_config'];

    $actualFilePath = $this->fetchFile($uuid, $filePath);

    // There should only be one iteration of this queue.
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->getImporterQueue();

    // Queue is self calling and should keep going until complete.
    return $queue->createItem([
        'uuid'              => $uuid,
        // Resource id is used to create table.
        // and has to be same as original.
        'resource_id'       => $resourceId,
        'file_identifier'   => $this->sanitizeString($actualFilePath),
        'file_path'         => $actualFilePath,
        'import_config'     => $importConfig,
        'file_is_temporary' => $this->isFileTemporary($actualFilePath),
    ]);
  }

  /**
   * Tests if the file want to use is usable attempt to make it usable.
   *
   * @param string $uuid
   *   UUID.
   * @param string $filePath
   *   file.
   *
   * @return string usable file path,
   *
   * @throws \Exception If fails to get a usable file.
   */
  protected function fetchFile(string $uuid, string $filePath): string {

    try {

      // Try to download the file some other way.
      // using this method to allow for custom scheme handlers.
      $source = $this->getFileObject($filePath);

      // Is on local file system.
      if ($source->isFile()) {
        return $filePath;
      }

      $tmpFile = $this->getTemporaryFile($uuid);
      $dest    = $this->getFileObject($tmpFile, 'w');

      $this->fileCopy($source, $dest);

      return $tmpFile;
    } catch (\Exception $e) {
      // Failed to get the file.
      throw new SuspendQueueException("Unable to fetch {$filePath} for resource {$uuid}. Reason: " . $e->getMessage(), 0, $e);
    }
  }

  /**
   *
   * @param string $filePath
   * @param string $filePath
   * @return \SplFileObject
   */
  protected function getFileObject($filePath, $mode = 'r') {
    return new \SplFileObject($filePath, $mode);
  }

  /**
   *
   * @param \SplFileObject $source
   * @param \SplFileObject $dest
   * @return int
   * @throws RuntimeException If either read or write fails
   */
  protected function fileCopy(\SplFileObject $source, \SplFileObject $dest) {

    $total = 0;
    while ($source->valid()) {

      // Read a large enough frame to reduce overheads.
      $read = $source->fread(128 * 1024);

      if (FALSE === $read) {
        throw new \RuntimeException("Failed to read from source " . $source->getPath());
      }

      $bytesWritten = $dest->fwrite($read);

      if ($bytesWritten !== strlen($read)) {
        throw new \RuntimeException("Failed to write to destination " . $dest->getPath());
      }

      $total += $bytesWritten;
    }

    return $total;
  }

  /**
   * Generate a tmp filepath for a given $uuid.
   *
   * @param string $uuid
   *   UUID.
   *
   * @return string
   */
  protected function getTemporaryFile(string $uuid): string {
    return $this->getTemporaryDirectory() . '/dkan-resource-' . $this->sanitizeString($uuid);
  }

  /**
   * Determine if the file is in the temporary fetched file.
   *
   * @param string $filePath
   *
   * @return bool
   */
  protected function isFileTemporary(string $filePath): bool {
    return 0 === strpos($filePath, $this->getTemporaryDirectory() . '/dkan-resource-');
  }

  /**
   * returns the temporary directory used by drupal.
   *
   * @return string
   */
  protected function getTemporaryDirectory() {
    return file_directory_temp();
  }

  /**
   * Get the queue for the datastore_import.
   *
   * @return \Drupal\Core\Queue\QueueInterface
   */
  public function getImporterQueue(): QueueInterface {
    return \Drupal::service('queue')
        ->get('dkan_datastore_import_queue');
  }

  /**
   *
   * @param string $string
   * @return string
   */
  protected function sanitizeString($string) {
    return preg_replace('~[^a-z0-9]+~', '_', strtolower($string));
  }

}
