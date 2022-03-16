<?php

namespace Drupal\datastore\DataDictionary;

interface AlterTableQueryInterface {

  /**
   * Apply data dictionary types to the given table.
   */
  public function applyDataTypes(): void;

}
