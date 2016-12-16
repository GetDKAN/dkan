<?php

/**
 * @file
 * region--preface-first.tpl.php
 */
?>
<?php if ($content): ?>
  <div<?php print $attributes; ?>>
    <?php if ($content_attributes): ?>
      <div<?php print $content_attributes; ?>>
    <?php endif; ?>
    <?php print $content; ?>
    <?php if ($content_attributes): ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>
