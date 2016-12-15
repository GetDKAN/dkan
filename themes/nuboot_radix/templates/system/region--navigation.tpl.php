<?php

/**
 * @file
 * region--navigation.tpl.php
 *
 * Default theme implementation to display the "navigation" region.
 *
 * Available variables:
 * - $content: The content for this region, typically blocks.
 * - $attributes: String of attributes that contain things like classes and ids.
 * - $content_attributes: The attributes used to wrap the content. If empty,
 *   the content will not be wrapped.
 * - $region: The name of the region variable as defined in the theme's .info
 *   file.
 * - $page: The page variables from bootstrap_process_page().
 *
 * Helper variables:
 * - $is_admin: Flags true when the current user is an administrator.
 * - $is_front: Flags true when presented in the front page.
 * - $logged_in: Flags true when the current user is a logged-in member.
 *
 * @see bootstrap_preprocess_region().
 * @see bootstrap_process_page().
 *
 * @ingroup themeable
 */
?>
<div id="nav-wrapper">
  <header<?php print $attributes; ?>>
    <?php if ($content_attributes): ?>
      <div<?php print $content_attributes; ?>>
    <?php endif; ?>
    <div class="navbar-header">
      <?php if ($page['logo']): ?>
        <a class="logo navbar-btn pull-left" href="<?php print url($page['front_page']); ?>" title="<?php print t('Home'); ?>">
          <img src="<?php print $page['logo']; ?>" alt="<?php print t('Home'); ?>" />
        </a>
      <?php endif; ?>
      <?php if ($page['site_name']): ?>
        <a class="name navbar-brand" href="<?php print url($page['front_page']); ?>" title="<?php print t('Home'); ?>"><?php print $page['site_name']; ?></a>
      <?php endif; ?>
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
    <div class="navbar-collapse collapse">
      <nav role="navigation">
        <?php print $content; ?>
      </nav>
    </div>
    <?php if ($content_attributes): ?>
    </div>
    <?php endif; ?>
  </header>
</div>
