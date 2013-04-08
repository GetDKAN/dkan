<?php

/**
 * Expose menu items as a context condition.
 */
class context_condition_menu extends context_condition {
  /**
   * Override of condition_values().
   */
  function condition_values() {
    if (module_exists('menu')) {
      $menus = menu_parent_options(menu_get_menus(), array('mlid' => 0));
      $root_menus = array();
      foreach ($menus as $key => $name) {
        $id = explode(':', $key);
        if ($id[1] == '0') {
          $root_menus[$id[0]] = check_plain($name);
        }
        else {
          $link = menu_link_load($id[1]);
          $identifier = $link['link_path'];
          $root_menu = $root_menus[$id[0]];
          while (isset($menus[$root_menu][$identifier])) {
            $identifier .= "'";
          }
          $menus[$root_menu][$identifier] = $name;
        }
        unset($menus[$key]);
      }
      array_unshift($menus, "-- " . t('None') . " --");
    }
    else {
      $menus = array();
    }
    return $menus;
  }

  /**
   * Override of condition_form().
   * Use multiselect widget.
   */
  function condition_form($context) {
    $form = parent::condition_form($context);
    $form['#type'] = 'select';
    $form['#multiple'] = TRUE;
    return $form;
  }

  /**
   * Override of condition_form_submit().
   * Trim any identifier padding for non-unique path menu items.
   */
  function condition_form_submit($values) {
    // Trim any identifier padding for non-unique path menu items.
    $values = parent::condition_form_submit($values);
    $trimmed = array();
    foreach ($values as $key => $value) {
      $trimmed[trim($key, "'")] = trim($value, "'");
    }
    return $trimmed;
  }

  /**
   * Override of execute().
   */
  function execute() {
    if ($this->condition_used()) {
      $trail = menu_get_active_trail();
      foreach ($trail as $item) {
        if (!empty($item['href'])) {
          foreach ($this->get_contexts($item['href']) as $context) {
            $this->condition_met($context, $item['href']);
          }
        }
      }
    }
  }
}
