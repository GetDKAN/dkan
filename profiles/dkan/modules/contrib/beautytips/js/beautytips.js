
/**
 * Defines the default beautytip and adds them to the content on the page
 */
(function ($) {
  Drupal.behaviors.beautytips = {
    attach: function(context, settings) {
      // Fix for drupal attach behaviors in case the plugin is not attached.
      if (typeof(jQuery.bt) == 'undefined' && jQuery.bt == null){
        return;
      }
      jQuery.bt.options.closeWhenOthersOpen = true;
      var beautytips = Drupal.settings.beautytips;
      var styles = Drupal.settings.beautytipStyles;

      // Add the tooltips to the page
      for (var key in beautytips) {
        // Build array of options that were passed to beautytips_add_beautyips
        var btOptions = styles[beautytips[key]['style']];
        if (beautytips[key]['list']) {
          for ( var k = 0; k < beautytips[key]['list'].length; k++) {
            btOptions[beautytips[key]['list'][k]] = beautytips[key][beautytips[key]['list'][k]];
          }
        }

        if (beautytips[key]['cssSelect']) {
          if (beautytips[key]['animate']) {
            btOptions = beautytipsAddAnimations(beautytips[key]['animate'], btOptions);
          }
          // Run any java script that needs to be run when the page loads
          if (beautytips[key]['contentSelector'] && beautytips[key]['preEval']) {
            $(beautytips[key]['cssSelect']).each(function() {
              if (!beautytipsProcessed(this, false)) {
                eval(beautytips[key]['contentSelector']);
              }
            });
          }
          // Run this if the content is straight text being passed.
          if (beautytips[key]['text']) {
            $(beautytips[key]['cssSelect']).each(function() {
              if (!beautytipsProcessed(this)) {
                if (beautytipsOffsetParentEval(beautytips[key], btOptions)) {
                  btOptions['offsetParent'] = eval(btOptions['offsetParent']);
                }
                $(this).bt(beautytips[key]['text'], btOptions);
              }
            });
          }
          // Run this if we are grabbing ajax loaded content.
          else if (beautytips[key]['ajaxPath']) {
            $(beautytips[key]['cssSelect']).each(function() {
              if (!beautytipsProcessed(this)) {
                if (beautytips[key]['ajaxDisableLink']) {
                  $(this).click(function(event) {
                    event.preventDefault();
                  });
                }
                if (beautytipsOffsetParentEval(beautytips[key], btOptions)) {
                  btOptions['offsetParent'] = eval(btOptions['offsetParent']);
                }
                $(this).bt(btOptions);
              }
            });
          }
          // Run this if we are grabbing content from some place on the page.
          else {
            $(beautytips[key]['cssSelect']).each(function() {
              if (!beautytipsProcessed(this)) {
                if (beautytipsOffsetParentEval(beautytips[key], btOptions)) {
                  btOptions['offsetParent'] = eval(btOptions['offsetParent']);
                }
                $(this).bt(btOptions);
              }
            });
          }
        }
        // Clear out the options for the next tip.
        btOptions.length = 0;
      }
    }
  }

  /**
   * Determine if an element has already been processed.
   */
  function beautytipsProcessed(element, addClass) {
    if ($(element).hasClass('beautytips-module-processed')) {
      return true;
    }
    if (addClass != false) {
      $(element).addClass('beautytips-module-processed');
    }
    return false;
  }

  /**
   * Determine if the offsetParent needs to be found.
   *  doing this allows us to choose adifferent offset parent per
   *  individual beautytip, or to choose and to choose an offset
   *  based on its relationship to the element.
   */
  function beautytipsOffsetParentEval(beautytip, btOptions) {
    return (beautytip['offsetParentEval'] === "always" || (beautytip['offsetParentEval'] === "once" && jQuery.type(btOptions['offsetParent']) === "string"));
  }


  function beautytipsAddAnimations(animations, btOptions) {
    switch (animations['on']) {
      case 'none':
        break;
      case 'fadeIn':
        btOptions['showTip'] = function(box) {
          $(box).fadeIn(500);
        };
        break;
      case 'slideIn':
        break;
    }
    switch (animations['off']) {
      case 'none':
        break;
      case 'fadeOut':
        btOptions['hideTip'] = function(box, callback) { 
          $(box).animate({opacity: 0}, 500, callback);
        };
        break;
      case 'slideOut':
        btOptions['hideTip'] = function(box, callback) {
          var width = $("body").width();
          $(box).animate({"left": "+=" + width + "px"}, "slow", callback);
        }
        break;
    }
    return btOptions;
  }
})(jQuery);
