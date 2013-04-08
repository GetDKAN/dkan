
//IMCE integration. Introduces E.imce=BUE.imce
//Requires: bue.popup.js
(function(E, $) {

//create IMCE object shared by all editor instances.
var I = E.imce = BUE.imce = {};
//set IMCE URL on document load
$(function() {I.url = Drupal.settings.BUE.imceURL || ''});

//IMCE button html to be used in forms. Target field's name is required.
I.button = function(fname, text) {
  return I.url ? '<input type="button" id="bue-imce-button" name="bue_imce_button" class="form-submit" value="'+ (text || Drupal.t('Browse')) +'" onclick="BUE.imce.open(this.form.elements[\''+ fname +'\'])">' : '';
};

//open IMCE with user specified options.
I.open = function(opt) {
  //require URL set.
  if (!I.url) {
    return;
  }
  //reset previous parameters.
  I.ready = I.sendto = function(){}, I.target = null;
  //copy new parameters.
  $.extend(I, opt.focus ? {target: opt, ready: I.readyDefault, sendto: I.sendtoDefault} : opt);
  //Show popup and execute ready method if IMCE was loaded before.
  if (I.pop) {
    I.setPos();
    I.ready(I.win, I.pop);
  }
  //Load IMCE once and for all. Run window.bueImceLoad which then runs the ready method.
  else {
    var url = I.url + (I.url.indexOf('?') < 0 ? '?' : '&') + 'app=bue|imceload@bueImceLoad|';
    I.pop = BUE.createPopup('bue-imce-pop', Drupal.t('File Browser'), '<iframe src="'+ url +'" frameborder="0"></iframe>');
    I.setPos();
  }
};

//centre the IMCE popup inside the parent window
I.setPos = function() {
  var $p = $(I.pop), $win = $(window), winH = $.browser.opera ? window.innerHeight : $win.height();
  I.pop.open(null, null, {offset: {
    left: Math.max(0, ($win.width() - $p.width())/2),
    top: $win.scrollTop() + Math.max(0, (winH - $p.height())/2)
  }});
};

//Static sendto operation which executes dynamically set I.sendto()
I.finish = function(file, win) {
  I.sendto(file, win, I.pop);
};

//Predefined sendto operation. Process the sent file & close IMCE
I.sendtoDefault = function(file, win, pop) {
  var target = I.target, el = target.form.elements, val = {'alt': file.name, 'width': file.width, 'height': file.height};
  target.value = file.url;
  for (var i in val) {
    if (el['attr_'+i]) el['attr_'+i].value = val[i];
  }
  pop.close();
  target.focus();
};

//Predefined ready method. Highlight target url and add ESC(close) shortcut to file list.
I.readyDefault = function(win, pop) {
  var imce = win.imce, path = I.target && I.target.value;
  //highlight the target path in imce file list
  path && imce.highlight(path.substr(path.lastIndexOf('/')+1));
  //add ESC(close) shortcut for the list and focus on it initially.
  if (imce.fileKeys && !imce.fileKeys.k27) {
    imce.fileKeys.k27 = function(e) {
      pop.closenfocus();
      I.target && I.target.focus();
    };
  }
  !$.browser.opera && !$.browser.safari && $(imce.FLW).focus();
};

//IMCE onload function. Runs after first load of IMCE.
window.bueImceLoad = function(win) {
  (I.win = win).imce.setSendTo(Drupal.t('Send to editor'), I.finish);
  I.ready(win, I.pop);
  // Fix opera and webkit focus scrolling.
  if (($.browser.opera || $.browser.safari) && $(I.pop).is(':visible')) {
    $(I.win.imce.FLW).one('focus', function() {I.pop.close(); I.setPos();});
  }
};

})(BUE.instance.prototype, jQuery);

//backward compatibility
eDefBrowseButton = function(l, f, t) {return BUE.imce.button(f, t)};
