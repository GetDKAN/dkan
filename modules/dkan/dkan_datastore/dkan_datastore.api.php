<?php

/**
 * @file
 * Hooks provided by the DKAN Datastore module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Execute actions right after importing a resource to the datastore.
 *
 * @param $resource
 *   Resource object.
 */
function hook_datastore_post_import($resource) {
  if ($resource->getId()) {
    // Load resource node and execute needed actions.
  }
}

/**
 * @} End of "addtogroup hooks".
 */
