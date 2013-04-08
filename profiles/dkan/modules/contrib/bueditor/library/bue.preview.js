//Introduces E.prv(), E.prvAjax()
//Requires: none
(function(E, $) {

//Show/hide content preview.
E.prv = function(safecheck) {
  var E = this;
  if (E.prvOn) {
    return E.prvHide();
  }
  var safecheck = safecheck === undefined ? true : safecheck;
  var content = E.getContent();
  if (safecheck && !(E.safeToPreview = E.safeToPreview || content.indexOf('<') == -1)) {
    content = '<div class="warning">' + Drupal.t('The preview is disabled due to previously inserted HTML code in the content. This aims to protect you from any potentially harmful code inserted by other editors or users. If you own the content, just preview an empty text to re-enable the preview.') + '</div>';
  }
  return E.prvShow(BUE.autop(content));
};

//show preview with html inside.
E.prvShow = function(html, wrap) {
  var E = this;
  var $T = $(E.textArea);
  var $P = $(E.preview = E.preview || BUE.$html('<div class="preview bue-preview" style="display:none; overflow:auto"></div>').insertBefore($T)[0]);
  if (wrap === undefined || wrap) {
    html = '<div class="'+ (E.textArea.name == 'comment' ? 'comment' : 'node') +'"><div class="content">' + html + '</div></div>';
  }
  if (E.prvOn) {
    $P.html(html);
    return E;
  }
  E.prvPos = E.posSelection();
  $P.show().height($T.height()).width($T.width()).html(html);
  $T.height(1);
  E.buttonsDisabled(true, E.bindex).stayClicked(true);
  E.prvOn = true;
  return E;
};

//Hide preview.
E.prvHide = function() {
  var E = this;
  if (E.prvOn) {
    var $P = $(E.preview);
    $(E.textArea).height($P.height());
    $P.hide();
    E.buttonsDisabled(false).stayClicked(false);
    E.prvOn = false;
    E.prvPos && (E.makeSelection(E.prvPos.start, E.prvPos.end).prvPos = null);
  }
  return E;
};

//Ajax preview. Requires ajax_markup module.
 E.prvAjax = function(format, callback) {
  var E = this, $xM;
  if (E.prvOn) {
    return E.prvHide();
  }
  if (!($xM = $.ajaxMarkup)) {
    return E.prvShow(Drupal.t('Preview requires <a href="http://drupal.org/project/ajax_markup">Ajax markup</a> module with proper permissions set.'));
  }
  if (format && format.call) {
    callback = format;
    format = 0;
  } 
  E.prvShow('<div class="bue-prv-loading">' + Drupal.t('Loading...') + '</div>');
  $xM(E.getContent(), format || $xM.getFormat(E.textArea), function(output, status, request) {
    E.prvOn && E.prvShow(status ? output : output.replace(/\n/g, '<br />')) && (callback || Drupal.attachBehaviors)(E.preview);
  });
  return E;
};

//Convert new line characters to html breaks or paragraphs. Ported from http://photomatt.net/scripts/autop
BUE.autop = function (s) {
  if (s == '' || !(/\n|\r/).test(s)) {
    return s;
  }
  var  X = function(x, a, b) {return x.replace(new RegExp(a, 'g'), b)};
  var  R = function(a, b) {return s = X(s, a, b)};
	var blocks = '(table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|address|math|style|script|object|input|param|p|h[1-6])';
	s += '\n';
  R('<br />\\s*<br />', '\n\n');
  R('(<' + blocks + '[^>]*>)', '\n$1');
  R('(</' + blocks + '>)', '$1\n\n');
  R('\r\n|\r', '\n'); // cross-platform newlines
  R('\n\n+', '\n\n');// take care of duplicates
  R('\n?((.|\n)+?)\n\\s*\n', '<p>$1</p>\n');// make paragraphs
  R('\n?((.|\n)+?)$', '<p>$1</p>\n');//including one at the end
  R('<p>\\s*?</p>', '');// under certain strange conditions it could create a P of entirely whitespace
  R('<p>(<div[^>]*>\\s*)', '$1<p>');
  R('<p>([^<]+)\\s*?(</(div|address|form)[^>]*>)', '<p>$1</p>$2');
  R('<p>\\s*(</?' + blocks + '[^>]*>)\\s*</p>', '$1');
  R('<p>(<li.+?)</p>', '$1');// problem with nested lists
  R('<p><blockquote([^>]*)>', '<blockquote$1><p>');
  R('</blockquote></p>', '</p></blockquote>');
  R('<p>\\s*(</?' + blocks + '[^>]*>)', '$1');
  R('(</?' + blocks + '[^>]*>)\\s*</p>', '$1');
  R('<(script|style)(.|\n)*?</\\1>', function(m0) {return X(m0, '\n', '<PNL>')});
  R('(<br />)?\\s*\n', '<br />\n');
  R('<PNL>', '\n');
  R('(</?' + blocks + '[^>]*>)\\s*<br />', '$1');
  R('<br />(\\s*</?(p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)', '$1');
  if (s.indexOf('<pre') != -1) {
    R('(<pre(.|\n)*?>)((.|\n)*?)</pre>', function(m0, m1, m2, m3) {
      return X(m1, '\\\\([\'\"\\\\])', '$1') + X(X(X(m3, '<p>', '\n'), '</p>|<br />', ''), '\\\\([\'\"\\\\])', '$1') + '</pre>';
    });
  }
  return R('\n</p>$', '</p>');
};

})(BUE.instance.prototype, jQuery);

//backward compatibility
eDefAutoP = BUE.autop;
eDefPreview = function() {BUE.active.prv()};
eDefPreviewShow = function(E, s, w) {E.prvShow(s, w)};
eDefPreviewHide = function(E) {E.prvHide()};
eDefAjaxPreview = function() {BUE.active.prvAjax()};