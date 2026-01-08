<?php

namespace Drupal\datastore\DataDictionary;

/**
 * Alter table query interface.
 *
 * Provides ability to alter schema of existing datastore tables.
 */
interface AlterTableQueryInterface {

  /**
   * Apply data dictionary types to the given table.
   *
   * @throws \Exception
   *   Thrown when you try to execute more than once.
   */
  public function execute(): void;

}
