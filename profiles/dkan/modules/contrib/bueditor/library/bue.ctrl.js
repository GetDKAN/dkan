//Register button accesskeys as Ctrl shortcuts.
//Requires: none
BUE.preprocess.ctrl = function(E, $) {

  //store key-button relations.
  E.ctrlKeys = {};

  //get button keys
  $.each(E.buttons, function(i, B) {
    var pos, key;
    if (key = E.tpl.buttons[B.bid][3]) {//B.accessKey is not reliable since it is dynamically set on/off
      E.ctrlKeys[key.toUpperCase().charCodeAt(0)] = B;
      pos = B.title.lastIndexOf(' (');
      B.title = (pos < 0 ? B.title : B.title.substr(0, pos)) +' (Ctrl + '+ key +')';
    }
  });

  //register ctrl shortcuts for the editor.
  $(E.textArea).bind('keydown.bue', function(e) {
    if (e.ctrlKey && !e.shiftKey && !e.originalEvent.altKey && E.ctrlKeys[e.keyCode]) {
      E.ctrlKeys[e.keyCode].click();
      return false;
    }
  });

};


//Extend or alter shortcuts in your own postprocess:
//E.ctrlKeys['YOUR_KEY_CODE'] = {click: YOUR_CALLBACK};
//Do not use F, O, and P as shortcut keys in IE and Safari as they will always fire their default action.