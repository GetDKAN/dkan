<?php
/**
 * @file og-extras-group-links.tpl.php
 * OG Extras group links template
 *
 * Variables available:
 * - $gid: group id.
 * - $group_type: group type.
 * - $group_node: group node.
 * - $group_node_links: formatted links to create group content.
 *
 * @ingroup views_templates
 */
?>
<?php if (!empty($gid) && !empty($group_node_links)): ?>
  <?php print $group_node_links; ?>
<?php endif; ?>