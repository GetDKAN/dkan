
//Autocomplete user defined phrases as they are typed in the editor.
//Requires: none
(function(E, $) {

//tag completer for html & bbcode
BUE.ACTag = function(E, prefix) {
  var cursor = E.posSelection().start, content = E.getContent();
  if (content.substr(cursor - 1, 1) == '/') return;
  var mate = ({'>': '<', ']': '['})[prefix];
  var i = content.substr(0, cursor).lastIndexOf(mate);
  if (i < 0) return;
  var re = new RegExp('^([a-z][a-z0-9]*)[^\\'+ prefix +']*$');
  var match = content.substring(i + 1, cursor).match(re);
  return match ? mate +'/'+ match[1] + prefix : null;
};

//set initial AC pairs
BUE.preprocess.autocomplete = function(E, $) {
  //add tag AC
  E.ACAdd({'<!--': '-->', '<?php': '?>', '>': BUE.ACTag, ']': BUE.ACTag});

  //register keypress
  $(E.textArea).bind('keypress.bue', function(e) {
    var code = e.charCode === undefined ? e.keyCode : e.charCode;
    //disable keycodes that have multi-meaning in opera. 39: hypen-right, 40: parenthesis-down.
    //extend 37:percentage-left, 38:ampersand-up, 33:exclamation-pageup, 34:double quote-pagedown...
    if ($.browser.opera && /^(37|38|39|40)$/.test(code+'')) return;
    var handler, suffix, chr = String.fromCharCode(code), prefix = chr;
    if (!(handler = E.AC[chr])) return;
    if (!handler.lookback) {
      suffix = handler;
    }
    else {
      var pos = E.posSelection(), content = E.getContent();
      for (var lb in handler.lookback) {
        if (content.substring(pos.start - lb.length, pos.start) == lb) {
          prefix = lb + prefix;
          suffix = handler.lookback[lb];
          break;
        }
      }
      if (suffix === undefined && handler.ins) {
        suffix = handler.ins
      }
    }
    if ($.isFunction(suffix)) {
      suffix = suffix(E, prefix);
    }
    if (suffix === false) return false;//prevent default
    typeof suffix == 'string' && E.replaceSelection(suffix, 'start');
  });

};

//Add AC pairs at runtime
E.ACAdd = function(prefix, suffix) {
  var E = this;
  if (typeof prefix == 'object') {
    $.each(prefix, function(a, b){E.ACAdd(a, b)});
    return E;
  }
  E.AC = E.AC || {};
  var len = prefix.length;
  if (len < 2) {
    len && (E.AC[prefix] = suffix);
    return E;
  }
  var trigger = prefix.charAt(len - 1), lookfor = prefix.substr(0, len - 1), options = E.AC[trigger];
  if (typeof options != 'object') {
    options = E.AC[trigger] = {lookback: {}, ins: options || false};
  }
  options.lookback[lookfor] = suffix;
  delete E.AC[prefix];
  return E;
};

//Remove an AC pair at runtime
E.ACRemove = function(prefix) {
  var E = this, len = prefix.length;
  var trigger = prefix.charAt(len-1);
  if (E.AC && E.AC[trigger]) {
    if (typeof E.AC[trigger] == 'object') {
      delete E.AC[trigger].lookback[prefix.substr(0, len-1)];
    }
    else {
      delete E.AC[trigger];
    }
  }
  return E;
};

})(BUE.instance.prototype, jQuery);

//Extend autocomplete list in your own postprocess:
//E.ACAdd('prefix', 'suffix');
//E.ACAdd({prefix1: suffix1, prefix2: suffix2,...});
//E.ACAdd('prefix', function(E, prefix){return suffix;});