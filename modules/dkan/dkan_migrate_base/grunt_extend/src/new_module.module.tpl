<?php

/**
 * @file
 * Harvesting module for <%- name %>
 */

/**
 * Implements hook_migrate_api().
 */
function <%- name %>_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      '<%- name %>' => array(
        'title' => t('<%- name %>'),
      ),
    ),
    'migrations' => array(
      '<%- name %>' => array(
        'class_name' => '<%- endpointClass %>',
        'group_name' => '<%- name %>',
        'title' => t('<%- name %> Export'),
      ),
    ),
  );
  return $api;
}

class <%- endpointClass %> extends <%- endpointClassExtends %> {
  /**
   * Registers endpoints.
   */
  public function __construct($arguments) {
    $arguments['endpoint'] = '<%- endpoint %>';
    parent::__construct($arguments);
  }

  /**
   * Implements prepare.
   */
  public function prepare($node,$row) {
    parent::prepare($node, $row);
  }
}

/**
 * Deregisters migrations.
 */
function <%- name %>_migrations_disable() {
  Migration::deregisterMigration('<%- name %>');
}
