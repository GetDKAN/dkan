<?php

/**
 * @file
 * Generates CSS sprites for editor buttons.
 */

/**
 * Create and return editor sprite depending on editor settings or buttons.
 */
function bueditor_sprite($editor, $buttons = array()) {
  if (!empty($buttons)) {
    uasort($buttons, create_function('$b1,$b2', 'return $b1->weight - $b2->weight;'));
    return bueditor_sprite_buttons($buttons, $editor->iconpath);
  }
  elseif (isset($editor->eid) && $editor->eid > 0) {
    return bueditor_sprite_editor($editor);
  }
  return '';
}

/**
 * Create an icon sprite for an editor.
 */
function bueditor_sprite_editor($editor) {
  return bueditor_sprite_buttons(bueditor_buttons($editor->eid), $editor->iconpath);
}

/**
 * Create an icon sprite for a set of buttons.
 */
function bueditor_sprite_buttons($buttons, $iconpath) {
  $icons = array();
  $iconpath = bueditor_path_tr($iconpath);
  foreach ($buttons as $button) {
    if (substr($button->title, 0, 4) != 'tpl:' && preg_match('/\.(png|gif|jpg)$/', $button->icon)) {
      $icons[] = $iconpath . '/' . $button->icon;
    }
  }
  return bueditor_sprite_icons($icons);
}

/**
 * Create an icon sprite for a set of icons.
 */
function bueditor_sprite_icons($icons) {
  if (image_get_toolkit() != 'gd' || empty($icons) || ($total = count($icons)) < 2) {
    return '';
  }
  //Image properties of the first one in the list will be the reference for the sprite
  $first_icon = $icons[0];
  $sprite = new stdClass();
  $sprite->dir = bueditor_sprites_dir();
  $sizes = @array_map('filesize', $icons);
  $sprite->name = 'sprite_' . md5(implode(' ', array_merge($icons, $sizes))) . substr($first_icon, strrpos($first_icon, '.'));
  $sprite->source = $sprite->dir . '/' . $sprite->name;
  if (file_exists($sprite->source)) {
    return $sprite->name;
  }
  if (!file_prepare_directory($sprite->dir, 1) || !($icon = image_load($first_icon, 'gd'))) {
    return '';
  }
  //set fixed icon dimensions
  $icon_width = $icon->info['width'];
  $icon_height = $icon->info['height'];
  //set sprite dimensions based on the number of icons
  $sprite->info = $icon->info;
  $sprite->toolkit = $icon->toolkit;
  $sprite->info['width'] = $icon_width * $total;
  $sprite->resource = image_gd_create_tmp($icon, $sprite->info['width'], $sprite->info['height']);
  //copy first icon
  imagecopy($sprite->resource, $icon->resource, 0, 0, 0, 0, $icon_width, $icon_height);
  imagedestroy($icon->resource);
  //copy the rest
  for ($i = 1; $i < $total; $i++) {
    if ($icon = image_load($icons[$i], 'gd')) {
      imagecopy($sprite->resource, $icon->resource, $i * $icon_width, 0, 0, 0, $icon_width, $icon_height);
      imagedestroy($icon->resource);
    }
  }
  //save sprite to file
  $result = image_save($sprite);
  imagedestroy($sprite->resource);
  return $result ? $sprite->name : '';
}
