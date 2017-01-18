<?php

/**
 * @file
 * Custom group node template.
 */
?>
<?php if ($teaser): ?>
  <?php
    $node = node_load($nid);
    $datasets = dkan_dataset_groups_datasets($node);
    // Group image.
    $image = field_get_items('node', $node, 'field_image');
    if ($image):
      $group_logo = field_view_value('node', $node, 'field_image', $image[0], array(
        'type' => 'image',
        'settings' => array(
          'image_style' => 'group_medium',
          'image_link' => 'content',
        ),
      ));
    endif;
  ?>
  <article class="node-teaser">
    <div class="field-name-field-image"><?php print render($group_logo); ?></div>
    <h2 class="node-title"><a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
    <div class="content">
      <p>
        <?php $teaser = field_view_field('node', $node, 'body', array(
            'label' => 'hidden',
            'type' => 'text_summary_or_trimmed',
            'settings' => array('trim_length' => 150),
          )); print render($teaser);
        ?>
      </p>
    </div>
    <a href="<?php print urldecode($node_url) ?>" class="btn btn-primary"><?php print count($datasets) ?> datasets</a>
  </article>

<?php else: ?>

  <article id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>
    <?php if ((!$page && !empty($title)) || !empty($title_prefix) || !empty($title_suffix) || $display_submitted): ?>
    <header>
      <?php print render($title_prefix); ?>
      <?php if (!$page && !empty($title)): ?>
      <h2<?php print $title_attributes; ?>><a href="<?php print url($node_url); ?>"><?php print $title; ?></a></h2>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php if ($display_submitted): ?>
      <span class="submitted">
        <?php print $user_picture; ?>
        <?php print $submitted; ?>
      </span>
      <?php endif; ?>
    </header>
    <?php endif; ?>
    <?php
      // Hide comments, tags, and links now so that we can render them later.
      hide($content['comments']);
      hide($content['links']);
      hide($content['field_tags']);
      print render($content);
    ?>
    <?php if (!empty($content['field_tags']) || !empty($content['links'])): ?>
    <footer>
      <?php print render($content['field_tags']); ?>
      <?php print render($content['links']); ?>
    </footer>
    <?php endif; ?>
    <?php print render($content['comments']); ?>
  </article>

<?php endif; ?>
