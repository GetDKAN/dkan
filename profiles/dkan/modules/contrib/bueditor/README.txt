
- BUEditor:
A plain textarea editor aiming to facilitate code writing.
It's the most flexible text editor of the web because it allows you to;
 - build the editor from scratch.
 - determine the functionality by defining image or text buttons that generate code snippets, html tags, BBCode tags etc.
 - determine the design and layout by defining theme buttons that insert html to the layout.


- WHAT'S NEW

6.x-1.x:
 - custom icon and library paths for each editor.
 - support using different editor templates for different textareas in a page.
 - alternative editor assignment for user roles.
 - theme buttons that provide unlimited theming options.
 - headings (h1, h2, h3, h4) and separators in default editor.
 - changed key variable from "editor" to "BUE". (ex: editor.active is now BUE.active)
 - another popup dialog(BUE.quickPop) that has no title or close button.
 - jquery effects. (ex: effects in popup openings)

6.x-2.x
 - CSS sprites for icons(switchable under editor path settings).
 - bueditor.js got smaller.
 - new buttons for the default editor: Underline, Strike-through, Quote, Code 
 - ability to extend editor instances via post process functions.
 - proper support for selection handling in Opera
 - open() method of popups and dialogs now accepts DOM or jQuery objects as content.
 - new popup markup for better theming.
 - popup shortcuts: ENTER(link click), ESC(close), UP & DOWN (link navigation)
 - improved admin interface: drag & drop, visual icon and key selector.
 - ability to include library files from different directories.
 - ability to include custom css files.
 - import/export complete editor settings, icons, and libraries.
 - IMCE now opens in an editor pop up.
 - No need for php buttons to get IMCE URL which is now stored in Drupal.settings.BUE.imceURL(or BUE.imce.url when the bue.imce library is used)
 - new E.prvAjax() method for live previewing html or non-html markup with the help of Ajax Markup module.
 - new optional library files:
   - bue.autocomplete.js: enables AC inside textareas. Completes html/bbcode tags by default.
   - bue.ctrl.js: converts access keys into CTRL shortcuts.
   - bue.find.js: enables search and replace inside textareas.(Depends on: popup, markup)
   - bue.history.js: cross-browser undo-redo for textareas.
   - bue.markup.js: introduces HTML creating and parsing methods.
   - bue.imce.js: integrates IMCE file browser in a popup.(Depends on: popup)
   - bue.li.js: auto inserts a list item when enter key is pressed at the end of a list item.
   - bue.misc.js: miscellaneous methods used in default editor.(Depends on: popup, markup)
   - bue.popup.js: introduces editor popups: E.dialog & E.quickPop
   - bue.popup.css: experimental CSS3 styling of editor popups
   - bue.preview.js: introduces preview methods E.prv(), E.prvAjax()
   - bue.tab.js: enables indent(TAB), unindent(Shift+TAB) and auto-indent(ENTER) inside textareas
   - bue.min.default.js: minified sum of popup, html, preview, imce, and misc libraries
   - bue.min.all.js: minified sum of all libraries
 - new quick-import templates:
   - bbcode.bueditor.txt: BBCode editor having equivalent buttons of the default editor.
   - commenter.bueditor.txt: Simple editor having no library dependency.
   - default.bueditor.txt: The default editor having various buttons inserting HTML.
   - lab.bueditor.txt: BUEditor lab for experimental code.


- HOW TO INSTALL:
1) Copy editor directory to your modules(sites/all/modules) directory.
2) Enable the module at module administration page.
3) Add/edit editors at admin/config/content/bueditor.
4) There is the default editor you can use as a starting point.
5) You may install IMCE module to use it as a file/image browser in editor's image & link dialogs.
6) Make sure your input format does not filter the tags the editor inserts.


- ADDING BUTTONS:
You can add buttons to an editor by three methods;
1- Manually entering the values for new button fields located at the bottom of the button list.
3- Importing editor code(PHP) that contains buttons.
2- Importing a CSV file that contains previously exported buttons.(deprecated)


- EXPORTING AND DELETING BUTTONS:
You should first select the buttons you want to export or delete, using checkboxes next to them.
Then select the action you want to take in the selectbox below the list and press GO.


- BUTTON PROPERTIES

TITLE:(required) Title or name of the button. Displayed as a hint on mouse over.
A title can be translated by prefixing it with "t:". Ex: t:Bold turns into t('Bold').
If the title starts with "tpl:", the button is considered a theme button. See BUTTON TYPES

