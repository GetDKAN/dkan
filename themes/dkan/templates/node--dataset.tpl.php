<article<?php print $attributes; ?>>
  <?php print $user_picture; ?>
  <?php print render($title_prefix); ?>
  <?php if (!$page && $title): ?>
  <header>
    <h2<?php print $title_attributes; ?>><a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
  </header>
  <?php endif; ?>
  <?php print render($title_suffix); ?>
  <?php if ($display_submitted): ?>
  <footer class="submitted"><?php print $date; ?> -- <?php print $name; ?></footer>
  <?php endif; ?>

  <div<?php print $content_attributes; ?>>
    <?php
      // We hide the comments and links now so that we can render them later.
      hide($content['links']);
      if (module_exists('comment')) {
        hide($content['comments']);
      }
      if (module_exists('disqus')) {
        hide($content['disqus']);
      }
      print render($content);
    ?>
  </div>

  <div class="clearfix">
    <?php if (!empty($content['links'])): ?>
      <nav class="links node-links clearfix"><?php print render($content['links']); ?></nav>
    <?php endif; ?>

    <?php if ($page && (module_exists('comment') || module_exists('disqus'))) : ?>
      <h2> <?php print t('Comments') ?> </h2>
      <?php if (module_exists('disqus')) : ?>
        <?php print render($content['disqus']) ?>
      <?php else : ?>
        <?php print render($content['comments']); ?>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</article>
