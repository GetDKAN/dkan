<?php

/**
 * @file
 * Hooks provided by the DKAN Dataset REST API module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the list of validation handlers applied to all fields submited via API.
 *
 * @param  $handlers
 *   Array of validation handler functions to call. Handlers should be functions
 *   that accept arguments:
 *     * $node - the node object being validated, not yet saved.
 *     * $field_name - the field name being validated.
 *     * $info - the info array for the field.
 *
 */
function hook_dkan_dataset_rest_api_field_validate_alter(&$handlers) {
  $handlers[] = 'mymodule_dkan_dataset_rest_api_validation_handler';
}

/**
 * @} End of "addtogroup hooks".
 */
