//Introduces cross-browser editor history with two new methods. E.undo() & E.redo()
//Requires: none
(function(E, $) {

//history object
BUE.history = function(E) {
  var H = this;
  H.bue = E;
  H.max= 50; //maximum number of states in undo/redo history
  //the key codes(not char codes) triggering state saving. (backspace, enter, space, del, V, X, comma, dot)
  H.keys= {'8': 1, '13': 1, '32': 1, '46': 1, '86': 1, '88': 1, '188': 1, '190': 0};
  H.period= 500; //minimum time needed to pass before saving successively triggered states.
  H.states= []; //stores the states
  H.current= -1; //index of the latest activated/stored state
  H.writable= true; //dynamic allowance of state saving.

  //attach textarea events triggering history operations.
  $(E.textArea).one('focus.bue', function(){H.save()}).bind('keyup.bue', function(e) {
    H.writable && (!H.keys || H.keys[e.keyCode]) && H.save();
  });

  //save history on setContent.
  E.historySetContent = E.setContent;
  E.setContent = function() {
    this.history.save();
    return this.historySetContent.apply(this, arguments);
  };
};

//history methods
var H = BUE.history.prototype;

//allow/disallow write permission
H.allow = function(){this.writable = true};
H.disallow = function(){this.writable = false};

//save textarea state.
H.save = function(bypass) {
  var H = this, E = H.bue;
  //chek write perm
  if (!bypass && !H.writable) {
    return;
  }
  H.disallow();
  setTimeout(function(){H.allow()}, H.period);
  //delete redo-states if any.
  while(H.states[H.current + 1]) {
    H.states.pop();
  }
  var val = E.getContent(), len = H.states.length;
  if (len && val == H.states[len-1].value) {
    return;
  }
  if (len == H.max) {
    H.states.shift();
    len--;
  }
  H.states[(H.current = len)] = {value: val, cursor: E.posSelection(), scrollTop: E.textArea.scrollTop};
};

//restore a state relative to the current state.
H.go = function(i) {
  var H = this, E = H.bue;
  i < 0 && H.current == H.states.length - 1 && H.save(true);
  var state, index = H.current + i;
  if (state = H.states[index]) {
    H.disallow();//prevent setContent save state.
    E.setContent(state.value);
    H.allow();
    E.makeSelection(state.cursor.start, state.cursor.end);
    E.textArea.scrollTop = state.scrollTop;
    H.current = index;
  }
};
  
//undo/redo for the editor.
E.undo = function() {this.history.go(-1); return this;};
E.redo = function() {this.history.go(1); return this;};

//create history for each editor instance
BUE.preprocess.history = function(E, $) {
  E.history = new BUE.history(E);
};

})(BUE.instance.prototype, jQuery);

//Change settings in your own postprocess.
//E.history.max = YOUR_MAXIMUM_NUMBER_OF_UNDO_STATES;
//E.history.keys['YOUR_KEYCODE_TRIGGERING_STATE_SAVE'] = 1;
//E.history.keys = false;//allows any key to trigger state saving.
//E.history.period = YOUR_MIN_TIME_IN_MILISECONDS_TO_PASS_BEFORE_SAVING_THE_NEXT_STATE;

//Create custom buttons for your editor
//Undo -> js: E.undo();
//Redo -> js: E.redo();
//Use with bue.ctrl.js and assign Z and Y keys to override browsers' default undo and redo functions.
