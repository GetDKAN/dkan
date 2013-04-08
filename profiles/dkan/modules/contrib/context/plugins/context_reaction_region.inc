<?php

class context_reaction_region extends context_reaction {

  function editor_form($context) {
  }

  function options_form($context) {
    $values = $this->fetch_from_context($context);
    $form = array();
    foreach (list_themes() as $theme) {
      if ($theme->status) {
        $regions = system_region_list($theme->name);
        $default = isset($values[$theme->name]) ? $values[$theme->name]['disable'] : array();

        $form[$theme->name] = array(
          '#type' => 'fieldset',
          '#title' => "Disable Regions in {$theme->name} Theme",
          '#collapsible' => TRUE,
          '#collapsed' => !array_reduce($default, create_function('$a, $b', 'return $a || $b;')),
        );
        $form[$theme->name]['disable'] = array(
          '#type' => 'checkboxes',
          '#title' => t("Disable the following"),
          '#options' => $regions,
          '#default_value' => $default,
        );
      }
    }
    return $form;
  }
  
  function execute(&$page) {  
    global $theme;
    foreach ($this->get_contexts() as $k => $v) { 
      if (isset($v->reactions[$this->plugin][$theme])) {
        $regions = $v->reactions[$this->plugin][$theme]['disable'];
        foreach ($regions as $region => $disable) {
          if ($disable && isset($page[$region])) {
            unset($page[$region]);
          }
        }
      }
    }
  }
}
