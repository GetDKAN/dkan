//Introdue find & replace forms
//Requires: bue.popup.js, bue.markup.js
(function(E, $) {

//find a string inside editor content.
E.find = function (str, matchcase, regexp) {
  var E = this, from = E.posSelection().end, content = E.getContent();
  if (from == content.length) from = 0;
  var content = content.substr(from);
  var rgx = new RegExp(regexp ? str : BUE.regesc(str), matchcase ? '' : 'i');
  var index = content.search(rgx);
  if (index == -1) {
    if (from == 0) {
      alert(Drupal.t('No matching phrase found!'));
    }
    else if (confirmEOT()) {
      E.makeSelection(0, 0);
      E.find(str, matchcase, regexp);
    }
  }
  else {
    var strlen = regexp ? content.match(rgx)[0].length : str.length;
    index += from;
    E.makeSelection(index, index+strlen).scrollTo(index);
  }
  return E;
};

//replace str1 with str2.
E.replace = function(str1, str2, matchcase, regexp) {
  var E = this, s = E.getSelection(), rgx = new RegExp('^'+ (regexp ? str1 : BUE.regesc(str1)) +'$', matchcase ? '' : 'i');
  var found = s && s.search(rgx) == 0 || (s = E.find(str1, matchcase, regexp).getSelection()) && s.search(rgx) == 0;
  if (found && confirm(Drupal.t('Replace this occurance of "!text"?', {'!text': s}))) {
    str2 = regexp ? s.replace(new RegExp(str1, 'g' + (matchcase ? '' : 'i')), str2) : str2;
    E.replaceSelection(str2);
  }
  return E;
};

//replace all occurrences of str1 with str2.
E.replaceAll = function(str1, str2, matchcase, regexp) {
  var E = this, P = E.posSelection(), C = E.getContent(), n = 0;
  var R = new RegExp(regexp ? str1 : BUE.regesc(str1), 'g' + (matchcase ? '' : 'i'));
  var F = regexp ?  (function(s) {n++; return s.replace(R, str2)}) : (function() {n++; return str2;});
  var start = P.start == 0 || confirmEOT() ? 0 : P.start;
  E.setContent(C.substr(0, start) + C.substr(start).replace(R, F));
  alert(Drupal.t('Total replacements: !count', {'!count': n}));
  return E;
};

//scroll editor textarea to the specified character index. 
E.scrollTo = function(index) {
  var E = this, T = E.textArea, h = $(T).height();
  var sT = BUE.scrlT = BUE.scrlT || $(document.createElement('textarea')).css({width: $(T).width(), height: 1, visibility: 'hidden'}).appendTo(document.body)[0];
  sT.value = T.value.substr(0, index);
  T.scrollTop = sT.scrollHeight > h ? sT.scrollHeight - Math.ceil(h/2) : 0;
  return E;
};

//open Find & Replace form.
E.frForm = function() {
  var arg = arguments, F = theForm(), el = F.elements;
  var opt = typeof arg[0] == 'object' ? arg[0] : {isrep: arg[0], iscase: arg[1], isreg: arg[2], title: arg[3]};
  BUE.frPop.open(opt.title || (opt.isrep ? Drupal.t('Find & Replace') : Drupal.t('Search')));
  $(el.matchcase.parentNode)[opt.iscase ? 'show' : 'hide']();
  $(el.regexp.parentNode)[opt.isreg ? 'show' : 'hide']();
  $(el.replacetext).parents('div.bue-fr-row').add([el.replacebutton, el.replaceallbutton])[opt.isrep ? 'show' : 'hide']();
  return this;
};

//submit Find & Replace form.
E.frSubmit = function(B) {
  var E = this, el = B.form.elements, findtext = BUE.text(el.findtext.value);
  if (!findtext) {
    el.findtext.focus();
    return E;
  }
  var op = B.name, replacetext = BUE.text(el.replacetext.value);
  var matchcase = $(el.matchcase.parentNode).is(':visible') && el.matchcase.checked;
  var regexp = $(el.regexp.parentNode).is(':visible') && el.regexp.checked;
  switch (op) {
    case 'findbutton': E.find(findtext, matchcase, regexp); break;//find
    case 'replacebutton': E.replace(findtext, replacetext, matchcase, regexp); break;//replace
    case 'replaceallbutton': E.replaceAll(findtext, replacetext, matchcase, regexp); break;//replace all
  }
  return E;
};

//shortcuts
var H = BUE.html, I = BUE.input;

//confirmation message that will be used multiple times.
var confirmEOT = function() {
  return confirm(Drupal.t('End of textarea reached. Continue search at the beginning of textarea?'));
};

//cookie get & set
var K = function (name, value) {
  if (value === undefined) {//get
    return unescape((document.cookie.match(new RegExp('(^|;) *'+ name +'=([^;]*)(;|$)')) || ['', '', ''])[2]);
  }
  document.cookie = name +'='+ escape(value) +'; expires='+ (new Date(new Date()*1 + 30*86400000)).toGMTString() +'; path=/';//set
};

//return find&replace form
var theForm = function () {
  if (BUE.frForm) return BUE.frForm;
  var Dv = function(s, c) {return H('div', s, {style: 'margin-bottom: 4px', 'class': c||'bue-fr-row'})};
  var Ta = function(n) {return Dv(H('textarea', K('bfr_'+ n), {name: n, cols: 36, rows: 1, 'class': 'form-textarea'}), 'form-textarea-wrapper resizable')};
  var Cb = function(n, v) {return H('span', I('checkbox', n, '', {checked: K('bfr_'+ n) || null, 'class': 'form-checkbox'}) + v)};
  var Bt = function(n, v) {return I('button', n, v, {onclick: 'BUE.active.frSubmit(this)', 'class': 'form-submit'})};
  var F = Dv(Ta('findtext')) + Dv(Ta('replacetext'));
  F += Dv(Cb('matchcase', Drupal.t('Match case')) +' '+ Cb('regexp', Drupal.t('Regular expressions')));
  F += Dv(Bt('findbutton', Drupal.t('Find next')) +' '+ Bt('replacebutton', Drupal.t('Replace')) +' '+ Bt('replaceallbutton', Drupal.t('Replace all')));
  BUE.frPop = BUE.createPopup('bue-fr-pop', null, F = BUE.frForm = $(H('form', F))[0]);
  Drupal.behaviors.textarea && Drupal.behaviors.textarea.attach(F);
  $('div.grippie', F).height(4);
  $(window).unload(function() {
    if (!BUE.frForm) return;
    var el = BUE.frForm.elements;
    K('bfr_findtext', el.findtext.value);
    K('bfr_replacetext', el.replacetext.value);
    K('bfr_matchcase', el.matchcase.checked ? 'checked' : '');
    K('bfr_regexp', el.regexp.checked ? 'checked' : '');
  });
  return F;
};

})(BUE.instance.prototype, jQuery);

/*
Example button content to display just the find form:
js: E.frForm();
Example button content to display the whole find and replace form:
js: E.frForm({
  isrep: true, //enable replace
  iscase: true, //enable case sensitive switch
  isreg: true, //enable regular expression switch
  title: 'Replace some text' //custom title. defaults to 'Find & Replace'
});
*/

