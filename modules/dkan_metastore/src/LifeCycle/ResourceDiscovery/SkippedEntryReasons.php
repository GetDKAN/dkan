<?php

namespace Drupal\dkan_metastore\LifeCycle\ResourceDiscovery;

/**
 * Enum for reasons why a resource discovery entry was skipped.
 */
enum SkippedEntryReasons {
  case InvalidUrl;
  case MissingUrl;
  case UnsupportedType;
}
