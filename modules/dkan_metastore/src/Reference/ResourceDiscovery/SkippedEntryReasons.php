<?php

namespace Drupal\dkan_metastore\Reference\ResourceDiscovery;

/**
 * Enum for reasons why a resource discovery entry was skipped.
 *
 * May someday include UnsupportedType, but for now that logic lives closer to
 * the datastore.
 */
enum SkippedEntryReasons {
  case InvalidUrl;
  case MissingUrl;
}
