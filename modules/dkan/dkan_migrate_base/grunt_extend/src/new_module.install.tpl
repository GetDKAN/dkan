<?php

/**
 * @file
 * Install file for <%- name %>.
 */

/**
 * Implements hook_disable().
 */
function <%- name %>_disable() {
  <%- name %>_migrations_disable();
}
