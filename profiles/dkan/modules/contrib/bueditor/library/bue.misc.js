//Miscellaneous methods used in default editor: E.wrapLines(), E.toggleTag(), E.help(), E.tagChooser(), E.tagDialog()
//Requires: bue.popup.js, bue.markup.js
(function(E, $) {

//Wraps selected lines with b1 & b2 and then wrap the result with a1 & a2. Also restores a wrapped selection.
E.wrapLines = function(a1, b1, b2, a2) {
  var E = this, str = E.getSelection().replace(/\r\n|\r/g, '\n'), Esc = BUE.regesc;
  if (!str) {
    return E.tagSelection(a1 + b1, b2 + a2);
  }
  var M, R = new RegExp('^' + Esc(a1 + b1) + '((.|\n)*)' + Esc(b2 + a2) + '$');
  if (M = str.match(R)) {
    R = new RegExp(Esc(b2) + '\n' + Esc(b1), 'g');
    return E.replaceSelection(M[1].replace(R, '\n'));
  }
  return E.replaceSelection(a1 + b1 + str.replace(/\n/g, b2 + '\n' + b1) + b2 + a2);
};

//Tag toggling. Add/remove tag after parsing the selection.
E.toggleTag = function(tag, attributes, cursor) {
  var E = this, obj = {tag: tag, html: E.getSelection(), attributes: attributes};
  return E.insertObj(obj, {cursor: cursor, toggle: true});
};

//Display help text(button title) of each button.
E.help = function(effect) {
  var E = this;
  if (!E.helpHTML) {
    for (var B, rows = [], i = 0; B = E.buttons[i]; i++) {
      rows[i] = [BUE.input(B.type, null, B.value || null, {'class': B.className, src: B.src || null, style: $(B).attr('style')}), B.title];
    }
    E.helpHTML = BUE.table(rows, {id: 'bue-help', 'class': 'bue-'+ E.tplid});
  }
  E.quickPop.open(E.helpHTML, effect);
  return E;
};

//create clickable tag options that insert corresponding tags into the editor.[[tag, title, attributes],[...],...]
E.tagChooser = function(tags, opt) {
  var E = this, opt = $.extend({wrapEach: 'li', wrapAll: 'ul', applyTag: true, effect: 'slideDown'}, opt);
  var wa = BUE.html(opt.wrapAll || 'div', '', {'class': 'tag-chooser'}), $wa = $html(wa);
  var we = BUE.html(opt.wrapEach, '', {'class': 'choice'});
  var lnk = BUE.html('a', '', {href: '#', 'class': 'choice-link'});
  $.each(tags, function(i, inf) {
    var obj = {tag: inf[0], html: inf[1], attributes: inf[2]};
    $html(lnk).html(opt.applyTag ? BUE.objHtml(obj) : obj.html).click(function() {
      E.insertObj($.extend(obj, {html: null}));
      return false;
    }).appendTo($wa)[we ? 'wrap' : 'end'](we);
  });
  E.quickPop.open($wa, opt.effect);
  return E;
};

//open a dialog for a tag to get user input for the given attributes(fields).
E.tagDialog = function(tag, fields, opt) {
  var E = this, sel = E.getSelection(), obj = BUE.parseHtml(sel, tag) || {'attributes': {}};
  for (var field, hidden = '', rows = [], i = 0, n = 0; field = fields[i]; i++, n++) {
    field = fproc(field, obj, sel);
    if (field.type == 'hidden') {
      hidden += fhtml(field);
      n--;
      continue;
    }
    rows[n] = [BUE.html('label', field.title, {'for': field.attributes.id}), fhtml(field)];
    while (field.getnext && (field = fields[++i])) {
      rows[n][1] += fhtml(fproc(field, obj, sel));
    }
  }
  var dopt = $.extend({title: Drupal.t('Tag editor - @tag', {'@tag': tag.toUpperCase()}), stitle: Drupal.t('OK'), validate: false, submit: function(a, b) {return E.tgdSubmit(a, b)}, effect: 'show'}, opt);
  var table = BUE.table(rows, {'class': 'bue-tgd-table'});
  var sbm = BUE.html('div', BUE.input('submit', 'bue_tgd_submit', dopt.stitle, {'class': 'form-submit'}));
  var $form = $html(BUE.html('form', table + sbm + hidden, {name: 'bue_tgd_form', id: 'bue-tgd-form'}));
  E.dialog.open(dopt.title, $form, opt);
  $form.submit(function(){return fsubmit(tag, this, dopt, E)});
  return E;
};

//default submit handler for tag form
E.tgdSubmit = function(tag, form) {
  var E = this, obj = {tag: tag, html: null, attributes: {}};
  for (var name, el, i = 0; el = form.elements[i]; i++) {
    if (el.name.substr(0, 5) == 'attr_') {
      name = el.name.substr(5);
      if (name == 'html') obj.html = el.value;
      else obj.attributes[name] = el.value.replace(/\x22/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') || null;
    }
  }
  return E.insertObj(obj);
};

//helpers
var $html = BUE.$html;

//create field html
var fhtml = function (f) {
  var h = f.prefix || '';
  switch (f.type) {
    case 'select': h += BUE.selectbox(f.fname, f.value, f.options || {}, f.attributes); break;
    case 'textarea': h += BUE.html('textarea', '\n' + f.value, f.attributes); break;
    default: h += BUE.input(f.type, f.fname, f.value, f.attributes); break;
  }
  return h + (f.suffix || '');
};

//process field
var fproc = function(f, obj, sel) {
  f = typeof(f) == 'string' ? {'name': f} : f;
  if (f.name == 'html') {
    f.value =  typeof obj.html == 'string' ? obj.html : (sel || f.value || '');
  }
  f.value = Drupal.checkPlain(typeof obj.attributes[f.name] == 'string' ? obj.attributes[f.name] : (f.value || ''));
  f.title  = typeof f.title == 'string' ? f.title : f.name.substr(0, 1).toUpperCase() + f.name.substr(1);
  f.fname = 'attr_' + f.name;
  f.type = f.value.indexOf('\n') > -1 ? 'textarea' : (f.type || 'text');
  f.attributes = $.extend({name: f.fname, id: f.fname, 'class': ''}, f.attributes);
  f.attributes['class'] += ' form-' + f.type;
  if (f.required) {
    f.attributes['class'] += ' required';
    f.attributes['title'] = Drupal.t('This field is required.');
  }
  return f;
};

//tag dialog form submit
var fsubmit = function(tag, form, opt, E) {
  //check required fields.
  for (var el, i = 0; el = form.elements[i]; i++) if ($(el).is('.required') && !el.value) {
    return BUE.noticeRequired(el);
  }
  //custom validate
  var V = opt.validate;
  if (V) try {if (!V(tag, form, opt, E)) return false} catch(e) {alert(e.name +': '+ e.message)};
  E.dialog.close();
  //custom submit
  var S = opt.submit;
  S = typeof S == 'string' ? window[S] : S;
  if (S) try {S(tag, form, opt, E)} catch(e) {alert(e.name +': '+ e.message)};
  return false;
};

//Notice about the required field. Useful in form validation.
BUE.noticeRequired = function(field) {
  $(field).fadeOut('fast').fadeIn('fast', function(){$(this).focus()});
  return false;
};

})(BUE.instance.prototype, jQuery);

//backward compatibility.
eDefSelProcessLines = eDefTagLines = function (a, b, c, d) {BUE.active.wrapLines(a, b, c, d)};
eDefTagger = function(a, b, c) {BUE.active.toggleTag(a, b, c)};
eDefHelp = function(fx) {BUE.active.help(fx)};
eDefTagDialog = function(a, b, c, d, e, f) {BUE.active.tagDialog(a, b, {title: c, stitle: d, submit: e, effect: f})};
eDefTagInsert = function(a, b) {BUE.active.tgdSubmit(a, b)};
eDefTagChooser = function(a, b, c, d, e) {BUE.active.tagChooser(a, {applyTag: b, wrapEach: c, wrapAll: d, effect: e})};