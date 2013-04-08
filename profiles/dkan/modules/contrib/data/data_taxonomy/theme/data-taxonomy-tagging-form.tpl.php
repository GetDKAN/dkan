<?php
?>
<div class='data-taxonomy-tagging-form'>
  <label><?php print $label ?></label>
  <div class='data-taxonomy-tags clear-block'>
    <?php print $edit ?>
    <div id='data-taxonomy-tags-<?php print $form['vid']['#value'] ?>-<?php print $form['id']['#value'] ?>'><?php print $tags ?></div>
  </div>
  <div class='data-taxonomy-form clear-block'>
    <?php print drupal_render($form); ?>
  </div>
</div>
