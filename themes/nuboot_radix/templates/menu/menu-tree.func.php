<?php

/**
 * @file
 * menu-tree.func.php
 */

/**
 * Nuboot Radix theme wrapper function for the primary menu links.
 */
function nuboot_radix_menu_tree__primary(&$variables) {
  return '<ul class="menu nav navbar-nav">' . $variables['tree'] . '</ul>';
  // Code after RETURN statement cannot be executed.
  // Add views exposed search.
  // $block = block_load('dkan_sitewide', 'dkan_sitewide_search_bar');
  // if ($block) :
  //   $search = _block_get_renderable_array(_block_render_blocks(array($block)));
  //   print render($search);
  // endif;
  // End views exposed search.
}
