<?php

namespace Drupal\datastore\Storage;

use Drupal\common\Storage\AbstractJobStoreFactory;

/**
 * Create a job store object for the import process.
 */
class ImportJobStoreFactory extends AbstractJobStoreFactory {

  /**
   * {@inheritDoc}
   *
   * This string contains an ugly hash for historical reasons.
   */
  protected string $TABLE_NAME = 'jobstore_2613055649_importjob';

}
