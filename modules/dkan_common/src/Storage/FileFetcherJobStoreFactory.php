<?php

namespace Drupal\common\Storage;

/**
 * Create a job store object for the import process.
 */
class FileFetcherJobStoreFactory extends AbstractJobStoreFactory {

  /**
   * {@inheritDoc}
   *
   * This string contains an ugly hash for historical reasons.
   */
  protected string $tableName = 'jobstore_524493904_filefetcher';

}
