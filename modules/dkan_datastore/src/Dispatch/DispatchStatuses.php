<?php

namespace Drupal\dkan_datastore\Dispatch;

/**
 * Enum for dispatch statuses.
 */
enum DispatchStatuses {
  case Processed;
  case Skipped;
  case Failed;
}
