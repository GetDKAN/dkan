<?php

/**
 * @file
 * Template file for node search results.
 */
?>
<article class="node-search-result row" xmlns="http://www.w3.org/1999/html">
  <div class="col-md-2 col-lg-1 col-xs-2 icon-container">
    <?php
    print drupal_render($result_icon)
    ?>
  </div>
  <div class="col-md-10 col-lg-11 col-xs-10 search-result search-result-<?php print $type ?>">
    <h2 class="node-title"><a href="<?php print url($node_url) ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
    <?php if(!empty($group_list)): ?>
      <div class="group-membership"><?php print $group_list ?></div>
    <?php endif; ?>
    <?php print render($content['field_topic']); ?>
    <ul class="dataset-list"><?php print $dataset_list ?></ul>
    <?php if(!empty($body)): ?>
      <div class="node-description"> <p><?php print $body_summary; ?> </p></div>
    <?php endif; ?>
    <?php print render($content['resources']); ?>
    <?php
      if(isset($node->nid) && $node->type == 'harvest_source' && module_exists('dkan_harvest')) {
        $changed = format_date($node->changed, 'custom', 'm/d/Y');
        $count = dkan_harvest_datasets_count($node) ? dkan_harvest_datasets_count($node) : 0;
        print '<span>' . t('Number of Datasets:') . '</span> ' . $count;
        print '<br><span>' . t('Last updated:') . '</span> ' . $changed;
      }
    ?>
  </div>
</article>
