
/**
 * @file
 * A test suite for selected functionality from MarkdownEditor.
 *
 * @author Jakob Persson <jakob@imbridge.com>
 * @author Adam Bergmark
 */

markdownEditor = window.markdownEditor || {};

/*******************************************************************************
 * TEST SUITE
 ******************************************************************************/

/**
 * Test cases for some of the more complicated parts of the script.
 * To run this, open up the demo page for the editor and type
 * javascript:markdownEditor.testSuite(); void 0; into the address bar, or run
 * it through a console by simply executing the function.
 *
 * You will also have to make sure that this file is loaded, the easiest way to
 * do this is to put this file in the same folder as markdowneditor.js.
 *
 * An error will be thrown if an assertion fails.
 */
markdownEditor.testSuite = function () {

  alert("running test suite");

  var s = function () { return markdownEditor.selection; };
  var a = function () { return editor.active; };

  /**
   * Asserts that a value is true, no type coercion is done.
   *
   * @param expression
   *   The expression to test.
   * @param message
   *   An optional error message to describe the assertion.
   * @throws
   *   An Error if the expression is not true.
   */
  function assert(expression, message) {
    message = message || "";
    if (expression !== true) {
      var assertMessage = ",   wanted true but got: " + expression;
      var finalMessage = message + assertMessage;
      throw new Error(finalMessage);
    }
  }

  /**
   * Asserts that two values are equal without coercing types.
   *
   * @param expected
   *   The expected value.
   * @param expression
   *   The value to check.
   * @param message
   *   An optional error message describing the assertion.
   * @throws
   *   An Error if the two values do nat match.
   */
  function eq(expected, expression, message) {
    if (typeof expression === "string" && typeof expected === "string") {
      // Replace newlines since  they can be difficult to  see when an
      // error message is printed to the console.
      expected = expected.replace(/\n/g, "N");
      expression = expression.replace(/\n/g, "N");
    }

    message = message || "";
    var eqMessage = ",   expected: " + expected + " (" + typeof(expected) + "), but got: " + expression + " (" + typeof(expression) + ")";
    var finalMessage = message + eqMessage;

    // Turn into a regular assertion.
    assert(expected === expression, finalMessage);
  }

  var editorArea = document.getElementById("editor-demo");
  
  if (!editorArea) {
    throw new Error("Did not find the editor");
  }

  editorArea.focus();

  // Test getPrefix and getSuffix
  (function () {
    // selecting two characters
    a().setContent("123456");
    eq("123456", a().getContent());
    a().makeSelection(2,4);
    eq("34", a().getSelection(), "getpref/suf1");
    eq("12", s().getPrefix());
    eq("56", s().getSuffix());

    // no selection, just caret
    a().makeSelection(2,2);
    eq("", a().getSelection());
    eq("12", s().getPrefix());
    eq("3456", s().getSuffix());

    // selecting one character
    a().makeSelection(2,3);
    eq("3", a().getSelection());
    eq("12", s().getPrefix());
    eq("456", s().getSuffix());

    // have empty lines before and after
    a().setContent("\n\n123456\n\n");
    eq("\n\n123456\n\n", a().getContent());
    a().makeSelection(4,6);
    eq("34", a().getSelection(), "getpref/suf empty lines");
    eq("12", s().getPrefix());
    eq("56", s().getSuffix());

    // no selection, just caret
    a().makeSelection(4,4);
    eq("", a().getSelection());
    eq("12", s().getPrefix());
    eq("3456", s().getSuffix());

    // selecting one character
    a().makeSelection(4,5);
    eq("3", a().getSelection());
    eq("12", s().getPrefix());
    eq("456", s().getSuffix());

    // Selecting an empty row with rows above and below.
    a().setContent("12\n34\n56");
    a().makeSelection(3,5);
    eq("34", a().getSelection(), "empty row 1");
    eq("", s().getPrefix(), "prefix");
    eq("", s().getSuffix(), "suffix");
  })();


  // Test space().
  (function () {
    a().setContent("123456");
    a().makeSelection(2,4);
    eq("34", a().getSelection());
    s().space();
    eq("12 34 56", a().getContent());
    eq("34", a().getSelection(), "space 1");

    // Space just one character.
    a().setContent("123456");
    a().makeSelection(3,4);
    eq("4", a().getSelection());
    s().space();
    eq("4", a().getSelection());
    eq("123 4 56", a().getContent());

    // Selecting at the start of a row.
    a().setContent("123456");
    a().makeSelection(0,1);
    eq("1", a().getSelection());
    s().space();
    eq("1", a().getSelection());
    eq("1 23456", a().getContent());
    
    // Selecting at the start of a row (but not the first row).
    a().setContent("123\n456");
    a().makeSelection(4, 5);
    eq("123\n456", a().getContent());
    eq("4", a().getSelection());
    s().space();
    eq("4", a().getSelection());
    eq("123\n4 56", a().getContent());
  })();


  // Test Reference.

  // Test fromString.
  (function () {
    var R = markdownEditor.Reference;
    var r;
    r = R.fromString("[ABC]: DEF", "Foo");
    eq("[", r.prefix);
    eq("]: ", r.infix);
    eq("Foo", r.type);
    eq("ABC", r.identifier);
    eq("DEF", r.text);
    eq("[ABC]: DEF", r.toString());

    r = R.fromString("[',.]: !@#$%", "Bar");
    eq("[',.]: !@#$%", r.toString());

    var x = R.fromString("[^1aoeu]: ");
    eq("[^1aoeu]: ", x.toString());
  })();

  // Test lineBreak.
  (function () {
    a().setContent("123456");
    a().makeSelection(2,4);
    eq("34", a().getSelection(), "linebreak1");
    s().lineBreak();
    eq("34", a().getSelection(), "linebreak2");
    eq("12\n\n34\n\n56", a().getContent());

    // Test with a multi line selection.
    a().setContent("123\n456");
    a().makeSelection(2,5);
    eq("3\n4", a().getSelection());
    s().lineBreak();
    eq("3\n4", a().getSelection());
    eq("12\n\n3\n4\n\n56", a().getContent());

    // Test with line above and below, whole middle line selected.
    a().setContent("12\n34\n56");
    a().makeSelection(3,5);
    eq("34", a().getSelection());
    s().lineBreak();
    eq("34", a().getSelection());
    eq("12\n\n34\n\n56", a().getContent());

    a().setContent("*   1\n*   2\n*   3");
    a().makeSelection(0, 22);
    eq("*   1\n*   2\n*   3", a().getSelection());
    s().lineBreak();
    eq("*   1\n*   2\n*   3", a().getSelection());

    // Test removing prefixing spaces.
    a().setContent("foo   bar");
    a().makeSelection(5,9);
    eq(" bar", a().getSelection());
    s().lineBreak();
    eq(" bar", a().getSelection());

    // Test removing suffixing spaces
    a().setContent("foo   bar");
    a().makeSelection(0, 4);
    eq("foo ", a().getSelection());
    s().lineBreak();
    eq("foo ", a().getSelection());


    // Make sure extra line breaks aren't removed.
    a().setContent("\n\n\n\nfoo\n\n\n\n");
    a().makeSelection(4, 7);
    eq("foo", a().getSelection());
    s().lineBreak();
    eq("\n\n\n\nfoo\n\n\n\n", a().getContent());
  })();

  // Test Unordered list.
  (function () {
    a().setContent("a\nb\nc\nd");
    a().makeSelection(1, 6);
    eq("\nb\nc\n", a().getSelection());
    markdownEditor.unorderedList();
    eq("a\n\n*   b\n*   c\n\nd", a().getContent());
  })();


  a().setContent("Tests finished successfully");
};

