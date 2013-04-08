(function($) {

//Faster alternative to resizable textareas.
//Make textareas full expand/shrink on focus/blur
Drupal.behaviors.textarea = {attach: function(context, settings) {
  setTimeout(function() {$('.form-textarea-wrapper.resizable', context).once('textarea', textArea)});
}};

//Faster alternative to sticky headers.
//Header creation is skipped on load and done once the user scrolls on a table.
//Fixes tableselect bug where the state of checkbox in the cloned header is not updated.
Drupal.behaviors.tableHeader = {attach: function(context, settings) {
  var tables =$('table.sticky-enabled:not(.sticky-table)', context).addClass('sticky-table').get();
  if (tables.length) {
    if (!bue.tables) {
      bue.tables = [];
      $(window).scroll(winScroll).resize(winResize);
    }
    bue.tables = bue.tables.concat(tables);
  }
}};

//process resizable textareas
var textArea = function(i, W) {
  var T = $(W).addClass('resizable-textarea').find('textarea');
  var grp = $(El('div')).addClass('grippie').mousedown(TDrag).insertAfter(T)[0];
  $(T).focus(TExpand).blur(TShrink).keydown(TKeyResize);
  grp.bueT = T;
};

//start resizing textarea
var TDrag = function(e) {
  var $T = $(this.bueT), $doc = $(document);
  var doDrag = function(e) {$T.height($T[0].bueH = Math.max(18, bue.Y + e.pageY));return false;}
  var noDrag = function(e) {$doc.unbind('mousemove', doDrag).unbind('mouseup', noDrag);$T.css('opacity', 1);}
  bue.Y = $T.css('opacity', 0.25).height() - e.pageY;
  $doc.mousemove(doDrag).mouseup(noDrag);
  return false;
};

//auto-resize the textarea to its scroll height while typing. triggers are: backspace, enter, space, del, V, X
var resizeKeys = {'8': 1, '13': 1, '32': 1, '46': 1, '86': 1, '88': 1};
var TKeyResize = function(e) {
  var T = this;
  setTimeout(function() {
    if (resizeKeys[e.keyCode]) {
      var sH = T.scrollHeight, $T = $(T), tH = $T.height();
      tH < sH && $T.height(sH + 5);
    }
  });
};

//resize the textarea to its scroll height
var TExpand = function(e) {
  var T = this, sH = T.scrollHeight, $T = $(T), tH = $T.height();
  T.bueH = tH;
  tH < sH && $T.height(sH + 5);
};

//resize the textarea to its original height
var TShrink = function(e) {
  var T = this, $T = $(T), oriH = T.bueH, tH = $T.height();
  if (tH <= oriH) return;
  var $w = $(window), sTop = $w.scrollTop();
  var diffH = $T.offset().top < sTop  ? $T.height() - oriH : 0;
  $T.height(oriH);
  $w.scrollTop(sTop - diffH);
};

//create (table header)
var createHeader = function(table) {
  var $fixed = table.$fixed = $(table.cloneNode(false));
  var $repo = table.$repo = $(El('table')).append(table.tHead.cloneNode(true));
  $repo.css({visibility: 'hidden', position: 'absolute', left: '-999em', top: '-999em'}).insertBefore(table);
  $fixed.addClass('sticky-header').css('position', 'fixed')[0].id += '-fixed';
  return $fixed.insertBefore(table);
};

//handle window scroll (table header)
var winScroll = function(e) {
  var $w = $(window), sX = $w.scrollLeft(), sY = $w.scrollTop();
  for (var table, i = 0; table = bue.tables[i]; i++) {
    tableScroll(table, sX, sY);
  }
};

//handle window resize (table header)
var winResize = function(e) {
  for (var table, i = 0; table = bue.tables[i]; i++) if (table.$fixed && table.$fixed[0].tHead) {
    table.$fixed.width($(table).width());
  }
};

//handle sticky head on scroll (table header)
var tHeadOffset = false;
var tableScroll = function(table, sX, sY) {
  var $table = $(table), pos = $table.offset();
  var minY = pos.top, maxY = minY + $table.height() - $(table.tHead).height() * 2, minX = pos.left;
  var action = minY < sY && sY < maxY;
  var $fixed = table.$fixed || false;
  if (!action && (!$fixed || !$fixed[0].tHead)) return;
  $fixed = $fixed || createHeader(table);//create when necessary
  var $repo = table.$repo;
  if (action) {
    if (tHeadOffset === false) {//calculate toolbar offset
      tHeadOffset = Drupal.settings.tableHeaderOffset ? eval(Drupal.settings.tableHeaderOffset + '()') : 0;
    }
    $fixed.css({visibility: 'visible', top: tHeadOffset, left: minX-sX});
    if (!$fixed[0].tHead) {//run once in action
      var head = table.tHead;
      $table.prepend($repo[0].tHead);
      $fixed.append(head).width($table.width());
    }
  }
  else {//run once out of action
    $fixed.css('visibility', 'hidden');
    var head = table.tHead;
    $table.prepend($fixed[0].tHead);
    $repo.append(head);
  }
};

//process initial text(icon) fields. Add selector-opener next to them.
var iconProc = function(i, inp) {
  var sop = bue.sop.cloneNode(false);
  sop._txt = inp;
  sop.onclick = sopClick;
  inp.parentNode.insertBefore(sop, inp);
  bue.IL[inp.value] && iconShow(inp.value, sop);
};

//click event for selector opener.
var sopClick = function(e) {
  var pos = $(activeSop = this).offset();
  $(bue.IS).css({left: pos.left-parseInt($(bue.IS).width()/2)+10, top: pos.top+20}).show();
  setTimeout(function(){$(document).click(doClick)});
  return false;
};

//document click to close selector
var doClick = function(e) {
  $(document).unbind('click', doClick);
  $(bue.IS).hide();
};

//select text option
var textClick = function() {
  var sop = activeSop;
  if (sop._ico && $(sop._txt).is(':hidden')) {
    $(sop._ico).hide();
    $(sop._txt).show().val('');
  }
  sop._txt.focus();
};

//replace textfield with icon
var iconShow = function(name, sop) {
  $(sop._txt).val(name).hide();
  var img = sop._ico;
  if (img) {
    img.src = iconUrl(name);
    img.alt = img.title = name;
    $(img).show();
  }
  else {
    img = sop._ico = iconCreate(name).cloneNode(false);
    sop.parentNode.appendChild(img);
  }
};

//select image option
var iconClick = function() {iconShow(this.title, activeSop)};

//return URL for an icon
var iconUrl = function(name) {return bue.IP + name};

//create icon image.
var iconCreate = function(name) {
  var img = bue.IL[name];
  if (!img) return false;
  if (img.nodeType) return img;
  img = bue.IL[name] = El('img');
  img.src = iconUrl(name);
  img.alt = img.title = name;
  return img;
};

//create icon selector table
var iconSelector = function() {
  var table = $html('<table id="icon-selector" class="selector-table" style="display: none"><tbody><tr><td title="'+ Drupal.t('Text button') +'"><input type="text" size="1" class="form-text" /></td></tr></tbody></table>')[0];
  var tbody = table.tBodies[0];
  var row = tbody.rows[0];
  row.cells[0].onclick = textClick;
  var i = 1;
  for (var name in bue.IL) {
    if (i == 6) {
      tbody.appendChild(row = El('tr'));
      i = 0;
    }
    row.appendChild(cell = El('td'));
    cell.title = name;
    cell.onclick = iconClick;
    cell.appendChild(iconCreate(name));
    i++;
  }
  //fill in last row
  for(; i < 6; i++) {
    row.appendChild(El('td'));
  }
  return $(table).appendTo(document.body)[0];
};

//create key selector table
var keySelector = function() {
  var table = $html('<table id="key-selector" class="selector-table" style="display: none"><tbody></tbody></table>')[0];
  var tbody = table.tBodies[0];
  var keys = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.split('');
  bue.keys = {};
  for (var row, key, i = 0; key = keys[i]; i++) {
    i%6 == 0 && tbody.appendChild(row = El('tr'));
    bue.keys[key] = $(El('td')).mousedown(keyClick).html(key).attr({title: key}).appendTo(row)[0];
  }
  return $(table).appendTo(document.body)[0];
};

//click on a key in key selector.
var keyClick = function() {
  var key = $(this).text();
  activeSop.value = key;
  keyUsed(key, true, activeSop);
};

//get&set current used state for a key
var keyUsed = function(key, state, inp) {
  var key = key.toString().toUpperCase();
  if (state === undefined) return bue.keys[key] && $(bue.keys[key]).is('.used');
  var F = state ? ['addClass', 'unbind'] : ['removeClass', 'bind'];
  var title = inp ? $(inp).parents('tr:first').find('input.input-title').val() : key;
  bue.keys[key] && $(bue.keys[key])[F[0]]('used')[F[1]]('mousedown', keyClick).attr({title: title || key});
};

//process key fields to update key states
var keyProc = function(i, inp) {
  keyUsed(inp.value, true, inp);
  $(inp).parents('tr:first').find('input.input-title').val();
  $(inp).focus(function() {
    var pos = $(activeSop = this).offset();
    keyUsed(this.value, false);
    $(bue.KS).css({left: pos.left-parseInt($(bue.KS).width()/2)+10, top: pos.top+20}).show();
  }).blur(function() {
    $(bue.KS).hide();
    keyUsed(this.value, true, this);
  });
};

//table drag adjustment. make value updating simpler and start from 0.
var tableDrag = function() {
  var tdrag = Drupal.tableDrag && Drupal.tableDrag['button-table'];
  tdrag && (tdrag.updateFields = function() {
    $('#button-table input.input-weight').each(function(i, field) {field.value = i});
  })();//sort initially to make new buttons sink.
};

//actions for selected buttons
var selAction = function() {
  var $chks = $('#button-table').find('input:checkbox');
  if ($chks.size()) {
    $('#edit-go').click(function() {
      var action = $('#edit-selaction').val();
      if (action && $chks.filter(':checked').size()) {
        return action != 'delete' || confirm(Drupal.t('Are you sure want to delete the selected buttons?'));
      }
      return false;
    });
    $('#edit-selaction').change(function() {
      $('#edit-copyto')[this.value == 'copyto' ? 'show' : 'hide']();
    }).change();
  }
  else {
    $('#sel-action-wrapper').css({display: 'none'});
  }
};

//alter editor textarea process in order to calculate the process time
var eTime = function() {
  var oldProc = BUE.processTextarea;
  BUE.processTextarea = function (T, tplid) {
    var t = new Date(), E = oldProc(T,  tplid), jstime = '' + (new Date() - t);
    E && T.id == 'edit-demo-value' && setTimeout(function() {
      var phptime = '' + Drupal.settings.BUE.demotime, pad = ['000', '00', '0'];
      T.value += '\n\nEditor load times (milliseconds): \n  -Server side (PHP)\t: '+ (pad[phptime.length] || '') + phptime +'\n  -Client side (JS)\t: '+ (pad[jstime.length] || '') + jstime;
    });
    return E;
  };
};

//initiate variables and process page elements
var init = function() {
  bue.IL = Drupal.settings.BUE.iconlist;
  bue.BP = Drupal.settings.basePath;
  bue.IP = bue.BP + Drupal.settings.BUE.iconpath +'/';
  bue.$div = $(El('div'));
  bue.sop = $html('<img class="icon-selector-opener" src="'+ bue.BP +'misc/menu-expanded.png" title="'+ Drupal.t('Select an icon') +'" />')[0];
  //sync safe modifications
  setTimeout(function() {
    bue.IS = iconSelector(); //create icon selector
    bue.KS = keySelector(); //create key selector
    $('input').filter('.input-icon').each(iconProc).end().filter('.input-key').each(keyProc);//process icons and keys
    //disable A, C, V, X key selection when ctrl shortcuts are on.
    window.BUE && window.BUE.preprocess.ctrl && $.each(['A', 'C', 'X', 'V'], function(i, key) {keyUsed(key, true)});
    selAction();//selected buttons actions
    tableDrag();//alter table drag
    //disable auto expand/shrink for demo
    $('#edit-demo-value').unbind('focus', TExpand).unbind('blur', TShrink).unbind('keydown', TKeyResize);
  });
};

//local container
var bue = {};
//create document element
var El = function(name) {return document.createElement(name)};
//html to jQuery
var $html = function(s){return bue.$div.html(s).children()};
//calculate editor instance creation time
window.BUE && eTime();
//initiate
$(document).ready(init);

})(jQuery);