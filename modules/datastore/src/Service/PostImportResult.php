<?php

namespace Drupal\datastore\Service;

use Drupal\Core\Database\Connection;

class PostImportResult {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new PostImport object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Stores post import result in the database.
   *
   * @param string $identifier
   *   The resource identifier.
   * @param string $post_import_status
   *   The overall status.
   * @param string $post_import_percent_done
   *   The percent done, currently either 0 for errors or 100 for no errors.
   * @param string $post_import_error
   *   The error message if any.
   */
  public function storeJobStatus($identifier, $post_import_status, $post_import_percent_done, $post_import_error) {
    $this->connection->insert('dkan_post_import_job_status')
      ->fields([
        'resource_identifier' => $identifier,
        'post_import_status' => $post_import_status,
        'post_import_percent_done' => $post_import_percent_done,
        'post_import_error' => $post_import_error,
      ])
      ->execute();
  }

  /**
   * Retrieve post import result in the database.
   *
   * @param string $identifier
   *   The resource identifier of the distribution to retrieve post import result.
   */
  public function retrieveJobStatus($identifier) {
    return $this->connection->select('dkan_post_import_job_status')
      ->condition('resource_identifier', $identifier, '=')
      ->fields('dkan_post_import_job_status', [
        'post_import_status',
        'post_import_percent_done',
        'post_import_error',
      ])
      ->execute()
      ->fetchAssoc();
    }

}
