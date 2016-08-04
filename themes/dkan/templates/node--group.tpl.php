<?php
/**
 * @file
 * Custom group node template.
 */
?>
<?php if ($teaser): ?>
  <?php
    $node = node_load($nid);
    // Group image.
    $image = field_get_items('node', $node, 'field_image');
    if($image):
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
    <h2 class="node-title"><a href="<?php print url($node_url) ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
    <div class="content">
      <p>
        <?php $field = field_view_field('node', $node, 'body', array(
            'label' => 'hidden',
            'type' => 'text_summary_or_trimmed',
            'settings' => array('trim_length' => 150),
          ));
          print render($field);
        ?>
      </p>
    </div>
    <div class="button"><?php print render($content["og_count_entity_view_1"]); ?></div> 
  </article>

<?php else: ?>

  <article<?php print $attributes; ?>>
    <?php print $user_picture; ?>
    <?php print render($title_prefix); ?>
    <?php if (!$page && $title): ?>
    <header>
      <h2<?php print $title_attributes; ?>><a href="<?php print url($node_url) ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
    </header>
    <?php endif; ?>
    <?php print render($title_suffix); ?>
    <?php if ($display_submitted): ?>
    <footer class="submitted"><?php print $date; ?> -- <?php print $name; ?></footer>
    <?php endif; ?>  
    
    <div<?php print $content_attributes; ?>>
      <?php
        // We hide the comments and links now so that we can render them later.
        hide($content['comments']);
        hide($content['links']);
        print render($content);
      ?>
    </div>
    
    <div class="clearfix">
      <?php if (!empty($content['links'])): ?>
        <nav class="links node-links clearfix"><?php print render($content['links']); ?></nav>
      <?php endif; ?>

      <?php print render($content['comments']); ?>
    </div>
  </article>

<?php endif; ?>
