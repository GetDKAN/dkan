<?php

/**
 * Implements of hook_rules_condition_info().
 */
function multistep_rules_condition_info() {
  return array(
    'multistep_is_complete' => array(
      'label' => t('Content is complete'),
      'group' => t('Multistep'),
      'parameter' => array(
        'node' => array(
          'label' => t('Content'),
          'type' => 'node',
        ),
      ),
    ),
    'multistep_will_complete' => array(
      'label' => t('Content will complete'),
      'group' => t('Multistep'),
      'parameter' => array(
        'node' => array(
          'label' => t('Content'),
          'type' => 'node',
        ),
      ),
    ),
  );
}