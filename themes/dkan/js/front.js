/**
 * @file
 * JS for front page.
 */
(function ($) {
  Drupal.behaviors.dkanFront = {
    attach: function (context) {
      // Fade in 'add dataset' block.
      $('#block-dkan-sitewide-demo-front-dkan-add-front').delay(1500).fadeIn();
      // Remove 'add dataset' block.
      $('#block-dkan-sitewide-demo-front-dkan-add-front a.close').click(function() {
        $('#block-dkan-sitewide-demo-front-dkan-add-front').fadeOut();
      });
    }
  }
})(jQuery);

