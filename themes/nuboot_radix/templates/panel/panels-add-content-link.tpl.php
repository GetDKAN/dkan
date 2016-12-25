<?php

/**
 * @file
 * Template to control widget links in the add content modal.
 */
?>
<div class="dkan-type-button clearfix">
  <?php
  if (isset($icon_text_button)):
    $button = preg_replace("/<img[^>]+\>/i", " ", $icon_text_button);
    ?>
    <?php print $button; ?>
  <?php else: ?>
    <?php print $image_button; ?>
    <div><?php print $text_button; ?></div>
  <?php endif; ?>
</div>