CONTENT: Html or javascript code that is processed when the button is clicked. This can also be
php code that is pre evaluated and return html or javascript code. See BUTTON TYPES.

ICON: Image or text to display the button.

KEY: Accesskey that is supported by most browsers as a shortcut on web pages. With the right
key combinations users can fire the button's click event. Use Alt+KEY in Internet Explorer, and
Shift+Alt+KEY in Firefox. You can activate Ctrl+KEY by including the library bue.ctrl.js 

WEIGHT: Required for sorting the buttons. Line-up is from the lightest to the heaviest.
This is handled by dragging and dropping the button rows in the list.


- BUTTON TYPES
There are three types of buttons regarding the CONTENT property;
1- HTML BUTTONS 
2- JAVASCRIPT BUTTONS 
3- PHP BUTTONS
and a special type determined by the title prefix;
4- THEME BUTTONS


- HTML BUTTONS
These are used for directly inserting plain text or html into the textarea.
It is possible to use the selected text in the textarea by using the place holder %TEXT%
For example, assume that the button content is:
<p>%TEXT%</p>
and it is clicked after selecting the "Hello world!" text in the textarea. Then the result is:
<p>Hello world!</p>
with the selection preserved.
Multiple occurrences of %TEXT% is possible and each will be replaced by the selected text. 
These type of buttons are useful for simple html tags or other tag systems like BBCode.
Note: if you want to insert some text containing the phrase %TEXT%, use a javascript button.


- JAVASCRIPT BUTTONS
These type of buttons are used for special cases where it is insufficient to just replace the selected text.
The content of a javascript button must begin with a 3 character text "js:" to be differentiated from a
html button. The remaining code is treated as a javascript code and executed in a function when the
button is clicked. The function is called with the parameters E(active editor) and $(jQuery). 
Editor has many ready-to-use methods and variables making it easy to create javascript buttons.
See EDITOR VARIABLES AND METHODS and especially EDITOR INSTANCE variables and methods.


- PHP BUTTONS
The content of a php button must begin with "php:". The remaining code is pre evaluated at the server 
side and expected to return some code. According to the return value of the php code the real type of 
the button is determined. If the php code returns nothing or false, the button is disabled and does not
show up in the editor.
A php button is indeed a html or javascript button. Php execution is for some special purposes. For example,
it is possible to disable or change the content of the button for a specific user role;
Button with content
php: 
if (user_access('access foo')) {
  return 'js: alert("You have the permission to access foo")';
}
turns into a javascript button having the returned content for users having "access foo" permission. for others 
it is disabled and doesn't show up.


- THEME BUTTONS
A theme button is a special type of button that just inserts html into editor interface for theming purposes. It can be
used to insert separators, line breaks or any html code in order to achieve the themed editor interface. For a button to
be considered a theme button it should have a title starting with "tpl:". Having this title, the button is processed to
insert a piece of html code that is included in button content and button icon(or caption). A theme button, regarding its 
content, can also be a js or php button at the same time.

In order to determine what the button inserts into the layout;
 - first, content is checked and 
    - if it is javascript code(js:) it is executed and the value that returned is inserted into the layout
    - otherwise it is inserted as it is.
 - then, icon or caption is checked and inserted as being wrapped in "<span class="separator"></span>".

Here are some examples;

[title: "tpl:", content: "<br />", caption: ""]
Inserts <br />.(line break)

[title: "tpl:", content: "<br />", icon: "separator.png"]
Inserts <br /><span class="separator"><img src="path-to-sparator.png"></span>.

[title: "tpl:", content: "", caption: "|"] OR [title: "tpl:", content: "<span class="separator">|</span>"]
Inserts <span class="separator">|</span>.

[title: "tpl:", content: "js: return new Date()"]
Inserts new date returned from javascript.

You can also create groups of buttons by creating wrappers around them;

[title: "tpl:", content: "<div class="group1">"] (Start wrapping by opening a div)
[...buttons of the group in between(can be both theme buttons and functional buttons)]
[title: "tpl:", content: "</div>"] (End wrapping by closing the div)


- EDITOR PROPERTIES AND METHODS
BUE:
the top most container variable having other variables and methods in it.

BUE.mode
Integer representing the selection handling mode. 0- None, 1- Gecko and Webkit, 2- IE, 3- Opera

