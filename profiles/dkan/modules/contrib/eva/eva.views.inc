<?php

/**
 * Implements hook_views_plugins().
 */
function eva_views_plugins() {
  return array(
    'display' => array(
      'entity_view' => array(
        'title' => t('EVA Field'),
        'help' => t("Display the view as a part of an entity's content."),
        'handler' => 'eva_plugin_display_entity',
        'theme' => 'eva_display_entity_view',
        'uses hook entity view' => TRUE,
        'use ajax' => TRUE,
        'use pager' => TRUE,
        'use more' => TRUE,
        'accept attachments' => TRUE,
        'admin' => t('Entity content'),
        'help topic' => 'display-entity',
      ),
    ),
  );
}
