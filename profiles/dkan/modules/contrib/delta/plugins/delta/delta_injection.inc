<?php

class delta_injection {
  var $plugin;
  var $delta;
  var $original;
  var $settings;

  /**
   * Constructor. Do not override.
   */
  function __construct($plugin, $delta) {
    $this->plugin = $plugin;
    $this->delta = delta_load($delta);
  }

  /**
   * @todo
   */
  function variables() {
    return array();
  }

  /**
   * @todo
   */
  function config($name) {
    if (isset($this->settings[$name])) {
      return $this->settings[$name];
    }

    if (!empty($this->delta->parent)) {
      if ($plugin = delta_get_plugin($this->delta->parent, $this->plugin['name'])) {
        $this->settings[$name] = isset($this->delta->settings[$name]) ? delta_merge($plugin->config($name), $this->delta->settings[$name]) : $plugin->config($name);
      }
    }

    if (!isset($this->settings[$name])) {
      $this->settings[$name] = isset($this->delta->settings[$name]) ? delta_merge(variable_get($name), $this->delta->settings[$name]) : variable_get($name);
    }

    return $this->settings[$name];
  }

  /**
   * @todo
   */
  function inject() {
    foreach ($this->variables() as $name) {
      $settings = $this->config($name);

      if (isset($settings)) {
        $this->backup($name, delta_variable_set($name, $settings));
      }
    }
  }

  /**
   * @todo
   */
  function revoke() {
    foreach ($this->variables() as $name) {
      delta_variable_set($name, $this->backup($name));
    }
  }

  /**
   * @todo
   */
  function backup($name, $value = NULL) {
    if (isset($value) && !isset($this->original[$name])) {
      $this->original[$name] = $value;
    }

    if (isset($this->original[$name])) {
      return $this->original[$name];
    }
  }

  /**
   * @todo
   */
  function presave($form, &$form_state) {
    if ($this->delta->mode == DELTA_PRESERVE) {
      $base = array();

      if (!empty($this->delta->parent)) {
        if ($plugin = delta_get_plugin($this->delta->parent, $this->plugin['name'])) {
          foreach ($this->variables() as $variable) {
            $base[$variable] = $plugin->config($variable);
          }
        }
      }

      foreach ($this->variables() as $variable) {
        if (isset($base[$variable])) {
          $this->delta->settings[$variable] = delta_reduce($this->delta->settings[$variable], $base[$variable]);
        }
        else {
          $this->delta->settings[$variable] = delta_reduce(
            is_array($this->delta->settings[$variable]) ? $this->delta->settings[$variable] : array(),
            is_array(variable_get($variable)) ? variable_get($variable) : array());
        }
      }
    }
  }

  /**
   * @todo
   */
  function form_alter(&$form, &$form_state) {
    // Nothing to do here.
  }

  /**
   * @todo
   */
  function form_validate($form, &$form_state) {
    foreach ($this->variables() as $name) {
      $this->backup($name, variable_get($name));
    }
  }

  /**
   * @todo
   */
  function form_submit($form, &$form_state) {
    foreach ($this->variables() as $name) {
      $this->delta->settings[$name] = variable_get($name);

      variable_set($name, $this->backup($name));
    }
  }
}