BUE.templates
container for editor templates(configurations, buttons and interface)

BUE.instances
array containing the editor instances in the page

BUE.active:
currently active or last used editor instance. When a button is clicked or a textarea is focused, 
the corresponding editor instance becomes the BUE.active. If there are multiple editor instances, accesskeys 
are switched to work on the BUE.active.
BUE.active is widely used in javascript buttons since the methods of the current editor instance are accessed 
using it. Each editor instance has its own variables and methods that can(should) be used by javascript buttons. 
See EDITOR INSTANCE

BUE.processTextarea(T, tplid):
integrates the editor template(BUE.templates[tplid]) into the textarea T.
This can be used for dynamic editor integration at any time after page load.

BUE.postprocess:
a list(object) of post process functions which are called with the parameters E(editor instance) and $(jQuery) just after instance creation.
BUE.postprocess.yourProcessName = function(E, $){/* Extend/alter the instance E */};

BUE.buttonClick(eindex, bindex):
Trigger click event of the button BUE.instances[eindex].buttons[bindex]

BUE.text(text)
Process text for standardizing the new line characters.

Introduced by bue.popup library:

BUE.dialog:
dialog object of the editor used like a pop-up window for getting user input or displaying data.

BUE.dialog.open(title, content, effect):
Opens the dialog with the given title and content in it.
optional effect parameter is one of the jQuery effects ('slideDown' or 'fadeIn')
see also the openPopup() method below.

BUE.dialog.close(effect):
Closes the dialog.

BUE.quickPop:
another dialog object of the editor. It has no title or close button.
It has its own variables and methods.

BUE.quickPop.open(content, effect):
Opens the quick-pop with the content in it.

BUE.openPopup(id, title, content, effect):
Opens a pop-up having the given "id", titled as "title" and containing the "content".
Returns the js object representing the pop-up(a html table object).
This pop-up object has its internal "open(title, content, effect)" and "close(effect)" methods which can be used for 
further opening and closing operations.
Since pop-up object is a html table object, it has all the methods and properties of a regular table.
The difference between a pop-up and editor.dialog is that editor.dialog can only have one instance visible at a time, and it doesn't allow textarea editing when it is open.
Optional effect parameter is one of the jQuery effects (opening: 'slideDown', 'fadeIn', closing: 'slideUp', 'fadeOut')
or it can be a set of options: 
{
  effect: jQuery effect (default: 'show'),
  speed: either milliseconds or one of the 'slow', 'normal', 'fast' (default: 'normal'),
  offset: position of the popup(default: {top: top offset of the active button, left: left offset of the active button}),
  onopen(or callback): function to be run after opening (default: internal function to focus on the popup),
  onclose: function to be after closing the popup(default: none)
}

BUE.createPopup(id, title, content):
This method is used by openPopup method. Creates and returns the pop-up object for further use.(does not open it)

- EDITOR INSTANCE
Each editor running on a textarea is called an instance. Editor instances have their own variables 
and methods that make it easy to edit textarea content. Active instance on the page can be accessed by the 
variable "BUE.active".

A js button's script is executed in a function with the argument E that refers to BUE.active and the $ that refers to jQuery.
Here are the properties and variables of the istance E:

E.index: index of the instance in the array BUE.instances
E.textArea: textarea of the instance as an HTML object.
E.safeToPreview: initial state of html existance in the textarea.
E.tplid: template id used by the editor.
E.tpl: editor template that this instance uses.(one of BUE.templates)
E.UI: html object that wraps the instance interface. (<div class="bue-ui" id="bue-ui-%index"></div>)
E.buttons: array of buttons of the instance as HTML objects(input objects having the type "button" or "image")
E.bindex: latest/currently clicked button index that can be used in E.buttons. Ex: E.buttons[E.bindex]

E.focus():
Focus on the textarea of the instance.

E.getContent():
Returns the content of the textarea.

E.setContent(text):
Replaces the content of the textarea with the given text.

E.getSelection():
Returns the selected text in the textarea.

E.replaceSelection(text, cursor):
Replace the selected text in the textarea with the given text.
The optional second argument specifies the position of the caret after replacement.
if cursor='start', it is placed at the beginning of the replaced text.
if cursor='end', it is placed at the end of the replaced text.
if cursor is not defined, the selection is preserved containing the replaced text.

