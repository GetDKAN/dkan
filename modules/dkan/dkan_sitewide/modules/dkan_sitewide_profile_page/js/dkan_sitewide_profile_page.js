(function ($) {
  Drupal.behaviors.dataDashboardMenu = {
    attach: function(context){
      $dropwdown = $('.dropdown-toggle').on('click', function(e){
        $childMenu = $(this).next('.profile-nested-menu');
        $('.profile-nested-menu').not($childMenu).hide();
        $childMenu.toggle();
      });

      $nestedMenu = $('.block-dkan-profile-page-user-summary .dropdown-menu');

      $nestedMenu
      .removeClass('dropdown-menu')
      .addClass('profile-nested-menu');

      $nestedMenu.hide();

    }
  }
})(jQuery);