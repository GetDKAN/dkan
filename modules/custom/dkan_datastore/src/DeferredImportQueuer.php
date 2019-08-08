<?php

namespace Drupal\dkan_datastore;

use Dkan\Datastore\Resource;

/**
 * DeferredImport, uses resource information to add chunks to the import queue.
 *
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
class DeferredImportQueuer {

  /**
   * Split the resource to be processed by queue.
   *
   * @param string $uuid
   *   Usually UUID of resource being imported or simple job identifier.
   * @param \Dkan\Datastore\Resource $resource
   * @param array $importConfig
   *
   * @return mixed ID of queue item created or false on failure.
   *
   * @throws \RuntimeException
   */
  public function createDeferredResourceImport(string $uuid, Resource $resource, array $importConfig = []) {

    // Attempt to fetch the file in a queue so as to not block user.
    $queueId = $this->getQueue()
      ->createItem([
        'uuid'          => $uuid,
        'resource_id'   => $resource->getId(),
        'file_path'     => $resource->getFilePath(),
        'import_config' => $importConfig,
      ]);

    if (FALSE === $queueId) {
      throw new \RuntimeException("Failed to create file fetcher queue for {$uuid}");
    }

    return $queueId;
  }

  /**
   * Fetch the queue.
   *
   * @codeCoverageIgnore
   *
   * @return \Drupal\Core\Queue\QueueInterface
   */
  protected function getQueue() {
    return \Drupal::queue('dkan_datastore_file_fetcher_queue');
  }

}
