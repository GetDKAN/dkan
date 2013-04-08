<?php


/**
 * @file
 * Hooks provided by the Organic groups context module.
 */

/**
 * @addtgrouproup hooks
 * @{
 */

/**
 * Add context negotiation info.
 *
 * Define context "handlers".
 * - name: The human readable name of the context handler.
 * - Description: The description of the context handler.
 * - callback: The name of an implementation of callback_og_context_handler().
 * - menu path: Optional; The menu path as retrieved from menu_get_item() that
 *   is required for the context handler to be invoked.
 */
function hook_og_context_negotiation_info() {
  $providers = array();

  $providers['foo'] = array(
    'name' => t('Foo'),
    'description' => t("Determine context by checking if some foo value."),
    'callback' => 'foo_og_context_handler',
    // Invoke the context handler only on the following path.
    'menu path' => array('foo/%', 'foo/%/bar'),
  );

  return $providers;
}

/**
 * @} End of "addtgrouproup hooks".
 */

/**
 * @addtgrouproup callbacks
 * @{
 */

/**
 * Evaluates and return group IDs to provide group context.
 *
 * Callback for hook_og_context_negotiation_info().
 *
 * @return
 *  A nested array of group IDs, grouped first by entity type. Each value is
 *  a flat array of group IDs.
 */
function callback_og_context_handler() {
  return array(
    'node' => array(1, 2, 3),
  );
}

/**
 * @} End of "addtgrouproup callbacks".
 */
