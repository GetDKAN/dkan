<?php

namespace Drupal\dkan_datastore\Storage;

use Drupal\dkan_common\Storage\AbstractJobStoreFactory;

/**
 * Create a job store object for the import process.
 */
class ImportJobStoreFactory extends AbstractJobStoreFactory {

  /**
   * {@inheritDoc}
   *
   * This string contains an ugly hash for historical reasons.
   */
  protected string $tableName = 'jobstore_2613055649_importjob';

}
