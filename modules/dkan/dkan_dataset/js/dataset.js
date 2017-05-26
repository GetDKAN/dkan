(function ($) {

  Drupal.behaviors.dkanDataset = {
    attach: function (context, settings) {
      var height = 150;
      var dif = 30;
      var offset = height - dif;
      var id =  '.field-group-format.group_additional > tbody  > tr';
      dkanDatasetRowHide(id, height, dif, offset);
    }
  }

  // Adds "Show more" to rows longer than height.
  function dkanDatasetRowHide(id, height, dif, offset) {
    $(id).each(function() {
      if ($(this).height() > height) {
        height = height + "px";
        offset  = offset + "px";
        $(this).find('.field').append('<div style="position: absolute;top: ' + offset + ';width: 100%;background-color: #FFF;height: ' + dif + 'px;text-align: center;opacity: .9;" id="click-full"><a href="#">' + Drupal.t("Show more") + '</a></div>');
        $(this).find('.field').eq(0).css({"height": height, "overflow":"hidden", "position":"relative", "padding-bottom" : dif});
      }
      $("#click-full").off().on('click', function(e) {
        if ($(this).hasClass("clicked")) {
          $(this).parent().css({"height": height, "overflow":"hidden"});
          $(this).css({"bottom": "inherit", "top" : offset, "padding" : "0 0 20px 0"});
          $(this).find("a").text(Drupal.t("Show more"));
          $(this).removeClass("clicked");
        }
        else {
          $(this).parent().css({"height": "inherit"});
          $(this).css({"bottom": "0", "top" : "inherit", "padding" : "0 0 20px 0"});
          $(this).find("a").text(Drupal.t("hide"));
          $(this).addClass("clicked");
        }
        return false;
      });
    });
  }

})(jQuery);
