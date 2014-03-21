<?php

/**
 * Expose active contexts as a context condition.
 */
class context_condition_context extends context_condition_path {
  function execute() {
    if ($this->condition_used()) {
      $active_contexts = array_keys(context_active_contexts());
      foreach ($this->get_contexts() as $context) {
        if (!in_array($context->name, $active_contexts, TRUE) && $values = $this->fetch_from_context($context, 'values')) {
          // Always check against the active contexts.
          if ($this->match(array_keys(context_active_contexts()), $values)) {
            $this->condition_met($context);
          }
        }
      }
      // If the list of active contexts has changed, we need to recurse.
      if ($active_contexts != array_keys(context_active_contexts())) {
        $this->execute();
      }
    }
  }

  /**
   * Retrieve all context conditions.
   *
   * This method is slightly adapted to context_condition::get_contexts() in
   * order to ensure that a context that is used as condition in another context
   * gets handled before.
   */
  function get_contexts($value = NULL) {
    $map = context_condition_map();
    $map = isset($map[$this->plugin]) ? $map[$this->plugin] : array();

    $contexts = array();

    // Add the contexts that are needed for conditions in the other contexts
    // first. Start with the negated ones first, as we can not unset a met
    // condition afterwards.
    krsort($map);
    foreach ($map as $key => $submap) {
      // Negated context conditions start with a "~".
      if (substr($key, 0, 1) == "~") {
        $key = substr($key, 1);
      }
      if (!isset($contexts[$key])) {
        $context = context_load($key);
        // Check if context exists. This will fail for wildcards.
        if ($context) {
          $contexts[$context->name] = $context;
        }
      }
    }
    foreach ($map as $key => $submap) {
      foreach ($submap as $name) {
        if (!isset($contexts[$name])) {
          $context = context_load($name);
          $contexts[$context->name] = $context;
        }
      }
    }
    return $contexts;
  }
}