E.tagSelection(left, right, cursor):
Encloses the selected text in the textarea with the given left and right texts.
The optional third argument specifies the position of the caret after enclosing.
if cursor='start', it is placed at the beginning of the selected text.
if cursor='end', it is placed at the end of the selected text.
if cursor is not defined, the selection is preserved.

E.makeSelection(start, end):
Create a selection by selecting the characters between the indexes "start" and "end" where "end" is optional.

E.posSelection():
Returns the index values of selection start and selection end.
Returns {start: X, end: Y} where X is the start index and Y is the end index.
Note: No selection is also a selection where start=end=caret position.

E.buttonsDisabled(state, bindex):
Dynamically enable/disable buttons of the instance.
the first argument defines the state of the buttons and should be set to true or false.
the optional second argument defines the index of the button whose state will not change.
Ex: to disable all buttons except the pressed button;
js: E.buttonsDisabled(true, E.bindex);

E.stayClicked(state, bindex):
Add/remove "stay-clicked" class to/from a user defined button having the "bindex".
This method is usually used to toggle a stay-clicked effect on the active button without supplying the second argument.


- EDITOR ICONS
All images with jpg, gif or png extensions in the editor's icon path (which is bueditor_path/icons by default) are accessible by the editor and they are listed in the icon list in the editor editing page.


- EDITOR LIBRARY
While creating a javascript button you may want to use functions or variables from an external javascript library 
in order to shorten the content text and make it clean, or you may want to include some editor specific css files for theming purposes. For all these, you can use the library settings of the editor.


- KNOWN ISSUES
Accesskeys in Internet Explorer:
Pressing an accesskey(Alt+KEY) when there is a selection, deselects it preserving the caret position.

Accesskeys in Firefox:
If there are multiple editors in the page, accesskeys(Shift+Alt+KEY) will work on only the first editor instance. 
This is because FF does not allow dynamic adjustment of accesskeys.

CTRL shortcuts:
Do not use A(select all), C(copy), V(paste), X(cut) keys as they are text operation keys by default.
Do not use F(find), O(open), P(print) keys in IE and Safari as they will always fire their default actions.

New line character:
Since new line is represented by different characters (\r, \r\n, \n) on different platforms, there may be some 
unexpected behaviour of the editor in some platform-browser combos regarding the cursor position after text 
insertion/replacement. Specify new line characters as "\n", if you have to use any in your scripts.

POST variable limit:
Although it's a rare case, you may have to increase your server post variable limit if you have problems while adding too many buttons in admin interface.


- DEFAULT BUTTONS
BUEditor comes with a few default buttons that may help you extend the editor:

Insert/edit image:
Inserts image html after getting the src, width, height, alt attributes from the user. If IMCE module is installed, 
and the user has access to it, a Browse button will appear linking to IMCE image browser.
Editing a previously inserted image is possible if the html code of the image is selected with no extra characters.

Insert/edit link:
Inserts link html after getting the link URL, link text and title from the user. If IMCE module is installed, and the user has access to it, a Browse button will appear linking to IMCE file browser.
Editing a previously inserted link is possible if the html code of the link is selected with no extra characters.

Bold:
Encloses the selected text with the tag <strong>

Italic:
Encloses the selected text with the tag <em>

Underline:
Encloses the selected text with the tag <ins>

Strike-through:
Encloses the selected text with the tag <del>

Headings:
Pops a dialog showing h1, h2, h2, h4, h5, h6 heading tags to choose among.

Quote:
Encloses the selected text with the tag <blockquote>

Code:
Encloses the selected text with the tag <code>

Ordered list:
Converts the lines in the selected text to a numbered list. It is also possible to start a new list with no selection. 
If the selection is an ordered list which was previously created by this button, the lines in the text are restored.

Unordered list:
Converts the lines in the selected text to a bullet list. It is also possible to start a new list with no selection. 
If the selection is an unordered list which was previously created by this button, the lines in the text are restored.

Teaser break:
Inserts Drupal teaser break which is <!--break-->

Preview:
Previews the textarea content. By default, lines and paragraphs break automatically.

Help:
Displays the title(hint) for each button in the editor.


- TIPS AND TRICKS

How to extend image or link dialogs to get values for other attributes of "img" and "a" tags from the user?
How to create a dialog for any tag just like image or link dialogs?

