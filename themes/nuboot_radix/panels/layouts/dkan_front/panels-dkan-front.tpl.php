<?php

/**
 * @file
 * Template for dkan front page.
 */
?>
<div class="panel-display panel-dkan-front clearfix" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>

  <?php if($content['hero-first'] || $content['hero-second']): ?>
    <div class="panel-hero panel-row" <?php print 'style="background-image:url(' . $path . ');background-color:' . $bg_color . '"'; ?>>
      <div class="<?php print $tint; ?>"></div>
      <div class="container">
        <div class="panel-col-first">
          <div class="inside"><?php print $content['hero-first']; ?></div>
        </div>
        <div class="panel-col-second">
          <div class="inside"><?php print $content['hero-second']; ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['top-full']): ?>
    <div class="panel-top-full panel-row">
      <div class="container">
        <div class="inside"><?php print $content['top-full']; ?></div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['triplet-first'] || $content['triplet-second'] || $content['triplet-third']): ?>
    <div class="panel-triplet panel-row">
      <div class="container">
        <div class="panel-col-first">
          <div class="inside"><?php print $content['triplet-first']; ?></div>
        </div>
        <div class="panel-col-second">
          <div class="inside"><?php print $content['triplet-second']; ?></div>
        </div>
        <div class="panel-col-third">
          <div class="inside"><?php print $content['triplet-third']; ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['bottom-full']): ?>
    <div class="panel-bottom-full panel-row">
      <div class="container">
        <div class="inside"><?php print $content['bottom-full']; ?></div>
      </div>
    </div>
  <?php endif; ?>

  <?php if($content['bottom-first'] || $content['bottom-second']): ?>
    <div class="panel-bottom panel-row">
      <div class="container">
        <div class="panel-col-first">
          <div class="inside"><?php print $content['bottom-first']; ?></div>
        </div>
        <div class="panel-col-second">
          <div class="inside"><?php print $content['bottom-second']; ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>
