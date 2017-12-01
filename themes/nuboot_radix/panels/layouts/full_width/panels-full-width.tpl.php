<?php

/**
 * @file
 * Template for a 4 row panel layout.
 */
?>
<div class="panel-display panel-full-width clearfix"
  <?php
    if (!empty($css_id)) {
      print "id=\"$css_id\"";
    }
  ?>>

  <?php if($content['top-first'] || $content['top-second']): ?>
    <div class="panel-hero panel-top panel-row" <?php print 'style="background-image:' . $path . ';background-color:' . $bg_color . '"'; ?>>
      <div class="<?php print $tint; ?>"></div>
      <div class="container">
        <div class="panel-col-first">
          <div class="inside"><?php print $content['top-first']; ?></div>
        </div>
        <div class="panel-col-second">
          <div class="inside"><?php print $content['top-second']; ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['middle']): ?>
    <div class="panel-middle panel-row">
      <div class="container">
        <div class="inside"><?php print $content['middle']; ?></div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['bottom-first'] || $content['bottom-second'] || $content['bottom-third']): ?>
    <div class="panel-bottom panel-row">
      <div class="container">
        <div class="panel-col-first">
          <div class="inside"><?php print $content['bottom-first']; ?></div>
        </div>
        <div class="panel-col-second">
          <div class="inside"><?php print $content['bottom-second']; ?></div>
        </div>
        <div class="panel-col-third">
          <div class="inside"><?php print $content['bottom-third']; ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['footer']): ?>
    <div class="panel-bottom-full panel-row">
      <div class="container">
        <div class="inside"><?php print $content['footer']; ?></div>
      </div>
    </div>
  <?php endif; ?>

</div>
