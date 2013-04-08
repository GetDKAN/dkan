<?php

/**
 * Field handler to provide a list of OG roles of a user.
 */
class og_handler_field_user_roles extends og_handler_field_prerender_list {

  function query() {
    // Get a user table we can relate to.
    // @todo: Make the used user relationship configurable.
    foreach ($this->query->relationships as $alias => $info) {
      if ($info['table'] == 'users') {
        $user_table_alias = $alias;
      }
      elseif ($info['table'] == 'og_membership') {
        $og_membership_table_alias = $alias;
      }

      if (!empty($user_table_alias) && !empty($og_membership_table_alias)) {
        break;
      }
    }

    // If there is a users table present use its uid. Else, skip this field.
    if (!empty($user_table_alias)) {
      $this->aliases['uid'] = $this->query->add_field($user_table_alias, 'uid');
    }
    if (!empty($og_membership_table_alias)) {
      $this->aliases['group_type'] = $this->query->add_field($og_membership_table_alias, 'group_type');
    }
    parent::query();
  }

  function pre_render(&$values) {
    if (!isset($this->aliases['uid']) || !isset($this->aliases['group_type'])) {
      return;
    }

    $list = array();
    $this->items = array();
    foreach ($values as $value) {
      if ($value->{$this->aliases['uid']} && $value->{$this->field_alias}) {
        $list[] = array(
          'uid' => $value->{$this->aliases['uid']},
          'group_type' => $value->{$this->aliases['group_type']},
          'gid' => $value->{$this->field_alias},
        );
      }
    }

    // Array of all the role IDs we will need to get a role name.
    $all_rids = array();
    if ($list) {
      foreach ($list as $value) {
        $rids = og_get_user_roles($value['group_type'], $value['gid'], $value['uid'], FALSE);
        foreach ($rids as $rid => $role) {
          $this->items[$value['gid']][$value['uid']][$rid]['rid'] = $rid;
          $this->items[$value['gid']][$value['uid']][$rid]['role'] = $role;
        }
      }
    }
  }

  function render_item($rid, $item) {
    return check_plain($item['role']);
  }

  function advanced_render($values) {
    if (isset($this->aliases['uid'])) {
      return parent::advanced_render($values);
    }
  }

  function document_self_tokens(&$tokens) {
    $tokens['[' . $this->options['id'] . '-role' . ']'] = t('The name of the role.');
    $tokens['[' . $this->options['id'] . '-rid' . ']'] = t('The role ID of the role.');
  }

  function add_self_tokens(&$tokens, $item) {
    $tokens['[' . $this->options['id'] . '-role' . ']'] = $item['role'];
    $tokens['[' . $this->options['id'] . '-rid' . ']'] = $item['rid'];
  }
}
