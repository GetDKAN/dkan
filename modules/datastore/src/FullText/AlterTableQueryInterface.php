<?php

namespace Drupal\datastore\FullText;

/**
 * Alter table query interface.
 *
 * Provides ability to alter schema of existing datastore tables.
 */
interface AlterTableQueryInterface {

  /**
   * Apply fulltext indexes to the given table.
   */
  public function applyFullTextIndexes(): void;

}
