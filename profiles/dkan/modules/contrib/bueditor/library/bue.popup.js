//Introduces editor popups: E.dialog & E.quickPop
//Requires: none
(function(E, $) {

BUE.popups = BUE.popups || {};

//default template for editor popups or dialogs. Use table wrapper against various positioning bugs in IE.
BUE.popHtml = '<table class="bue-popup" style="display: none;" role="dialog"><tbody class="bue-zero"><tr class="bue-zero"><td class="bue-zero"><div class="bue-popup-head clearfix"><div class="bue-popup-title"></div><button class="bue-popup-close" type="button">x</button></div><div class="bue-popup-body"><div class="bue-popup-content clearfix"></div></div></td></tr></tbody></table>';

//open popup.
BUE.openPopup = function (id, title, content, opt) {
  return BUE.createPopup(id).open(title, content, opt);
};

//create popup
BUE.createPopup = function (id, title, content) {
  if (BUE.popups[id]) {
    return BUE.popups[id];
  }
  var $P = BUE.$html(BUE.popHtml).appendTo('body').attr('id', id);
  var $title = $P.find('.bue-popup-title').html(title || '');
  var $content = $P.find('.bue-popup-content').html(content || '');
  var P = BUE.popups[id] = $P[0];
  //open
  P.open = function (title, content, opt) {
    if (title !== undefined && title !== null) {
      $title.html(title);
    }
    if (content !== undefined && content !== null) {
      $content.html(content);
    }
    var E = P.bue = BUE.active, B = E.buttons[E.bindex||0];
    opt = typeof opt == 'string' ? {effect: opt} : opt;
    opt = $.extend({effect: 'show', speed: 'normal', callback: P.onopen}, opt);
    opt.onopen = opt.onopen || opt.callback;
    //calculate popup offset
    if (!opt.offset && B) {
      var pos = $(B).offset(), w = $P.width(), left = Math.max(15, pos.left - w/2 + 15);
      opt.offset = {
        left: left - Math.max(0, left + w - $(window).width() + 15),
        top: pos.top + 15
      };
      B.pops = true;
    }
    $P.css(opt.offset);
    //display popup
    if (opt.effect == 'show') {
      $P.show();
      opt.onopen && opt.onopen.call(P);
    }
    else {
      $P[opt.effect](opt.speed, opt.onopen);
    }
    P.onclose = opt.onclose || false;
    return P;
  };
  //close
  P.close = function(effect) {
    $P.stop(true, true)[effect || 'hide']();
    P.onclose && P.onclose.call(P);
    return P;
  };
  //close the pop, focus on the editor
  P.closenfocus = function() {
    P.close().bue.focus();
    return P;
  };
  //focus on the first link or form input if any exists in the pop.
  P.onopen = function() {
    if ($P.css('display') != 'none') {
      var $form = $P.focus().find('form');
      if ($form.size()) {
        $($form[0].elements[0]).focus();
      }
      else {
        $P.find('a:first').focus();
      }
    }
    return P;
  }
  //add tabindex. make focusable
  $P.attr('tabindex', 0);
  //close-button
  $P.find('.bue-popup-close').click(P.closenfocus)[0].title = Drupal.t('Close');
  //close on ESC
  $P.keydown(function(e) {
    if (e.keyCode == 27) {
      P.closenfocus();
      return false;
    }
  });
  //make draggable
  $P.find('.bue-popup-head').mousedown(function (e) {
    var pos = {X: parseInt($P.css('left')) - e.pageX, Y: parseInt($P.css('top')) - e.pageY};
    var drag =  function(e) {$P.css({left: pos.X + e.pageX, top: pos.Y + e.pageY});return false;};
    var undrag = function(e) {$(document).unbind('mousemove', drag).unbind('mouseup', undrag)};
    $(document).mousemove(drag).mouseup(undrag);
    return false;
  });
  return P;
};

//initialize editor dialog & quickPop.
BUE.preprocess = $.extend({popup: function(Ed, $) {
  //run once
  if (Ed.index) return;
  //ceate the dialog.
  var D = E.dialog = BUE.dialog = BUE.createPopup('bue-dialog');
  var foc  = function() {this.blur()};
  var Do = D.open, Dc = D.close;
  //open
  D.open = function(title, content, opt) {
    D.esp && D.close();
    var E = BUE.active;
    E.buttonsDisabled(true).stayClicked(true);
    D.esp = E.posSelection();
    $(E.textArea).bind('focus.bue', foc);
    return Do(title, content, opt);
  };
  //close
  D.close = function(effect) {
    if (!D.esp) return D;
    var E = D.bue;
    $(E.textArea).unbind('focus.bue', foc);
    E.buttonsDisabled(false).stayClicked(false);
    E == BUE.active && E.makeSelection(D.esp.start, D.esp.end);
    D.esp = null;
    return Dc(effect);
  };

  //Create quick pop
  var Q = E.quickPop = BUE.quickPop = BUE.createPopup('bue-quick-pop');
  var Qo = Q.open, Qc = Q.close, $Q = $(Q);
  //open
  Q.open = function(content, opt) {
    $(document).mouseup(Q.close);
    return Qo(null, content, opt);
  };
  //close
  Q.close = function() {
    $(document).unbind('mouseup', Q.close);
    return Qc();
  };
  //navigate(UP-DOWN) & trigger(ENTER) links
  $Q.keydown(function (e) {
    switch (e.keyCode) {
      case 13:
        setTimeout(Q.closenfocus);//settimeout to allow click event trigger.
        break;
      case 38:case 40:
        var $a = $Q.find('a'), i = $a.index(document.activeElement);
        $a.eq(i + e.keyCode - 39).focus();
        return false;
    }
  });
  //no title in quick-pop
  $Q.find('.bue-popup-head').css({display: 'none'});//hide() is too slow.
}}, BUE.preprocess);

})(BUE.instance.prototype, jQuery);