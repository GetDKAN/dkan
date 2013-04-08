
//Html creating and parsing methods.
//Requires: none
(function(E, $) {

//html for a given tag. attributes having value=null are not printed.
BUE.html = function(tag, ihtml, attr) {
  var A = attr || {}, I = ihtml || '';
  var H = '<'+ tag;
  for (var i in A) {
    H += A[i] == null ? '' : ' '+ i +'="'+ A[i] +'"';
  }
  H += Nc(tag) ? (' />'+ I) : ('>'+ I +'</'+ tag +'>');
  return tag ? H : I;
};

//html for a given object.
BUE.objHtml = function(obj) {
  return obj && obj.tag ? Html(obj.tag, obj.html, obj.attributes) : '';
};

//form input html.
BUE.input = function(t, n, v, a) {
  return Html('input', '', $.extend({'type': t, 'name': n, 'value': v||null}, a));
};

//selectbox html. opt has property:value pairs.
BUE.selectbox = function(n, v, opt, attr) {
  var opt = opt||{}, H = '';
  for (var i in opt) {
    H += Html('option', opt[i], {'value': i, 'selected': i == v ? 'selected' : null});
  }
  return Html('select', H, $.extend({}, attr, {'name': n}));
};

//table html
BUE.table = function(rows, attr) {
  for (var R, H = '', i = 0; R = rows[i]; i++) {
    H += R['data'] === undefined ? BUE.trow(R) : BUE.trow(R['data'], R['attr']);
  }
  return Html('table', H, attr);
};
BUE.trow = function(cells, attr) {
  for (var C, H = '', i = 0; C = cells[i]; i++) {
    H += C['data'] === undefined ? Html('td', C) : Html('td', C['data'], C['attr']);
  }
  return Html('tr', H, attr);
};

//Escape regular expression specific characters in a string
BUE.regesc = function (s) {
  return s.replace(/([\\\^\$\*\+\?\.\(\)\[\]\{\}\|\:])/g, '\\$1');
};

//Check if a string is a non closing html tag.
BUE.nctag = function (s) {
  return !s || /^(img|input|hr|br|embed|param)$/.test(s);
};

//Parse the string as html. If match an html element return properties, otherwise return null.
BUE.parseHtml = function(s, tag) {
  var r = new RegExp('^<('+ (tag || '[a-z][a-z0-9]*') +')([^>]*)>($|((?:.|[\r\n])*)</\\1>$)');
  if (!(match = s.match(r)) || (!match[3] && !Nc(match[1]))) {
    return null;
  }
  var tag = match[1], arr = [], attr = {}, match;
  if ((arr = match[2].split('"')).length > 1) {
    for (var i = 0; arr[i+1] !== undefined; i += 2) {
      attr[arr[i].replace(/\s|\=/g, '')] = arr[i+1];
    }
  }
  return {tag: tag, attributes: attr, html: match[4]};
};

//Insert a parsed object into textarea by extending/replacing/tagging the current selection.
E.insertObj = function(obj, opt) {
  if (!obj || !obj.tag) {
    return this;
  }
  var E = this, tag = obj.tag, opt = $.extend({cursor: null, extend: true, toggle: false}, opt);
  var sametag, sel = E.getSelection(), selobj = sel && opt.extend && BUE.parseHtml(sel);
  //selection and new obj are of the same type
  if (sametag = selobj && selobj.tag == tag) {
    //toggle selected tag and exit
    if (opt.toggle) return E.replaceSelection(selobj.html, opt.cursor);
    //create a new object to combine properties of selection and the new obj.
    var obj = {
      tag: tag,
      html: typeof obj.html != 'string' || obj.html == sel ? selobj.html : obj.html,
      attributes: $.extend(selobj.attributes, obj.attributes)
    };
  }
  //replace selection
  if (sametag || Nc(tag) || obj.html) {
    return E.replaceSelection(BUE.objHtml(obj), opt.cursor);
  }
  //tag selection
  var html = Html(tag, '', obj.attributes);
  return E.tagSelection(html.substr(0, html.length - tag.length - 3), '</'+ tag +'>', opt.cursor);
};

//shortcuts
var Html = BUE.html;
var Nc = BUE.nctag;

})(BUE.instance.prototype, jQuery);

//backward compatibility.
eDefHTML = BUE.html;
eDefInput = BUE.input;
eDefSelectBox = BUE.selectbox;
eDefTable = BUE.table;
eDefRow = BUE.trow;
eDefNoEnd = BUE.nctag;
eDefRegEsc = BUE.regesc;
eDefParseTag = BUE.parseHtml;
eDefInputText = function(n, v, s) {return BUE.input('text', n, v, {'size': s||null})};
eDefInputSubmit = function(n, v) {return BUE.input('submit', n, v)};