There is the E.tagDialog(tag, fields, options) method(introduced by default library) to create a dialog for
any tag.
tag -> tag name
fields -> an array of attributes that are eiter strings or objects.
options -> object containing optional parameters:
  title: dialog title. if not specified, "Tag editor - (tag)" is used.
  stitle: label for submit button. if not specified, "OK" is used.
  submit: custom submit handler. called with four parameters (tag, form, options, E)
  validate: custom validator. called with four parameters (tag, form, options, E)
  effect: jQuery effect ('slideDown' or 'fadeIn')

The simplest form, for example:
E.tagDialog('div', ['id', 'class', 'style', 'html']);//html is a special keyword that represents inner html
will create a DIV Tag Dialog requesting values of attributes id, class and style and also the inner html.
It will also detect if the current selection is a proper DIV tag, and if so, will put the values of attributes to the corresponding fields.
After submission, it will enclose/replace the selection in the textarea.

You might have noticed that fields in image/link dialogs are declared as objects not as strings. That's a
customized form of declaring attributes. It is ideal to use an object if you want
- a field type other than textfield (type: 'select', options: {'left': 'Left', 'right': 'Right'})
  the default type is text and other supported types are select, textarea, hidden
- a custom label (title: 'Image URL')
- a default value (value: ' ')
- some prefix or suffix text or html (prefix: '[ ', suffix: ' ]')
- to join two fields in a single line like in image width & height fields (getnext: true)
- to set custom attributes for the field (attributes: {size: 10, style: 'width: 200px'})
- to force value entry (required: true)

Note:
- The field object must have a name property that specifies the attribute name. ex:{name: 'href'}
- If a field value has new a line character(\n) in it, then the field type automatically becomes "textarea"

