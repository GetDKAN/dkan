<?php

// Check editor permissions
$editor = user_role_load_by_name('editor');
  $roles = array($editor->rid => $editor->name);
  $role_permissions = user_role_permissions($roles);
  $editor_permissions = $role_permissions[$editor->rid];
print_r($editor_permissions);
