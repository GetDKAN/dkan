<?php
/**
 * Expose themes as context reactions.
 */
class context_reaction_theme_html extends context_reaction_theme {
  /**
   * Allow admins to provide additional body classes.
   */
  function options_form($context) {
    $values = $this->fetch_from_context($context);
    $form = array(
      'class' => array(
        '#title' => t('Section class'),
        '#description' => t('Provides this text as an additional body class (in <strong>$classes</strong> in html.tpl.php) when this section is active.'),
        '#type' => 'textfield',
        '#maxlength' => 64,
        '#default_value' => isset($values['class']) ? $values['class'] : '',
      ),
    );
    return $form;
  }

  /**
   * Set additional classes onto the 'body_classes'.
   */
  function execute(&$vars) {
    $classes = array();
    foreach ($this->get_contexts() as $k => $v) {
      if (!empty($v->reactions[$this->plugin]['class'])) {
        $vars['classes_array'][] = $v->reactions[$this->plugin]['class'];
      }
    }
  }
}
