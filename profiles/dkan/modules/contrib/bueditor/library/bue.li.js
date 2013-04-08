
//Automatically insert a new list item when enter-key is pressed at the end of a list item.
//Requires: none
BUE.preprocess.li = function(E, $) {

  $(E.textArea).bind('keyup.bue', function(e) {
    if (!e.ctrlKey && !e.shiftKey && !e.originalEvent.altKey && e.keyCode == 13) {
      var prefix = E.getContent().substr(0, E.posSelection().start);
      /<\/li>\s*$/.test(prefix) && E.tagSelection('<li>', '</li>');
    }
  });
 
};