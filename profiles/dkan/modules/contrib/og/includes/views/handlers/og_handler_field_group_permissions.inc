<?php

/**
 * Field handler to provide a list of group permissions.
 */
class og_handler_field_group_permissions extends og_handler_field_prerender_list {
  function construct() {
    parent::construct();
    $this->additional_fields['uid'] = array('table' => 'users', 'field' => 'uid');
  }

  function pre_render(&$values) {
    $list = array();
    $this->items = array();

    foreach ($values as $value) {
      $list[] = array(
        'uid' => $value->{$this->aliases['uid']},
        'gid' => $value->{$this->field_alias},
      );
    }

    // Array of all the role IDs we will need to get a role name.
    $all_rids = array();
    if ($list) {
      foreach ($list as &$value) {
        $rids = og_get_user_roles($value['gid'], $value['uid']);
        // Hold the user role IDs per group and per user.
        $value['rids'] = $rids;
        $all_rids = array_merge($all_rids, $rids);
      }
      if ($all_rids) {
        $all_perms = og_get_permissions();
        // Get the permissions of all the roles we need to show.
        $perms = og_role_permissions($all_rids);
        foreach ($list as $value) {
          foreach ($value['rids'] as $rid) {
            foreach ($perms[$rid] as $perm => $perm_value) {
              $this->items[$value['gid']][$value['uid']][$perm]['perm'] = $all_perms[$perm]['title'];
            }
          }
        }
      }
    }
  }

  function render_item($rid, $item) {
    return $item['perm'];
  }

  // TODO:
  function document_self_tokens(&$tokens) {
  }

  function add_self_tokens(&$tokens, $item) {
  }
}
