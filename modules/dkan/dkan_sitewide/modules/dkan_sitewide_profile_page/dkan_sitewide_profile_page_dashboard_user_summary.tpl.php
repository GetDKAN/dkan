<div class="block-dkan-profile-page-user-summary container">
  <div class="row">
    <div class="col-xs-3 col-sm-6 col-md-6 col-lg-4">
      <?php print $profile_picture; ?>
    </div>
    <div class="col-xs-9 col-sm-6 col-md-6 col-lg-8 dkan-profile-page-user-info">
      <p class="dkan-profile-page-user-name">
        <strong><a href="javascript:void(0);"><?php print $name; ?></a></strong>
      </p>
      <p><?php print $num_dataset; ?> Datasets</p>
      <p><?php print $num_groups; ?> Groups</p>
      <p>User since <?php print $since; ?></p>
    </div>
  </div>
  <div class="row">
    <?php print $menu ?>
  </div>
</div>
