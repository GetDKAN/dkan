(function ($) {
  Drupal.behaviors.dataDashboardMenu = {

    attach: function(context){
      $dropwdown = $('.block-dkan-profile-page-user-summary .row > ul.menu > li.expanded > a').on('click', function(e){
        e.preventDefault();
        $childMenu = $(this).next('.profile-nested-menu');
        $('.profile-nested-menu').not($childMenu).hide();
        $childMenu.toggle();
      });

      $nestedMenu = $('.block-dkan-profile-page-user-summary .menu .menu');

      $nestedMenu
      .removeClass('dropdown-menu')
      .addClass('profile-nested-menu');

      $nestedMenu.hide();

    }
  }
})(jQuery);
