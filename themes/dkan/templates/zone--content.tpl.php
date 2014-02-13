<?php if ($wrapper): ?><div<?php print $attributes; ?>><?php endif; ?>  
  <div<?php print $content_attributes; ?>>    
    <?php if ($breadcrumb): ?>
      <div id="toolbar" class="grid-<?php print $columns; ?>">
        <div id="breadcrumb"><?php print $breadcrumb; ?></div>
        <div id="actions"><?php print $tabs; ?></div>
      </div>
    <?php endif; ?>
    <?php if ($messages): ?>
      <div id="messages" class="grid-<?php print $columns; ?>"><?php print $messages; ?></div>
    <?php endif; ?>
    <a id="main-content"></a>
    <?php if (!$is_front): ?>
      <?php print $content; ?>
    <?php endif; ?>
  </div>
<?php if ($wrapper): ?></div><?php endif; ?>