So lets add an "align" attribute field to the image dialog(note that it's not XHTML compliant):

The field object to pass to E.tagDialog is;
{
  name: 'align',//required
  title: 'Image align', // if we don't set it, it will be set as 'Align' automatically.(the name with the first letter uppercase)
  type: 'select', // we use a select box instead of a plain text field.
  options: {'': '', left: 'Left', right: 'Right', center: 'Center'} //structure is {attribute-value: 'Visible value'}
}

Lets add it to the form in the image button's content:

var form = [
 {name: 'src', title: 'Image URL', required: true},
 {name: 'width', title: 'Width x Height', suffix: ' x ', getnext: true, attributes: {size: 3}},
 {name: 'height', attributes: {size: 3}},
 {name: 'alt', title: 'Alternative text', required: true},
 {name: 'align', title: 'Image align', type: 'select', options: {'': '', left: 'Left', right: 'Right', center: 'Center'}} //align
];
E.tagDialog('img', form, {title: 'Insert/edit image'});

That's it. We now have an image dialog which can also get/set the "align" attribute of an image tag.


How to create a button that gets user input and adds it to the textarea?

If you want to use a complete form for user input, then use the E.tagDialog method with a custom submit handler.
If you want to get just a single input you may consider using javascript prompt().
Here is an example that gets image URL as a user input
js:
var url = prompt('URL', '');//prompt for URL
var code = '<img src="'+ url +'" />';//put the url into the code.
E.replaceSelection(code);//replace the selection with the code.


How to extend the functionality of Headings button to create a specialized tag chooser?
How to create an image chooser(ie. smiley chooser) using E.tagChooser?

Firstly, we should understand what E.tagChooser does.
E.tagChooser(tags, options)

Parameter "tags": an array of tag info, each having the format:
 [tag, title, attributes]
  tag: the tag that will enclose the selected text in the textarea
  title: the text or html to help the user choose this tag
  attributes: attributes that will be inserted inside the tag. ex:{'id': 'site-name', 'class': 'dark'}

ex tags: [ ['span', 'Red', {'style': 'color: red'}], ['span', 'Blue', {'class': 'blue-text'}] ]
this will create two options:
Red (inserting <span style="color: red"></span>)
Blue (inserting <span class="blue-text"></span>)

Parameter "options": an object containing the optional parameters.
It defaults to {wrapEach: 'li', wrapAll: 'ul', applyTag: true, effect: 'slideDown'}

wrapEach: the html tag that will enclose each option.
wrapAll: the html tag that will enclose the whole block of options.
applyTag: boolean allowing the user to preview the effect of the tag.
effect: jQuery effect ('slideDown' or 'fadeIn')

Knowing the details we can create our customized tag chooser.
Let's, for example, add styled headings to the default header chooser.
js: E.tagChooser([
 ['h1', 'Header1'],
 ['h1', 'Header1-title', {'class': 'title'}],// this will insert <h1 class="title"></h1>
 ['h2', 'Header2'],
 ['h2', 'Header2-title', {'class': 'title'}],
 ['h3', 'Header3'],
 ['h4', 'Header4']
]);

Now, let's create an image chooser
There will be no title for our tags since we will use applyTag to preview the image that will be inserted. However we will be using a line break for every N(=4 in our example) image in order to create rows of options. Otherwise,
all of them will be placed in a single row.
js: E.tagChooser([
 ['img', '', {'src': '/path-to-images/img1.png'}],//better to set also the width & height & alt attributes
 ['img', '', {'src': '/path-to-images/img2.png'}],
 ['img', '', {'src': '/path-to-images/img3.png'}],
 ['img', '<br />', {'src': '/path-to-images/img4.png'}],//line break added after 4th
 ['img', '', {'src': '/path-to-images/img5.png'}],
 ['img', '', {'src': '/path-to-images/img6.png'}],
 ['img', '', {'src': '/path-to-images/img7.png'}],
 ['img', '<br />', {'src': '/path-to-images/img8.png'}],//br after 8th
 ['img', '', {'src': '/path-to-images/img9.png'}],
 ['img', '', {'src': '/path-to-images/img10.png'}]
], {wrapEach: '', wrapAll: 'div'});


While inserting a single tag should we use the classic <tag>%TEXT%</tag> pattern or the E.toggleTag('tag') ?
What is the difference between <tag>%TEXT%</tag> and js:E.toggleTag('tag') ?

First of all, the classic tag insertion method does not require any additional library, whereas E.toggleTag is a part of the bue.misc.js library.

- Classic method preserves the selected text after tag insertion, whereas E.toggleTag selects the whole insertion.
Classic method: converts the selection "foo" to "<tag>foo</tag>", ("foo" still being selected)
E.toggleTag('tag'): converts the selection "foo" to "<tag>foo</tag>" (<tag>foo</tag> is selected)

- Classic method doesn't parse the selection to check if it is an instance of the tag, whereas E.toggleTag does and toggles it.
Classic method: converts the selection "<tag>foo</tag>" to "<tag><tag>foo</tag></tag>"
E.toggleTag('tag'): converts the selection "<tag>foo</tag>" to "foo"

- In classic method you define the attributes of the tag in the usual way, whereas in E.toggleTag you pass them as an object
<tag class="foo" id="bar">%TEXT%</tag> <=> E.toggleTag('tag', {'class': 'foo', 'id': 'bar'})

- In classic method It's possible to use the selected text for any purpose, whereas in E.toggleTag the only goal is to html.
 Classic method can use the selection multiple times and do anything with it: [bbcode]%TEXT%[/bbcode]: (%TEXT%)


- BACKWARD COMPATIBILITY

In 6.x-2.x the default library was removed and all functions starting with eDef are deprecated and new equivalents were implemented as either bueditor methods or editor instance methods in corresponding libraries.
Below is the list of equivalents:

bue.markup.js
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
bue.preview.js
  eDefAutoP = BUE.autop;
  eDefPreview = function() {BUE.active.prv()};
  eDefPreviewShow = function(E, s, w) {E.prvShow(s, w)};
  eDefPreviewHide = function(E) {E.prvHide()};
  eDefAjaxPreview = function() {BUE.active.prvAjax()};
bue.imce.js
  eDefBrowseButton = function(l, f, t) {return BUE.imce.button(f, t)};
bue.misc.js
  eDefSelProcessLines = eDefTagLines = function (a, b, c, d) {BUE.active.wrapLines(a, b, c, d)};
  eDefHelp = function(fx) {BUE.active.help(fx)};
  eDefTagDialog = function(a, b, c, d, e, f) {BUE.active.tagDialog(a, b, {title: c, stitle: d, submit: e, effect: f})};
  eDefTagInsert = function(a, b) {BUE.active.tgdSubmit(a, b)};
  eDefTagger = function(a, b, c) {BUE.active.toggleTag(a, b, c)};
  eDefTagChooser = function(a, b, c, d, e) {BUE.active.tagChooser(a, {applyTag: b, wrapEach: c, wrapAll: d, effect: e})};