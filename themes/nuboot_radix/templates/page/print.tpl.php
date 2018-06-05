<?php

/**
 * @file
 * Default printer-friendly version.
 */
?>

<div class="container">
  <div class="branding">
    <?php if ($logo): ?>
      <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" />
    <?php endif; ?>
    <?php if ($site_name): ?>
        <?php print $site_name; ?>
    <?php endif; ?>
    <?php if (!empty($site_slogan)): ?>
      <div class="site-slogan"><?php print $site_slogan; ?></div>
    <?php endif; ?>
  </div>

  <h1 class="print-title"><?php print $title; ?></h1>
  <div class="print-content"><?php print render($content); ?></div>

  <footer id="footer" class="footer">
    <?php if ($copyright): ?>
      <small class="copyright pull-left"><?php print $copyright; ?></small>
    <?php endif; ?>
    <small class="pull-right"><?php print render($page['footer']); ?></small>
  </footer>
</div>

