<?php

/**
 * @file
 * Blog node.
 *
 * This template assumes that IF there is an image field on the blog node,
 * the machine name is 'field_content_image'.
 * This assumption is based on a client site using the debut_blog module,
 * if you plan to implement the drupal core blog or a custom blog content type,
 * simply create an image field with that machine name, or update the
 * hook_preprocess_node function in the includes/node.inc file
 * to use the image field of your choice.
 *
 * If using the debut_blog module,
 * update the blog view display format to use node teaser rather than fields.
 */
hide($content['comments']);
hide($content['links']);
hide($content['field_content_image']);
global $base_url;
?>

<?php if ($teaser || !$page): ?>

  <?php if($blog_img) : ?>
    <article class="node-teaser">
      <div class="blog-image col-sm-3">
        <a href="<?php print $blog_url ?>">
          <div style="background-image: url('<?php print $blog_img_path ?>')"></div>
        </a>
      </div>
  <?php else : ?>
    <article class="node-teaser no-image">
  <?php endif; ?>

      <div class="content <?php if ($blog_img) { print 'col-sm-9'; } ?>"<?php print $content_attributes ?>>
        <?php print render($title_prefix); ?>
          <h2 class="blog-title"><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
        <?php print render($title_suffix); ?>
        <?php if ($display_submitted) : ?>
          <div class="submitted">
            <?php if(!empty($blog_author)) {
              print ' Posted by <a href="' . $base_url . '/' . $blog_author_url . '">' . $blog_author . '</a> on ' . date('F j, Y', $node->created) . '  <i class="fa fa-clock-o" aria-hidden="true"></i> ' . date('g:ia', $node->created);
            }
            else {
              print ' Posted on ' . date('F j, Y', $node->created) . '  <i class="fa fa-clock-o" aria-hidden="true"></i> ' . date('g:ia', $node->created);
            }
            ?>
          </div>
        <?php endif; ?>

        <?php
          print render($content);
          print render($content['links']);
        ?>

      </div>

    </article>

<?php else: ?>

  <article class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>
    <?php if($blog_img) : ?>
      <div class="content"<?php print $content_attributes; ?>>
        <div class="blog-image">
          <?php print theme('image_style', array('path' => $blog_img, 'style_name' => 'story_image_teaser')); ?>
        </div>
    <?php else : ?>
      <div class="content no-image"<?php print $content_attributes; ?>>
    <?php endif; ?>
      <?php print render($title_prefix); ?>
        <h1 <?php print $title_attributes; ?>><?php print $title; ?></h1>
      <?php print render($title_suffix); ?>
      <?php if ($display_submitted): ?>
        <div class="submitted">
          <?php if (!empty($blog_author)) {
              print ' Posted by <a href="' . $base_url . '/' . $blog_author_url . '">' . $blog_author . '</a> on ' . date('F j, Y', $node->created) . '  <i class="fa fa-clock-o" aria-hidden="true"></i> ' . date('g:ia', $node->created);
          }
          else {
            print ' Posted on ' . date('F j, Y', $node->created) . '  <i class="fa fa-clock-o" aria-hidden="true"></i> ' . date('g:ia', $node->created);
          }
          ?>
        </div>
      <?php endif; ?>

      <?php
        print render($content);
      ?>
    </div>

    <?php print render($content['links']); ?>

    <?php print render($content['comments']); ?>

<?php endif; ?>
