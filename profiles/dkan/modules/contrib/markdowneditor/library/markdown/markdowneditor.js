/**
 * @file
 * MarkdownEditor JS library for BUEditor.
 *
 * @author Jakob Persson <jakob@nodeone.se>
 * @author Adam Bergmark
 */


/*******************************************************************************
 * IMPORTED HELPERS
 *******************************************************************************/

Cactus = window.Cactus || {};
Cactus.Addon = Cactus.Addon || {};
Cactus.DOM = Cactus.DOM || {};

Cactus.Addon.Function = (function () {

  /**
   * @param Object scope The scope to call the function in
   * @param mixed *args Arguments to pass to the function
   *          bind is called on
   * @return Function(arg1)
   *  @param mixed *args  Additional arguments to concatenate
   *            to the outer args before calling the function
   *
   * Called on a function A, bind returns a function B
   * which executes A in the scope of the first argument
   * given to bind, passing the rest of bind's arguments
   * concatenated with the arguments to B as arguments to A.
   *
   * Implementation notes:
   * apply is retrieved through Function.prototype since setTimeout
   * has no properties in safari 2 (fixed in webkit nightly - 2007-x).
   */
  Function.prototype.bind = function (scope, arg1) {
    var args = Array.prototype.slice.call (arguments, 1);
    var func = this;
    return function () {
      return Function.prototype.apply.call (
        func,
        scope,
        args.concat (
          Array.prototype.slice.call (arguments)));
    };
  };

  /**
   * Executes a function and returns the specified value afterwards.
   * This is useful when a function does not normally
   * return a value. Example of usage would be
   * if you bind a function to a DOM event but want the event to return
   * false afterwards in order to halt the event.
   * This would be writtes like this:
   * foo.bar.bind (foo).returning (false);
   *
   * Any arguments passed to the function returned will be
   * relayed to the inner function.
   *
   * Concise explanation:
   * Applied to a function A and given an argument V, returning returns a function
   * B that executes A in the global scope applying arguments sent to B to A,
   * followed by B returning V.
   *
   * @param mixed value  The value to return after executing the function
   * @return Function  A function that executes the function and
   *           then returns the value specified.
   *     @param mixed *args  arguments that are passed through to the inner
   *               function.
   */
  Function.prototype.returning = function (value) {
    var func = this;
    return function () {
      func.apply (null, arguments);
      return value;
    };
  };

  /**
   * Applied to a function F, wait returns a function G that sets a timeout that
   * executes F after the specified delay. Any additional arguments passed to G
   * are forwarded to F.
   *
   * @param natural delay
   *   The delay in milli seconds before calling the function F.
   * @return Function
   *   The new function, that when executed sets a timeout to call F.
   */
  Function.prototype.wait = function (delay) {
    delay = delay || 0;
    return Function.prototype.bind.call(setTimeout, null, this, delay);
  };
})();

Cactus.DOM.Array = (function () {
  /**
   * Empties an array, use this function when there are several
   * references to an array and you can't modify all of them to
   * point to a new array instance.
   *
   * @param Array array
   */
  Array.empty = function (array) {
    array.length = 0;
  };

  /**
   * Removes the specified element from the given array. If removeAll is set
   * element is removed from every index in the array, should it exist
   * under several indices. Otherwise only the first occurence is removed.
   * The function returns true if it found a match, otherwise false.
   * Any indices to the right of the index are shifted to the left
   *
   * @param Array array
   *   The array to remove the element from.
   * @param mixed element
   *   The element to remove.
   * @param optional boolean removeAll = true
   *   If more than one matching element should be removed (if found).
   * @return boolean
   *   The index of the element that was removed, -1 if nothing was removed.
   */
  Array.remove = function (array, element, removeAll) {

    removeAll = removeAll === undefined ? false : !!removeAll
    var newArray = [];
    var removed  = -1;

    function shouldRemove (matchingElements) {
      return matchingElements && (removeAll || removed === -1);
    }
    /*
     * Append the elements we want to keep to newArray
     */
    for (var i = 0; i < array.length; i++) {
      if (shouldRemove (element === array [i])) {
        removed = i;
      } else {
        newArray.push (array[i]);
      }
    }
    /*
     * Move contents of newArray to array
     */
    if (array.length > newArray.length) {
      Array.empty (array);
      while (newArray.length) {
        array.push (newArray.shift());
      }
    }

    return removed;
  };
}) ();

/**
 * @creation 2006
 *
 * ClassNames is a singleton which provides static methods for modifying CSS
 * class names for HTML Elements.
 */
Cactus.DOM.ClassNames = (function () {
  function ClassNames () {

  } ClassNames.prototype = {
    /**
     * Adds a class to an object. But only if the class doesn't already
     * exist on the object
     *
     * @param HTMLElement o
     * @param string className
     */
    add : function (o, className) {
      // Only add if the className isn't already added
      if (!this.has(o, className)) {
        // If the className property is empty, we can simply overwrite
        // it.
        if (!o.className) {
            o.className = className;
            // if it isn't empty, we have to prepend a space so that
            // "a" and "b" becomes "a b".
        } else {
            o.className += " " + className;
        }
      }
    },
    /**
     * Checks if a given className is as a className of o. It assumes that
     * class names are separated by spaces and all other characters will be
     * counted as part of class names.
     *
     * @param HTMLElement o
     * @param string className
     * @return boolean
     */
    has : function (o, className) {
        return RegExp("(?:^| )" + className + "(?: |$)").test (o.className);
    },
    /**
     * Removes a class from o
     *
     * @param HTMLElement o
     * @param string className
     */
    del : function (o, className) {
      /*
       * Make sure the class exists, so we don't waste time doing
       * what isn't necessary
       */
      if (this.has (o, className)) {
        var classNames = this.get (o);
        Array.remove (classNames, className);
        o.className = classNames.join(" ");
      }
    },
    /**
     * Returns an array containing all classnames of an element
     *
     * @param HTMLElement o
     * @return Array
     */
    get : function (o) {
      return o.className.split (/\s+/);
    }
  };
  return new ClassNames();
})();

Cactus.DOM.tag = (function () {
  /**
   * Checks if a collection can be iterated through using
   * numeric indices and the length property
   *
   * @param mixed collection
   * @return boolean
   */
  var isArrayLike = function (collection) {
    return !! (collection &&
           (typeof collection === "object") &&
           ("length" in collection) &&
           isFinite (collection.length) &&
           (collection !== window) &&
           !("tagName" in collection));
  };

  function append (o, contents) {
    if (typeof (contents) === "string" || typeof contents === "number") {
      o.appendChild (document.createTextNode (contents));
    }
    else if (isArrayLike (contents)) {
      if (o.tagName.toLowerCase() === "select") {
        for (var i = 0, option; i < contents.length; i++) {
          option = contents[i];
          o.appendChild (option);
          if (option._selected) {
            option._selected = undefined;
            o.selectedIndex = i;
          }
        }
      }
      else {
        for (var j = 0; j < contents.length; j++) {
          append (o, contents[j]);
        }
      }
    }
    else if (contents) {
      o.appendChild (contents);
    }
  }

  /**
   * @param string name  the tag name of the element created.
   * @param optional Hash attributes  cantains html attributes to assign to
   *                  the elements, among other things.
   * @param optional mixed contents
   *         string: a text node with the value is appended to the el.
   *         HTMLElement: the element is appended to the new element
   *         Array<HTMLElement>: all elements are appended.
   */
  function tag (name, attributes, contents) {
    if (!attributes) {
      attributes = {};
    }

    var o;
    o = document.createElement (name);

    if (contents === null || contents === undefined) {
      contents = [];
    }
    else if (!isArrayLike(contents)) {
      contents = [contents];
    }

    var style = attributes.style;
    if (style) {
      for (var p in style) if (style.hasOwnProperty (p)) {
        o.style [p] = style [p];
      }
    }
    delete attributes.style;

    for (var q in attributes) if (attributes.hasOwnProperty (q)) {
      // opera  will set  selected=undefined if  it's set  on an
      // option  that  isn't appended  to  a  select  so we  set
      // _selected instead and then  check for the value when we
      // append to a select.
      if (q === "selected") {
        o._selected = attributes.selected;
      }
      else {
        o [q] = attributes [q];
      }
    }

    if (contents !== undefined && contents !== null) {
      append (o, contents);
    }

    return o;
  }
  return tag;
})();


/*******************************************************************************
 * MARKDOWN EDITOR
 ******************************************************************************/

window.markdownEditor = window.markdownEditor || {};

/**
 * Localization function, this can be overwritten if localization is available.
 * By default it always returns the argument it gets.
 *
 * @param string
 *  The string to localize.
 * @param args
 *  String arguments to be replaced.
 * @return
 *  The localized string.
 */
markdownEditor.t = Drupal.t || function (string, args) {
  return string;
};

// Whether development options should be enabled.
// This is used to prevent CSS caching.
markdownEditor.development = true;

/*******************************************************************************
 * SETTINGS
 ******************************************************************************/

// Settings for the script.
markdownEditor.settings = {};

/**
 * Loads the style sheet for the editor dialog.
 * It is only loaded once, and is called by the various buttons that use dialogs.
 */
markdownEditor.settings.addStyleSheet = (function () {
  // Create a persistant variable only accessible to this function.
  var added = false;
  var tag = Cactus.DOM.tag;

  /**
   * Adds a link element to the document's head.
   *
   * @param url
   *   The URL of the css file to add.
   */
  function addCSS (url) {
    // Prevent caching by appending a random GET parameter.
    if (markdownEditor.development) {
      url += "?" + Math.random();
    }
    // Create a link element to include the CSS file.
    document.getElementsByTagName("head")[0].appendChild(tag("link", {
      rel : "stylesheet",
      href : url
    }));
  }

  // Persistant variable for the returned function.
  var added = false;

  // Returns a function that adds the CSS files once only.
  return function () {
    if (!added) {
      addCSS(markdownEditor.settings.cssPath);
      added = true;
    }
  };
})();


/*******************************************************************************
 * EXTRAS
 ******************************************************************************/

markdownEditor.extras = {

  /**
   * Gets the first available descendant of the parent with the specified
   * class name.
   *
   * @param className
   *   The classname to look for. Matches where an element has several class
   *   names is supported.
   * @param parent
   *   Optional. An ancestor of the element to look for. Defaults to the
   *   document object.
   * @return
   *   A HTMLElement. null if no element is found.
   */
  getElementByClassName : function (className, parent) {
    return markdownEditor.extras.getElementsByClassName(className, parent)[0];
  },

  /**
   * Gets all descendants of the parent with the specified class name.
   *
   * @param className
   *   The classname of the elements to look for.
   * @param parent
   *   The parent element to search under, default is document.
   * @return
   *   An array of all found elements, an empty array if none is found.
   */
  getElementsByClassName : function (className, parent) {
    parent = parent || document;
    var elements = parent.getElementsByTagName("*");
    var foundElements = [];
    for (var i = 0; i < elements.length; i++) {
      // Match the  class name, boundaries are  either the start
      // or end of the string, or a space.
      if (new RegExp("(^| )" + className + "( |$)").test(elements[i].className)) {
        foundElements.push(elements[i]);
      }
    }

    return foundElements;
  }

};

markdownEditor.extras.string = {

  /**
   * Repeats a string a given amount of times.
   * Example: repeat("xy", 3) => "xyxyxy"
   *
   * @param string
   *  The string to repeat.
   * @param count
   *   Natural number. The amount of times to repeat the string.
   * @return
   *  The repeated string.
   */
  repeat : function (string, count) {
    var finalString = "";
    for (var i = 0; i < count; i++) {
      finalString += string;
    }
    return finalString;
  }
};

markdownEditor.extras.collection = {

  /**
   * Executes the given function on every item in the collection, and returns
   * an array of all the results.
   *
   * @param collection
   *   An object with a length property and numbered indices.
   * @param func
   *   The function to execute on every item.
   * @return
   *   An array of func results.
   */
  map : function (collection, func) {
    var results = [];
    // Loop through each item and push the result into the results array.
    for (var i = 0; i < collection.length; i++) {
      results.push(func(collection[i]));
    }
    return results;
  },

  /**
   * Checks which index an object has in a collection.
   * The first match is returned.
   *
   * @param collection
   *   The collection to search through.
   * @param object
   *   The object to look for.
   * @return
   *   The index of the object, or -1 if the object isn't found.
   */
  indexOf : function (collection, object) {
    for (var i = 0; i < collection.length; i++) {
      // If  the object  matches in  the current  iteration, the
      // index is returned.
      if (object === collection[i]) {
        return i;
      }
    }
    return -1;
  }
};

markdownEditor.extras.dom = {

  /**
   * Inserts a node after another one.
   *
   * @param newNode
   *   The node to add.
   * @param previousNode
   *   The node that will become the previousSibling of newNode.
   */
  insertAfter : function (newNode, previousNode) {
    // Insert before the next sibling if it exists.
    if (previousNode.nextSibling) {
      previousNode.parentNode.insertBefore(newNode, previousNode.nextSibling);
    }
    // Otherwise append.
    else {
      previousNode.parentNode.appendChild(newNode);
    }
  },

  /**
   * Inserts an element before another one, or append it if the next element
   * doesn't exist.
   *
   * @param newNode
   *   The node to add.
   * @param nextNode
   *   The node to add the new node before, may be falsy to indicate that
   *   an append to the parent should be done.
   * @param parent
   *   The parent of the nodes, if unspecified the parent of nextNode is
   *   retrieved.
   */
  insertBefore : function (newNode, nextNode, parent) {
    parent = parent || nextNode.parentNode;

    // Insert before nextNode if it exists.
    if (nextNode) {
      parent.insertBefore(newNode, nextNode);
    }
    // Otherwise append to the parent.
    else {
      parent.appendChild(newNode);
    }
  }

};


/*******************************************************************************
 * DIALOG
 ******************************************************************************/

markdownEditor.dialog = {

  /**
   * Gets the element containing the dialog's contents.
   *
   * @return
   *  An HTMLElement.
   */
  getContent : function () {
    return BUE.dialog.popup ? markdownEditor.extras.getElementByClassName("cnt", BUE.dialog.popup) : markdownEditor.extras.getElementByClassName("bue-popup-content", BUE.dialog);
  },

  /**
   * Gets the first form element inside the dialog.
   *
   * @return
   *   A HTMLFormElemnt, or null if none is found.
   */
  getForm : function () {
    return markdownEditor.dialog.getContent().getElementsByTagName("form")[0];
  },

  /**
   * Wipes the dialog clean.
   */
  clear : function () {
    var content = markdownEditor.dialog.getContent();
    while (content.firstChild) {
      content.removeChild(content.firstChild);
    }
  },

  /**
   * Clears and closes the dialog.
   */
  close : function () {
    markdownEditor.dialog.clearWidth();
    markdownEditor.dialog.clear();
    BUE.dialog.close();
  },

  /**
   * Clears and opens the dialog. Also attaches event listeners for submitting
   * and closing the dialog.
   *
   * @param title
   *   The title of the dialog.
   * @param cssID
   *   The ID to assign to the dialog, for styling with CSS.
   * @param HTMLContents
   *   A string of contents to insert into the dialog.
   */
  open : function (title, cssID, HTMLContents) {
    markdownEditor.dialog.clear();

    cssID = cssID || "markdowneditor-dialog";
    if (!/^markdowneditor-dialog/.test(cssID)) {
      cssID = "markdowneditor-dialog-" + cssID;
    }

    markdownEditor.dialog.getContent().id = cssID;
    // Event keycodes.
    var keys = {
      esc : 27,
      enter : 13
    };

    // Open the dialog and set any content specified.
    BUE.dialog.open(title, HTMLContents || "");

    // Create a keylistener to enable  the user to close or submit the
    // dialog by pressing enter or escape.
    markdownEditor.dialog.getContent().onkeydown = function (e) {
      e = e || window.event;
      var target = e.target || e.srcElement;

      switch (e.keyCode) {

        // Close the dialog if esc is pressed.
        case keys.esc:
          markdownEditor.dialog.close();
          break;

        // Submit any existing form if enter is pressed.
        case keys.enter:

          // Don't submit if enter was pressed inside a textarea.
          if (target && target.tagName.toLowerCase() === "textarea") {
            break;
          }

          // Submit the dialog by calling the onsubmit handler.
          var form = markdownEditor.dialog.getContent().getElementsByTagName("form")[0];
          if (form && form.onsubmit) {
            form.onsubmit();
            return false;
          }
          break;
      }

    };
  },

  /**
   * Creates a form containing a table with label/form element pairs on each
   * row. Table headers can be specified. Each form element is assigned a
   * unique ID so label elements can be associated with them.
   *
   * This function can not create forms with more than two columns. The amount
   * of rows is arbitrary.
   *
   * @param *arguments
   *   Each argument corresponds to a label/form element pair or a table
   *   header row.
   * @return
   *   A HTMLFormElement with a table of form elements and associated labels.
   */
  createForm : function () {
    var tag = Cactus.DOM.tag;

    var rows = [];
    for (var i = 0; i < arguments.length; i++) {

      var element = arguments[i];

      // If the row contains table headers, add them.
      if (element.headers) {
        var tr = tag("tr");
        for (var j = 0; j < element.headers.length; j++) {
          tr.appendChild(tag("th", null, element.headers[i]));
        }
        rows.push(tr);
      }
      // Otherwise create a label/form element pair.
      else {
        element.tagName = element.tagName || "input";
        element.attributes = element.attributes || {};

        // Set the default type for inputs, if none is specified.
        if (element.tagName === "input" && !element.attributes.type) {
          element.attributes.type = "text";
        }

        // Add the  ID into the attributes hash,  unless an ID
        // is specified already.
        element.attributes.id = element.attributes.id || "dialog_element_" + i;

        element.attributes.className = element.attributes.className || "form-" + element.attributes.type;

        // Add the ID as the name, if no name is specified.
        element.attributes.name = element.attributes.name || element.attributes.id;

        // Create the form element.
        var formElement = tag(element.tagName, element.attributes, element.contents);

        // If  the  element  is  a  select  and  options  were
        // specified  they  are   created  and  added  to  the
        // select. If  a selected  option was specified  it is
        // marked as selected in the select element.
        if (element.tagName === "select" && element.options) {
          var optionIndex = 0;

          // Options are  passed in as a hash  where the key
          // is the option value and the value is the option
          // text.
          for (var value in element.options) if (element.options.hasOwnProperty(value)) {

            // Append the option.
            formElement.appendChild(tag("option", { value : value }, element.options[value]));

            // If this  element is the  selected one, mark
            // it as such in the select.
            if (element.selected == value) {
              formElement.selectedIndex = optionIndex;
            }
            optionIndex++;
          }
          delete optionIndex;
        }

        // If  the element  is  mandatory, a  red asterisk  is
        // appended to the label, and a class name is added to
        // the element.
        if (element.mandatory) {
          element.label = [element.label, tag("span", { style : { color : "red" } }, "*")];
          element.attributes.className = "mandatory";
        }

        // Create the label element.
        var label = tag("label", { htmlFor : element.attributes.id }, element.label);

        // Create  the table  row, the  label is  also created
        // here and is associated with the form element.
        rows.push(tag("tr", null, [
          tag("td", { className : "td-label" }, label),
          tag("td", { className : "td-element" }, formElement)
        ]));
      }
    }

    // Create the form and table/tbody and return the whole structure.
    return tag("form", {
      id : "markdowneditor-dialog-form",
      onsubmit : function () { return false; }
    }, tag("fieldset", null, tag("table", null, tag("tbody", null, rows))));
  },

  /**
   * Sets the onsubmit handler for a form, the form submission is
   * automatically halted.
   *
   * @param form
   *   The HTMLFormElement to assign the event to.
   * @param onsubmit
   *   The function to add as the onsubmit handler.
   */
  setOnsubmit : function (form, onsubmit) {
    form.onsubmit = onsubmit.wait(0).returning(false);
  },

  /**
   * Adds an submit button to the dialog.
   *
   * @param form
   *   The form that the button should be added to.
   * @param onclick
   *   A function that should run when the button is clicked.
   * @param title
   *   An optional localized title of the button. Defaults to t("OK").
   */
  addSubmitButton : function (form, onclick, title) {
    var tag = Cactus.DOM.tag;
    var t = markdownEditor.t;

    title = title || t("OK");

    // Extract the  function with a  timeout so errors  won't keep
    // the function from halting propagation.
    var button = tag("input", {
      type : "button",
      value : title,
      className : "markdowneditor-dialog-submit form-submit",
      onclick : onclick.wait(0).returning(false)
    });

    form.appendChild(button);

    return button;
  },

  /**
   * Adds a cancel button that closes the dialog when clicked.
   *
   * @param form
   *   The form that the button should be added to.
   * @param title
   *   An optional localized title for the button. Defaults to t("Cancel").
   * @param prepend
   *   Whether the button should be prepended to the dialog contents, if
   *   false, or if the argument is omitted the button is appended.
   *   Defaults to false.
   * @param confirmation
   *   Whether the user should be prompted to confirm the action.
   *   Defaults to false.
   * @return
   *   The button that was added.
   */
  addCancelButton : function (form, title, prepend, confirmation) {
    var tag = Cactus.DOM.tag;
    var t = markdownEditor.t;
    prepend = !!prepend;
    confirmation = !!confirmation;
    title = title || t("Cancel");

    // Append or prepend the button and set the onclick handler.
    var button = tag("input", {
      type : "button",
      value : title,
      className : "markdowneditor-dialog-cancel form-submit",
      onclick : function () {
        // Close the dialog  if no confirmation is needed  or the user
        // confirms.
        if (!confirmation || confirm(t("Any changes will be lost. Are you sure you want to cancel?"))) {
          markdownEditor.dialog.close();
        }
        return false;
      }
    });

    // Add the button to the start of the dialog if prepend is set.
    if (prepend) {
      form.insertBefore(button, form.firstChild);
    }
    // Otherwise append to the end of the dialog.
    else {
      form.appendChild(button);
    }

    return button;
  },

  /**
   * Gets the title of the dialog.
   *
   * @return
   *   The string title.
   */
  getTitle : function () {
    return BUE.dialog.popup ? BUE.dialog.popup.rows[0].cells[0].innerHTML : markdownEditor.extras.getElementByClassName("bue-popup-title", BUE.dialog).innerHTML;
  },

  /**
   * @return
   *   The HTML list containing error messages in the editor dialog.
   */
  _getErrorContainer : function () {
    var tag = Cactus.DOM.tag;

    var errors = document.getElementById("markdowneditor-dialog-errors");

    // Lazy initialization for the element.
    if (!errors) {
      var content = markdownEditor.dialog.getContent();
      errors = tag("ul", { id : "markdowneditor-dialog-errors", style : { display : "none" } });

      // Insert the element into the dialog.
      if (content.childNodes.length) {
        content.insertBefore(errors, content.firstChild);
      }
      else {
        content.appendChild(errors);
      }
    }

    return errors;
  },

  /**
   * Adds an error message to the error list.
   *
   * @param message
   *   The message to display, it can take the same arguments as
   *   Cactus.DOM.tag does for its content attribute.
   */
  addError : function (message) {
    var t = markdownEditor.t;
    var tag = Cactus.DOM.tag;

    message = message || t("An unspecified error occured");

    // Add a LI with the message to the error container.
    var container = markdownEditor.dialog._getErrorContainer();
    container.style.display = "block";
    container.appendChild(tag("li", { className : "error" }, message));
  },

  /**
   * Removes all messages from the error container.
   */
  clearErrors : function () {
    // Clear the contents.
    markdownEditor.dialog._getErrorContainer().innerHTML = "";
  },

  /**
   * Adds a button that opens an IMCE dialog, but only if IMCE is marked as
   * enabled.
   *
   * @param parent
   *   The element to append the button to.
   * @param resultElement
   *   The element to put the result from the IMCE window in.
   */
  addIMCELink : function (parent, resultElement) {
    //require URL set.
    if (!BUE.imce.url) {
      return;
    }
    var tag = Cactus.DOM.tag;
    var t = markdownEditor.t;
    var triggerFunction = function () {
      BUE.imce.open(resultElement);
    };
    // Append the button and assign its onclick handler that opens the
    // IMCE browse window.
    parent.appendChild(tag("input", {
      type : "button",
      id : "bue-imce-button",
      className : "imce-button form-submit",
      name : "bue_imce_button",
      value : t("Browse"),
      onclick : triggerFunction
    }));
  },

  /**
   * Display "send to bueditor" link and bind function to IMCE window unload event
   * @param {Object} win
   */
  imceWindowLoad : function (win) {
    win.imce.setSendTo(markdownEditor.t('Send to @app', {'@app': 'BUEditor'}), markdownEditor.dialog.imceWindowFinish);
    // TODO: Do not use jQuery here:
    $(window).unload(function() {
      if (MDEImceWindow && !MDEImceWindow.closed) MDEImceWindow.close();
    });
  },

  /**
   * Fill BUE dialog fields and close IMCE window
   * @param {Object} file
   * @param {Object} win
   */
  imceWindowFinish : function (file, win) {
    var el = document.forms['markdowneditor-dialog-form'].elements;
    var val = {'text' : file.name, 'title' : file.name, 'href' : file.url}
    for (var i in val) {
      if (!el[i].value) el[i].value = val[i];
    }
    win.blur();//or close()
    el[el.length-1].focus();//focus on last element.
  },

  /**
   * Gives focus to the first form element in the dialog.
   */
  focusFirst : function () {
    // Get all elements form the dialog.
    var elements = markdownEditor.dialog.getContent().getElementsByTagName("*");
    var element = null;

    // Loop through the elements and try to find a form element.
    for (var i = 0; i < elements.length; i++) {
      if (/^(button|input|textarea|select)$/.test(elements[i].tagName.toLowerCase())) {
        element = elements[i];
        break;
      }
    }

    // If a form element was found, it's given focus.
    if (element) {
      element.focus();
    }
  },

  /**
   * Clears any width settings that were set by setWidth.
   */
  clearWidth : function () {
    var dialog = document.getElementById("bue-dialog");
    dialog.style.maxWidth = "";
  },

  /**
   * Sets the maximum width of the dialog, useful for dialogs containing text.
   */
  setWidth : function () {
    var dialog = document.getElementById("bue-dialog");
    dialog.style.maxWidth = "600px";
  }
};


/*******************************************************************************
 * SELECTION
 ******************************************************************************/

markdownEditor.selection = {

  /**
   * Selects all characters to the end of the the line the end of the selection
   * belongs to.
   */
  selectToEndOfLine : function () {
    var content = BUE.active.getContent();
    var end = BUE.active.posSelection().end;
    while (content.charAt(end) !== "\n" && end < content.length) {
      end++;
    }
    BUE.active.makeSelection(BUE.active.posSelection().start, end);
  },

  /**
   * Returns a given line from the current selection
   *
   * @param lineNumber
   *   An natural number representing the line number.
   * @return
   *   A string. The line with the given line number.
   *   Returns undefined if the line doesn't exist.
   */
  getLine : function (lineNumber) {
    return BUE.active.getSelection().split(/(\r\n?|\n)/)[lineNumber];
  },

  /**
   * Excludes all preceeding and succeeding line breaks from the current
   * selection.
   */
  excludeLineBreaks : function () {
    var pos;
    if (/^(\n+)/.test(BUE.active.getSelection())) {
      pos = BUE.active.posSelection();
      BUE.active.makeSelection(Math.min(pos.start + RegExp.$1.length, pos.end), pos.end);
    }

    if (/(\n+)$/.test(BUE.active.getSelection())) {
      pos = BUE.active.posSelection();
      BUE.active.makeSelection(pos.start, Math.max(pos.start, pos.end - RegExp.$1.length));
    }
  },

  /**
   * Inserts characters around the current selection, the contents of the
   * selection is left unchanged.
   *
   * @param string
   *   The string to insert around the selection.
   */
  insertAround : function (string) {
    mSelection = markdownEditor.selection;

    mSelection.insertBefore(string);
    mSelection.insertAfter(string);
  },

  /**
   * Inserts characters before the current selection, the contents of the
   * selection is left unchanged
   *
   * @param string
   *   The string to insert before the selection.
   */
  insertBefore : function (string) {
    markdownEditor.selection.replace(/^/, string);
    var pos = BUE.active.posSelection();
    BUE.active.makeSelection(pos.start + string.length, pos.end);
  },

  /**
   * Inserts characters after the current selection, the contents of the
   * selection is left unchanged
   *
   * @param string
   *   The string to insert after the selection.
   */
  insertAfter : function (string) {
    markdownEditor.selection.replace(/$/, string);
    var pos = BUE.active.posSelection();
    BUE.active.makeSelection(pos.start, pos.end - string.length);
  },

  /**
   * Checks if the selection is empty.
   *
   * @return
   *   Whether nothing is selected.
   */
  isEmpty : function () {
    return BUE.active.getSelection() === "";
  },

  /**
   * Gets the remaining characters on the same line to the left of where the
   * selection starts.
   *
   * @return
   *   A string of the prefixing characters, or "" if there is no prefix.
   */
  getPrefix : function () {
    var content = BUE.active.getContent();
    var pos = BUE.active.posSelection();

    // If the selection starts at the first character of the document,
    // no prefix exists.
    if (pos.start === 0) return "";

    // The position where  the prefix ends is one step  to the left of
    // where the selection starts.
    var prefixEnd = pos.start - 1;

    // Loop to the  left, and stop when a line break  is found or when
    // the start of the document is reached.
    for (var i = prefixEnd; i > 0; i--) {
      if (/[\r\n]/.test(content.charAt(i))) {
        i++;
        break;
      }
    }
    // The prefix  is the leftmost value  of the end of  the prefix or
    // the start of the line.
    i = Math.min(i, prefixEnd);

    // Add one to prefixEnd to  include the rightmost character to the
    // left of the selection.
    return content.substring(i, prefixEnd + 1).replace(/[\r\n]/g, "");

  },

  /**
   * Gets the remaining characters on the same line to the right of where the
   * selection ends.
   *
   * @return
   *   A string of the suffixing character, or "" if there is no suffix.
   */
  getSuffix : function () {
    var content = BUE.active.getContent();

    // The suffix starts where the selection ends.
    var suffixStart = BUE.active.posSelection().end;

    // Start at the  start of the suffix, and loop  until a line break
    // or the end of the document is reached.
    for (var i = suffixStart; i < content.length - 1; i++) {
      // Match a line break.
      if (/[\r\n]/.test(content.charAt(i))) {
        // decrement  i  since this  character  isn't  a  part of  the
        // suffix.
        i--;
        break;
      }
    }

    // Add one to i since  substring ends it selection before the i'th
    // character.
    return content.substring(suffixStart, i + 1);
  },

  /**
   * @return
   *   Whether the selection has a prefix.
   */
  hasPrefix : function () {
    return markdownEditor.selection.getPrefix() !== "";
  },

  /**
   * @return
   *   Whether the selection has a suffix.
   */
  hasSuffix : function () {
    return markdownEditor.selection.getSuffix() !== "";
  },

  /**
   * Inserts a  space if the caret  was positioned next to  a non white
   * space character, both before and after the selection. The spaces are then
   * excluded from the selection. The space is not added to the start or end if
   * the selection is at the start or end of a line, respectively.
   */
  space : function () {

    // If the selection is not prefixed with a space and the selection
    // is not at the start of a row.
    if (!/(^|[ \t])$/m.test(markdownEditor.selection.getPrefix())) {
      // Insert a space at the start of the selection.
      markdownEditor.selection.replace(/^/, " ");

      // Make the  current selection exclude  the space that  was just
      // inserted.
      var pos = BUE.active.posSelection();
      if (pos.start === pos.end) {
        pos.end++;
      }
      BUE.active.makeSelection(pos.start + 1, pos.end);
      delete pos;
    }

    // If the selection is not suffixed with a space and the selection
    // is not ath the end of a row.
    if (!/^([ \t]|$)/m.test(markdownEditor.selection.getSuffix())) {

      // Suffix the selection with a space.
      markdownEditor.selection.replace(/$/, " ");

      // Adjust  the selection  to  exclude the  space  that was  just
      // inserted.
      var pos = BUE.active.posSelection();
      if (pos.end > pos.start) {
        pos.end--;
      }
      BUE.active.makeSelection(pos.start, pos.end);
      delete pos;
    }
  },

  /**
   * Inserts line breaks before and after the current selection if the selection
   * has a prefix or suffix respectively. The line breaks are then excluded from
   * the selection. Any preceeding spaces are removed.
   */
  lineBreak : function () {
    var selection = markdownEditor.selection;
    var getPos = BUE.active.posSelection.bind(BUE.active);
    var getContent = BUE.active.getContent.bind(BUE.active);


    // Remove any prefixing spaces.
    var content = getContent();
    var charactersToRemove = 0;
    for (var i = getPos().start - 1; i >= 0; i--) {
      if (content.charAt(i) === " ") {
        charactersToRemove++;
      }
      else {
        break;
      }
    }
    delete content;

    if (charactersToRemove) {
      BUE.active.makeSelection(getPos().start - charactersToRemove, getPos().end);
      selection.replace(new RegExp ("^ {" + charactersToRemove + "}"), "");
    }

    // Remove any suffixing spaces.
    var content = getContent();
    charactersToRemove = 0;
    for (var j = getPos().end; j < content.length; j++) {
      if (content.charAt(j) === " ") {
        charactersToRemove++;
      }
      else {
        break;
      }
    }
    delete content;

    if (charactersToRemove) {
      BUE.active.makeSelection(getPos().start, getPos().end + charactersToRemove);
      selection.replace(new RegExp(" {" + charactersToRemove + "}$"), "");
    }

    // If  the first  row of  the selection  has a  prefix, or  if the
    // previous  two characters  aren't linebreaks  we want  to insert
    // line breaks.
    var charactersBeforeSelection = getContent().substring(0, getPos().start);
    if (selection.hasPrefix() || (getPos().start !== 0 && !/\n\n$/.test(charactersBeforeSelection))) {

      // Loop and  include any prefixing line breaks  in the selection
      // so we only get 2 of them in total after the insertion.
      var content = getContent();
      for (var i = getPos().start - 1; content.charAt(i) === "\n"; i--) {
        BUE.active.makeSelection(getPos().start - 1, getPos().end);
      }
      delete content;

      // Replace the start of the  selection with two line breaks, and
      // adjust the selection to exclude those line breaks.
      selection.replace(/^\n{0,2}/, "\n\n");
      var pos = getPos();
      BUE.active.makeSelection(Math.min(pos.start + 2, pos.end), pos.end);
      delete pos;
    }

    var lastCharacterIndex = getContent().length - 1;
    var charactersAfterSelection = getContent().substring(getPos().start);
    if (selection.hasSuffix() || (getPos().end !== lastCharacterIndex && !/^\n\n/.test(charactersAfterSelection))) {

      // Loop and  include any suffixing line breaks  in the selection
      // so we can calculate the amount.
      var content = getContent();
      for (var i = getPos().end; content.charAt(i) === "\n"; i++) {
        BUE.active.makeSelection(getPos().start, getPos().end + 1);
      }
      delete content;

      selection.replace(/\n{0,2}$/, "\n\n");

      var pos = getPos();
      BUE.active.makeSelection(pos.start, Math.max(pos.end - 2, pos.start));
      delete pos;
    }
  },

  /**
   * Checks if the selection is prefixed with a substring.
   *
   * @param substring
   *   The prefix to look for.
   * @return
   *   A boolean.
   */
  startsWith : function (substring) {
    return BUE.active.getSelection().substring(0, substring.length) === substring;
  },

  /**
   * Checks if the selection is suffixed with a substring.
   *
   * @param substring
   *   The suffix to look for.
   * @return
   *   A boolean.
   */
  endsWith : function (substring) {
    var selection = BUE.active.getSelection();
    return selection.substring(selection.length - substring.length) === substring;
  },

  /**
   * Checks if the selection is prefixed and suffixed with a substring.
   *
   * @param prefix
   *   The prefix to look for
   * @param suffix
   *   The suffix to look for, if not specified the prefix is used as
   *   the suffix as well.
   * @return
   *   A boolean.
   */
  surroundedBy : function (prefix, suffix) {
    if (!(1 in arguments)) {
      suffix = prefix;
    }
    return markdownEditor.selection.startsWith(prefix) && markdownEditor.selection.endsWith(suffix);
  },

  /**
   * Puts the caret at the end of the current selection.
   */
  caretAtEnd : function () {
    var pos = BUE.active.posSelection();
    BUE.active.makeSelection(pos.end, pos.end);
  },

  /**
   * Surrounds the selection by a prefix and suffix.
   * The inserted characters are made part of the selection.
   *
   * @param prefix
   *   The string to prepend to the selection.
   * @param suffix
   *   The string to append to the selection, prefix is used as suffix if
   *   suffix is left out.
   */
  wrap : function (prefix, suffix) {
    // If suffix isn't specified, it defaults to the prefix.
    if (!(1 in arguments)) {
      suffix = prefix;
    }
    markdownEditor.selection.replaceAll(prefix + BUE.active.getSelection() + suffix);
  },

  /**
   * If the  selection starts with empty rows,  they are removed
   * from the  selection since the behavior  would be unexpected
   * otherwise. The same is done if it ends with empty rows.
   */
  trim : function () {
    // Match  newlines and line  breaks, and  make sure  the match
    // ends with  a line break,  otherwise spaces in front  of the
    // first row will be excluded.
    if (/^([\r\n\s]*[\r\n])/.test(BUE.active.getSelection())) {
      var pos = BUE.active.posSelection();
      BUE.active.makeSelection(pos.start + RegExp.$1.length, pos.end);
    }

    // Equivalent as above, but for the end of the line.
    if (/([\r\n][\r\n\s]*)$/.test(BUE.active.getSelection())) {
      var pos = BUE.active.posSelection();
      BUE.active.makeSelection(pos.start, pos.end - RegExp.$1.length);
    }
  },

  /**
   * Adds the given prefix to every row of the current selection.
   *
   * @param prefix
   *   The string to prefix rows with.
   */
  prefixRows : function (prefix) {
    markdownEditor.selection.replace(/^/gm, prefix);
  },

  /**
   * Adds the given suffix to every row of the current selection.
   *
   * @param suffix
   *   The string to append to every row.
   */
  suffixRows : function (suffix) {
    markdownEditor.selection.replace(/$/gm, suffix);
  },

  /**
   * Replaces the whole selection with the given replacement.
   *
   * @param replacement
   *   The string to insert.
   * @param caret
   *   Optional. Specifies where the caret should be positioned afterwards,
   *   see replace.
   */
  replaceAll : function (replacement, caret) {
    markdownEditor.selection.replace(/^[\s\S]*$/, replacement, caret);
  },

  /**
   * Does a search in the current selection and replaces matches.
   * Behaves exactly like String:replace in how it replaces.
   *
   * @param search
   *   String or RegExp, passed directly to String:replace.
   * @param replacement
   *   String or Function, passed directly to String:replace.
   * @param caret
   *   Optional. Where the caret should be positioned after the replacement,
   *   valid values are "start" and "end".
   *   If omitted, the previous selection is maintained.
   */
  replace : function (search, replacement, caret) {
    // Only pass caret if it's specified, we don't make any assumption
    // on how replaceSelection handles optional arguments.
    if (caret) {
      BUE.active.replaceSelection(BUE.active.getSelection().replace(search, replacement), caret);
    }
    else {
      BUE.active.replaceSelection(BUE.active.getSelection().replace(search, replacement));
    }
  }
};


/*******************************************************************************
 * LINK
 ******************************************************************************/

/**
 * Displays a dialog where the user can create a link. The reference is added to
 * the reference section of the BUE.
 */
markdownEditor.link = function () {
  var t = markdownEditor.t;
  var tag = Cactus.DOM.tag;
  var createForm = markdownEditor.dialog.createForm;
  var mDialog = markdownEditor.dialog;

  // Default values for form fields.
  var hrefValue = "";
  var referenceValue = "";
  var titleValue = "";
  var textValue = BUE.active.getSelection();
  var inlineValue = null;

  // Create the dialog form.
  var form = createForm(
    { label : t("Text"), mandatory : true, attributes : { name : "text", value : textValue } },
    { label : t("Description"), attributes : { name : "title", value : titleValue } },
    { label : t("Reference"), attributes : { name : "reference", value : referenceValue } },
    { label : t("URL"), mandatory : true, attributes : { name : "href", value : hrefValue } },
    { label : t("Inline"), attributes : { name : "inline", type : "checkbox", checked: "checked", value: inlineValue } }
  );

  // Add a submit handler and various buttons.
  var submitFunction = markdownEditor.link._process.bind(null, form, "Links");
  mDialog.setOnsubmit(form, submitFunction);
  mDialog.addIMCELink(form.elements.href.parentNode, form.elements.href);
  mDialog.addSubmitButton(form, submitFunction);
  mDialog.addCancelButton(form);

  // Open the dialog and add display the form.
  mDialog.open(t("Insert link"), "link");
  mDialog.getContent().appendChild(form);
  mDialog.focusFirst();
};

// Processes the form submission.
markdownEditor.link._process = function (form) {
  var Reference = markdownEditor.Reference;
  var t = markdownEditor.t;

  var referenceType = "Links";
  var text = form.elements.text.value;
  var reference = form.elements.reference.value || text;
  var href = form.elements.href.value;
  var title = form.elements.title.value;
  var inline = form.elements.inline.checked || false;

  // Validate input.
  markdownEditor.dialog.clearErrors();
  var valid = true;
  if (!text) {
    markdownEditor.dialog.addError(t("Text is a required field."));
    valid = false;
  }
  if (!href) {
    markdownEditor.dialog.addError(t("URL is a required field."));
    valid = false;
  }
  if (!valid) {
    return;
  }

  if (inline) {
    // Insert inline link after caret position
    var replaceString = "[" + text + "](" + href + ( title ? ' "' + title + '"' : '' ) + ")";
    markdownEditor.selection.replaceAll(replaceString);
    BUE.dialog.close();
  }
  else {
    // The text inserted at the caret position.
    var textString = text !== reference ? "[" + text + "][" + reference + "]" : "[" + reference + "][]";
    // The reference to add to the reference section of the BUE.
    var ref = new Reference(referenceType, reference, href + (title ? ' "' + title + '"' : ""));
    markdownEditor.references._callback(textString, ref);
  }
};


/*******************************************************************************
* IMAGE
******************************************************************************/

/**
 * Displays a dialog where the user can add an inline image. The reference is
 * added to the reference section of the BUE. The IMCE dialog is integrated
 * if IMCE is enabled.
 */
markdownEditor.image = function () {
  var t = markdownEditor.t;
  var tag = Cactus.DOM.tag;
  var createForm = markdownEditor.dialog.createForm;

  // Default values for form fields.
  var hrefValue = "";
  var referenceValue = "";
  var titleValue = "";
  var textValue = BUE.active.getSelection();
  var inlineValue = null;

  // Creating the form for the dialog.
  var form = createForm(
    { label : t("Alt"), mandatory : true, attributes : { name : "alt", value : textValue } },
    { label : t("Title"), attributes : { name : "title", value : titleValue } },
    { label : t("Reference"), attributes : { name : "reference", value : referenceValue } },
    { label : t("URL"), mandatory : true, attributes : { name : "href", value : hrefValue } },
    { label : t("Inline"), attributes : { name : "inline", type : "checkbox", value: inlineValue } }
  );

  // Create an onsubmit handler and various buttons.
  var submitFunction = markdownEditor.image._process.bind(null, form, "Images");
  var mDialog = markdownEditor.dialog;
  mDialog.setOnsubmit(form, submitFunction);
  mDialog.addIMCELink(form.elements.href.parentNode, form.elements.href);
  mDialog.addSubmitButton(form, submitFunction);
  mDialog.addCancelButton(form);

  // Open the dialog and display the form.
  mDialog.open(t("Insert image"), "image");
  mDialog.getContent().appendChild(form);
  mDialog.focusFirst();
};

/**
 * Handles submissions for adding images.
 *
 * @param form
 *   The form element of the dialog.
 */
markdownEditor.image._process = function (form) {
  var Reference = markdownEditor.Reference;
  var t = markdownEditor.t;

  var referenceType = "Images";
  var alt = form.elements.alt.value;
  var title = form.elements.title.value;
  var reference = form.elements.reference.value || alt;
  var href = form.elements.href.value;
  var inline = form.elements.inline.checked || false;

  // Validate input.
  markdownEditor.dialog.clearErrors();
  var valid = true;
  if (!alt) {
    markdownEditor.dialog.addError(t("Alt is a required field."));
    valid = false;
  }
  if (!href) {
    markdownEditor.dialog.addError(t("URL is a required field."));
    valid = false;
  }
  if (!valid) {
    return;
  }

  if (inline) {
    // Insert inline link after caret position
    var replaceString = "![" + alt + "](" + href + ( title ? ' "' + title + '"' : '' ) + ")";
    markdownEditor.selection.replaceAll(replaceString);
    BUE.dialog.close();
  }
  else {
    // The text added at the caret position.
    var textString = alt ? "![" + alt + "][" + reference + "]" : "![" + reference + "]";
    // The reference to add to the reference section.
    var ref = new Reference(referenceType, reference, href + (title ? ' "' + title + '"' : ""));
    markdownEditor.references._callback(textString, ref);
  }
};


/*******************************************************************************
 * FOOTNOTE
 ******************************************************************************/

/**
 * Opens a dialog that lets the user create a footnote and its associated
 * reference. A default reference name is specified.
 */
markdownEditor.footnote = function () {
  var tag = Cactus.DOM.tag;
  var t = markdownEditor.t;
  var createForm = markdownEditor.dialog.createForm;
  var mDialog = markdownEditor.dialog;

  var referenceValue = "";
  var textValue = "";

  var contents = BUE.active.getContent();

  // Get the first available reference number.
  for (var i = 1; new RegExp("\\[\\^" + i + "\\]", "m").test(contents); i++);
  referenceValue = i;

  // Create the dialog form.
  var form = createForm(
    { label : t("Reference"), mandatory : true, attributes : { name : "reference", value : referenceValue } },
    { label : t("Text"), tagName : "textarea", attributes : { name : "text", value : textValue, className : 'form-textarea' } }
  );

  // Create the onsubmit function
  var submitFunction = markdownEditor.footnote._process.bind(null, form, "Footnotes");

  // Add buttons.
  mDialog.setOnsubmit(form, submitFunction);
  mDialog.addSubmitButton(form, submitFunction);
  mDialog.addCancelButton(form);

  // Open the dialog and display the form.
  mDialog.open(t("Insert footnote"), "footnote");
  mDialog.getContent().appendChild(form);
  mDialog.focusFirst();
};

/**
 * Handles the submission when creating footnotes.
 *
 * @param form
 *   The HTMLFormElement of the dialog.
 */
markdownEditor.footnote._process = function (form) {
  var Reference = markdownEditor.Reference;
  var t = markdownEditor.t;

  var referenceType = "Notes";
  var reference = form.elements.reference.value;
  var text = form.elements.text.value;

  // Validate input.
  markdownEditor.dialog.clearErrors();
  if (!reference) {
    markdownEditor.dialog.addError(t("Reference is a required field."));
    return;
  }

  // The text to insert at the caret position.
  var textString = "[^" + reference + "]";
  // The reference to add to the reference section.
  var ref = new Reference(referenceType, reference, text);
  ref.setPrefix("[^");

  window.r = ref;

  markdownEditor.references._callback(textString, ref, false, t("You cannot make two references to the same footnote."), false);
};


/*******************************************************************************
 * ABBREVIATION
 ******************************************************************************/

/**
 * Opens a dialog that lets the user add an abbreviation along with its
 * definiton.
 */
markdownEditor.abbreviation = function () {
  var tag = Cactus.DOM.tag;
  var t = markdownEditor.t;
  var createForm = markdownEditor.dialog.createForm;

  // Default values for the form elements.
  var abbreviationValue = BUE.active.getSelection();
  var textValue = "";

  // Create the dialog form.
  var form = createForm(
    { label : t("Abbreviation"),  mandatory : true, attributes : { name : "abbreviation", value : abbreviationValue } },
    { label : t("Definition"), attributes : { name : "text", value : textValue } }
  );

  // Create the submit button and  have it assign an onclick handler
  // that processes the form submission. Also add a cancel button.
  var submitFunction = markdownEditor.abbreviation._process.bind(null, form, "Abbreviations");
  var mDialog = markdownEditor.dialog;
  mDialog.setOnsubmit(form, submitFunction);
  mDialog.addSubmitButton(form, submitFunction);
  mDialog.addCancelButton(form);

  // Open the dialog and display the form.
  mDialog.open(t("Insert abbreviation"), "abbreviation");
  mDialog.getContent().appendChild(form);
  mDialog.focusFirst();
};

/**
 * Handles submissions when adding abbreviations.
 *
 * @param form
 *   The HTMLFormElement of the dialog.
 */
markdownEditor.abbreviation._process = function (form) {
  var Reference = markdownEditor.Reference;
  var t = markdownEditor.t;

  var referenceType = "Abbreviations";
  var abbreviation = form.elements.abbreviation.value;
  // The text to be inserted at the caret position.
  var text = form.elements.text.value;
  if (!text) {
    text = abbreviation;
  }

  // Validate input.
  markdownEditor.dialog.clearErrors();
  if (!abbreviation) {
    markdownEditor.dialog.addError(t("Abbreviation is a required field"));
    return;
  }

  // The reference to be added to the reference section.
  var ref = new Reference(referenceType, abbreviation, text);
  ref.setPrefix("*[");

  markdownEditor.references._callback(abbreviation, ref);
};


/*******************************************************************************
 * AUTO LINK
 ******************************************************************************/

/**
 * Turns the selection into an auto link. An auto link is just an URL inside
 * angle brackets, <http://www.example.com>.
 */
markdownEditor.autoLink = function () {
  var mSelection = markdownEditor.selection;

  // Insert brackets around the caret if nothing is selected.
  if (mSelection.isEmpty()) {
    mSelection.space();
    mSelection.insertBefore("<");
    mSelection.insertAfter(">");
  }
  // If the selection is an auto link, the angle brackets are removed.
  else if (mSelection.surroundedBy("<", ">")) {
    mSelection.replace(/^<([\s\S]+)>/m, "$1");
  }
  // Otherwise angle brackets are added around the selection.
  else {
    mSelection.wrap("<", ">");
  }
};


/*******************************************************************************
 * UNORDERED LIST
 ******************************************************************************/

/**
 * Toggles the current selection into or out of being an unordered list, it can
 * also convert from an ordered list into an unordered.
 */
markdownEditor.unorderedList = function () {

  var mSelection = markdownEditor.selection;

  mSelection.trim();

  // Remove OL enumeration if it's present
  if (/^\d+\. /.test(BUE.active.getSelection())) {
    mSelection.replace(/^\d+\. /gm, "");
    mSelection.replace(/^ {4}/gm, "");
  }

  // If the selection is an unordered list.
  if (/^\* /m.test(BUE.active.getSelection())) {
    // Remove the list characters.
    mSelection.replace(/^\* +/gm, "");
    mSelection.replace(/^ {4}/gm, "");
  }
  // If the selection is empty, insert a list item and put the caret
  // at the end of the insertion.
  else if (BUE.active.getSelection() === "") {
    mSelection.replaceAll("*   ");
  }
  // Append an asterisk to every row that isn't indented.
  // Does not prefix empty rows.
  else {
    var lines = BUE.active.getSelection().split(/\r?\n|\r/);
    var newLines = [];

    var prefixReg = /^(?: {4}|> )/;

    newLines.push("*   " + lines[0]);

    // Gets the current prefix of the  line, a prefix in this sense is
    // only a block quote "> " or a code block " ".
    function getLinePrefix(line) {
      var match = prefixReg.exec(line);
      return match ? match[0] : "";
    }

    for (var i = 1, line; i < lines.length; i++) {
      var linePrefix = getLinePrefix(lines[i]);
      var previousLinePrefix = getLinePrefix(lines[i - 1]);

      // Do nothing with empty lines.
      if (lines[i] === "") {
        newLines.push(lines[i]);
      }
      // If the line has no prefix, it should be prefixed.
      else if (!linePrefix) {
        newLines.push("*   " + lines[i]);
      }
      // A prefix should be added if the line  does not have the
      // same prefix as  the previous line. For example  a block quote
      // line below  another one  means that they  both belong  to the
      // same list item.
      else if (lines[i] !== "" && linePrefix !== previousLinePrefix && previousLinePrefix) {
        newLines.push("*   " + lines[i]);
      }
      // We've concluded  that the line belongs to  the previous line,
      // so we just indent it.
      else {
        newLines.push("    " + lines[i]);
      }
    }

    mSelection.replaceAll(newLines.join("\n"));
  }

  mSelection.lineBreak();
  mSelection.caretAtEnd();
};


/*******************************************************************************
 * ORDERED LIST
 ******************************************************************************/

/**
 * Turns the selection into an ordered list, if the selection is alreadf an
 * ordered list the enumeration is removed. The function can also turn an
 * unordered list into an ordered one.
 */
markdownEditor.orderedList = function () {
  var mSelection = markdownEditor.selection;

  mSelection.trim();

  // If the selection is an unordered list, we remove the bullets.
  if (/^\* /.test(BUE.active.getSelection())) {
    mSelection.replace(/^\* {3}/gm, "");
    mSelection.replace(/^ {4}/gm, "");
  }

  // If  the list  is  formatted  as ordered  already,  we remove  the
  // enumeration.
  if (/^ *\d+\./.test(BUE.active.getSelection())) {
    mSelection.replace(/^ *\d+\.\s*/gm, "");
    mSelection.replace(/^ {4}/gm, "");
  }
  // If the selection  is empty, insert a list item  and put the caret
  // at the end of the insertion.
  else if (BUE.active.getSelection() === "") {
    mSelection.replaceAll("1.  ");
  }
  // Insert numbers for  every row of the selection,  if the selection
  // is an unordered list the asterisks are removed in the process.
  else {
    var prefixCounter = 1;
    // Get the number prefix for the next row. An internal counter is kept.
    function createLinePrefix() {
      return (prefixCounter++) + ".  ";
    }

    var lines = BUE.active.getSelection().split(/\r?\n|\r/);
    var newLines = [];

    // Matches a code block or block quote line.
    var prefixReg = /^(?: {4}|> )/;

    newLines.push(createLinePrefix() + lines[0]);

    function getLinePrefix(line) {
      var match = prefixReg.exec(line);
      return match ? match[0] : null;
    }

    // Handle each line separately.
    for (var i = 1, line; i < lines.length; i++) {
      var linePrefix = getLinePrefix(lines[i]);
      var previousLinePrefix = getLinePrefix(lines[i - 1]);

      // Skip empty lines.
      if (lines[i] === "") {
        newLines.push(lines[i]);
      }
      // A prefix should  only be added if the line  does not have the
      // same prefix as  the previous line. For example  a block quote
      // line below  another one  means that they  both belong  to the
      // same list item.
      else if (!linePrefix || (lines[i] !== "" && linePrefix !== previousLinePrefix)) {
        newLines.push(createLinePrefix() + lines[i]);
      }
      // The line belongs to the previous line, so it's indented to match this.
      else {
        newLines.push("    " + lines[i]);
      }
    }

    mSelection.replaceAll(newLines.join("\n"));
  }

  mSelection.lineBreak();
  mSelection.caretAtEnd();
};


/*******************************************************************************
 * HEADER
 ******************************************************************************/

/**
 * Gives the user a dialog for creating headers. Selected headers can also be
 * modified.
 */
markdownEditor.header = function () {
  var t = markdownEditor.t;
  var tag = Cactus.DOM.tag;
  var createForm = markdownEditor.dialog.createForm;

  var headerValue, textValue, idValue;

  var selection = BUE.active.getSelection();

  // If a header is selected, we extract the values.
  // If a value is unavailable, a default value is specified.
  if (/(#+)\s*(\S*)\s*\{#([^\}]+)\}/.test(selection)) {
    headerValue = RegExp.$1.length || "2";
    textValue = RegExp.$2 || "";
    idValue = RegExp.$3 || "";
  }
  // Otherwise we assign default values.
  else {
    headerValue = 2;
    textValue = BUE.active.getSelection();
    idValue = "";
  }

  // Available header depths. Used to create the select in the dialog form.
  var options = {
    2 : 2,
    3 : 3,
    4 : 4,
    5 : 5,
    6 : 6
  };

  // Create the dialog form.
  var form = createForm(
    { label : t("Header Level"), tagName : "select", options : options, selected : headerValue, attributes : { name : "header_type" } },
    { label : t("Text"), mandatory : true, attributes : { name : "text", value : textValue } },
    { label : t("ID"), attributes : { name : "id", value : idValue } }
  );

  // Create a submit button  and add an onclick/onsubmit handler. Also
  // create a cancel button.
  var mDialog = markdownEditor.dialog;
  var submitFunction = markdownEditor.header._callback.bind(null, form);

  mDialog.setOnsubmit(form, submitFunction);
  // The event is passed on through the submit button to onsubmit.
  var submitButton = mDialog.addSubmitButton(form, function () {});
  submitButton.onmousedown = submitFunction;
  mDialog.addCancelButton(form);

  // Create the dialog and display the form.
  mDialog.open(t("Insert header"), "header");
  mDialog.getContent().appendChild(form);
  mDialog.focusFirst();
};

/**
 * Handles submissions when adding headers
 *
 * @param form
 *   The HTMLFormElement of the dialog.
 */
markdownEditor.header._callback = function (form) {
  var t = markdownEditor.t;

  // Try to parse the integer value, default to 2 if that isn't possible.
  var headerType = parseInt(form.elements.header_type.value, 10) || 2;
  var text = form.elements.text.value;
  var id = form.elements.id.value ? " {#" + form.elements.id.value + "}" : "";

  // Validate input.
  markdownEditor.dialog.clearErrors();
  if (!text) {
    markdownEditor.dialog.addError(t("Text is a required field"));
    return;
  }

  // Add one # for every form level (2 becomes ##, 3 becomes ###).
  var headerHashes = markdownEditor.extras.string.repeat("#", headerType);

  // Insert the information and close the dialog.
  BUE.active.replaceSelection(headerHashes + " " + text + " " + id);
  markdownEditor.selection.lineBreak();
  markdownEditor.selection.caretAtEnd();
  BUE.dialog.close();
};


/*******************************************************************************
 * INLINE CODE
 ******************************************************************************/

/**
 * Wraps the selection with backticks, or unwraps if the selection is wrapped
 * already.
 */
markdownEditor.codeInline = function () {
  if (markdownEditor.selection.isEmpty()) {
    markdownEditor.selection.space();
    markdownEditor.selection.insertAround("`");
  }
  // If the selection is already code, the back ticks are removed.
  else if (markdownEditor.selection.surroundedBy("`")) {
    markdownEditor.selection.replace(/^\`([\s\S]+)\`/m, "$1");
  }
  // Otherwise back ticks are added around the selection.
  else {
    BUE.active.replaceSelection("`" + BUE.active.getSelection() + "`");
  }
};


/*******************************************************************************
 * EMPHASIS
 ******************************************************************************/

/**
 * Wraps the selection with asterisks or unwraps if the selection is emphasized
 * already.
 */
markdownEditor.emphasis = function () {
  if (markdownEditor.selection.isEmpty()) {
    markdownEditor.selection.space();
    markdownEditor.selection.insertAround("*");
  }
  // If the selection is emphasized, the asterisks are removed.
  else if (markdownEditor.selection.surroundedBy("*")) {
    markdownEditor.selection.replace(/^\*([\s\S]+)\*/m, "$1");
  }
  // Otherwise asterisks are added around the selection.
  else {
    markdownEditor.selection.wrap("*");
  }
};


/*******************************************************************************
 * STRONG EMPHASIS
 ******************************************************************************/

/**
 * Wraps the selection with double asterisks, or unwraps if it's already
 * emphasized.
 */
markdownEditor.strongEmphasis = function () {
  if (markdownEditor.selection.isEmpty()) {
    markdownEditor.selection.space();
    markdownEditor.selection.insertAround("**");
  }
  // If the selection is emphasized, the asterisks are removed.
  if (markdownEditor.selection.surroundedBy("**")) {
    markdownEditor.selection.replace(/^\*+([\s\S]+?)\*+$/, "$1");
  }
  // Otherwise asterisks are added around the selection.
  else {
    markdownEditor.selection.replace(/^\**([\s\S]+?)\**$/, "**$1**");
  }
};


/*******************************************************************************
 * BLOCK QUOTES
 ******************************************************************************/

/**
 * Toggles block quoting of the selection.
 */
markdownEditor.blockQuote = function () {
  var mSelection =  markdownEditor.selection;

  mSelection.excludeLineBreaks();

  // Remove ">" in the beginning of rows if they are there.
  if (mSelection.startsWith(">")) {
    mSelection.replace(/^>\s?/gm, "");
  }
  // Otherwise "> " is prepended  to every row, existing code blocks
  // are removed in the process.
  else {
    mSelection.prefixRows("> ");
  }

  mSelection.lineBreak();
  mSelection.caretAtEnd();
};


/*******************************************************************************
 * HORIZONTAL RULER
 ******************************************************************************/

/**
 * Inserts a horizontal ruler in its own paragraph.
 */
markdownEditor.horizontalRuler = function () {
  var selection = markdownEditor.selection;
  selection.replaceAll("---------");
  selection.lineBreak();
  selection.caretAtEnd();
};


/*******************************************************************************
 * CODE BLOCK
 ******************************************************************************/

/**
 * Toggles a code block for the selection.
 */
markdownEditor.codeBlock = function () {
  var mSelection = markdownEditor.selection;

  mSelection.excludeLineBreaks();

  if (mSelection.startsWith("    ")) {
    mSelection.replace(/^ {4}/gm, "");
  }
  else {
    mSelection.prefixRows("    ");
  }

  // Make sure  the block  is located  on its own  row, and  place the
  // caret at the end.
  mSelection.selectToEndOfLine();
  mSelection.lineBreak();
  mSelection.caretAtEnd();
};


/*******************************************************************************
 * LINE BREAK
 ******************************************************************************/

/**
 * Inserts a line break.
 */
markdownEditor.lineBreak = function () {
  // Replace selection with two spaces and a line break.
  markdownEditor.selection.replaceAll("  \n", "end");
};


/*******************************************************************************
 * TABLE
 ******************************************************************************/

/**
 * Creates a dynamic table dialog for creating a Markdown Extra table.
 *
 * Implementation:
 * The table used to structure the form has at least three rows and at least
 * one column. The first row contains table headings, the second one contains
 * alignments for the content cells. Finally, the rest of the rows (the amount
 * of these is dynamic) contain the actual table data. The table will end up
 * being structured like it is in the dialog.
 */
markdownEditor.Table = (function() {
  var tag = Cactus.DOM.tag;
  var t = markdownEditor.t;
  var string = markdownEditor.extras.string;
  var collection = markdownEditor.extras.collection;
  var dom = markdownEditor.extras.dom;
  var ClassNames = Cactus.DOM.ClassNames;

  function Table () {
    this.createForm();
    this.columns = this.startingColumns;
    this.openDialog();
  } Table.prototype = {
    // The amount of rows to display when the dialog opens.
    startingRows : 2,
    // The amount of columns to display when the dialog opens.
    startingColumns : 2,

    // The amount of content rows and content columns currently in the dialog.
    rows : 0,
    columns : 0,

    // References to the form elements.
    form : null,
    table : null,
    tbody : null,

    /**
     * @return
     *   A TD with an input box for a table header.
     */
    createHeaderCell : function () {
      return tag("td", null, tag("input", {
        type : "text",
        name : "header",
        className : "form-text"
      }));
    },

    /**
     * Creates a cell for choosing the alignment of a column, consists of a
     * select with four values, "none" (or ""), "lef", "center" and "right".
     *
     * @return
     *   A TD with three radio buttons along with their labels.
     */
    createAlignmentCell : function () {
      return tag("td", { className : "alignment-cell" }, tag("select", { className : "form-select" }, [
        tag("option", { value : "none", selected : true }, t("- None -")),
        tag("option", { value : "left" }, t("Left")),
        tag("option", { value : "center" }, t("Center")),
        tag("option", { value : "right" }, t("Right"))
      ]));
    },

    /**
     * Creates a cell for setting the value of a table cell.
     *
     * @return
     *   A TD with an input.
     */
    createContentCell : function () {
      return tag("td", null, [
        tag("input", {
          type : "text",
          className : "form-text"
        })
      ]);
    },

    /**
     * Removes a content row from the table if there are at least 2 rows
     * already.
     *
     * @param row
     *   The row to remove.
     */
    removeRow : function (row) {
      if (this.rows >= 2) {
        this.tbody.removeChild(row);
        this.rows--;

        if (this.rows === 1) {
          this.getRemoveRowButton(this.getContentRows()[0]).disabled = true;
        }

      }
    },

    /**
     * Gets the remove button for the given row.
     *
     * @param row
     *   The row to find the button on.
     * @return
     *   The button that removes the specified row.
     */
    getRemoveRowButton : function (row) {
      var cell = row.cells[row.cells.length - 1];
      var buttons = cell.getElementsByTagName("button");
      for (var i = 0; i < buttons.length; i++) {
        if (buttons[i].innerHTML === "-") {
          return buttons[i];
        }
      }
      throw new Error("No button found");
    },

    /**
     * Adds a content row to the document.
     *
     * @param previousRow
     *   The row that will be the previousSibling of the row created.
     */
    addRow : function (previousRow) {
      if (this.rows === 1) {
        this.getRemoveRowButton(this.getContentRows()[0]).disabled = false;
      }

      if (this.tbody.lastChild === previousRow) {
        this.tbody.appendChild(this.createContentRow());
      }
      else {
        this.tbody.insertBefore(this.createContentRow(), previousRow.nextSibling);
      }
    },

    /**
     * Creates a row of content cells, takes into account the current number
     * of columns.
     *
     * @return
     *   A TR containing content cells.
     */
    createContentRow : function () {
      var cells = [tag("th")];
      var columns = this.columns || this.startingColumns;
      for (var i = 0; i < columns; i++) {
        cells.push(this.createContentCell());
      }

      var modificationContainer = tag("div", {
        className : "row-modification-container"
      });

      cells.push(tag("td", { className : "row-modification-cell" }, modificationContainer));

      var row = tag("tr", {
        className : "content-row"
      }, cells);


      // Add buttons for adding and removing rows.
      modificationContainer.appendChild(this.createRemoveRowButton(row));
      modificationContainer.appendChild(this.createAddRowButton(row));

      this.rows++;
      return row;
    },

    /**
     * Adds a button for adding rows.
     *
     * @param previousRow
     *   The row to insert new rows after if the button is clicked.
     * @param title
     *   An optional title text for the button.
     * @return
     *   A button that adds a row below another one.
     */
    createAddRowButton : function (previousRow, title) {
      title = title || t("Insert a row below this row.");
      var addButton = tag("button", {
        title : title,
        className : "add-row-button"
      }, "+");
      addButton.onclick = this.addRow.bind(this, previousRow).wait(0).returning(false);
      return addButton;
    },

    /**
     * Creates a button for removing the given row.
     *
     * @param row
     *   The row to remove when the button is clicked.
     * @param title
     *   An optional title for the button.
     */
    createRemoveRowButton : function (row, title) {
      title = title || t("Remove this row.");

      return tag("button", {
        title : title,
        className : "remove-row-button",
        onclick : this.removeRow.bind(this, row).wait(0).returning(false)
      }, "-");
    },

    /**
     * Creates a button for adding a column after the given cell.
     *
     * @param cell
     *   The column this cell belongs to will be the column that the new column
     *   is inserted after.
     * @param title
     *   An optional title for the button.
     * @return
     *   The HTMLButtonElement created.
     */
    createAddColumnButton : function (cell, title) {
      title = title || t("Insert a new column to the right of this column.");
      var addButton = tag("button", { title : title }, "+");
      addButton.onclick = this.addColumn.bind(this, cell).wait(0).returning(false);
      return addButton;
    },

    /**
     * Creates a button that adds a column before the given cell's column.
     *
     * @param cell
     *   The table cell whose column the new column is added before.
     * @param title
     *   Optional. The localized text of the button.
     * @return
     *   A HTMLButtonElement that can add a new column.
     */
    createPrependColumnButton : function (cell, title) {
      title = title || t("Insert a new column to the left of this column.");
      return tag("button", {
        className : "prepend-column-button",
        title : title,
        onclick : this.prependColumn.bind(this, cell).wait(0).returning(false)
      }, "+");
    },

    /**
     * Creates a button for removing  the column the given cell belongs to.
     *
     * @param cell
     *   When clicked the column this cell belongs to will be removed.
     * @return
     *   The HTMLButtonElement created.
     */
    createRemoveColumnButton : function (cell) {
      var removeButton = tag("button", { title : t("Remove this column.") }, "-");
      removeButton.onclick = this.removeColumn.bind(this, cell).wait(0).returning(false);
      return removeButton;
    },

    /**
     * Creates a cell with buttons for adding and removing rows.
     *
     * @return
     *   A TD with two buttons.
     */
    createColumnModificationCell : function () {
      var td = tag("td", {
        className : "column-modification-cell"
      });
      td.appendChild(this.createPrependColumnButton(td));
      td.appendChild(this.createRemoveColumnButton(td));
      td.appendChild(this.createAddColumnButton(td));
      return td;
    },

    /**
     * Adds a row of buttons for adding and removing columns.
     */
    addColumnModificationRow : function () {
      var row = tag("tr", {
        className : "column-modification-row"
      });
      var cells = [];

      // Add an empty column on the left side.
      var td = tag("td", null, null);
      row.appendChild(td);

      // Add a modification cell for every starting column of the table.
      for (var i = 0; i < this.startingColumns; i++) {
        row.appendChild(this.createColumnModificationCell(row));
      }
      this.tbody.appendChild(row);
    },

    /**
     * Creates the form and the initial table for the dialog.
     */
    createForm : function () {
      // Create the two top rows.
      var headers = [tag("th", null, t("Headers"))];
      var alignments = [tag("th", null, t("Col Align"))];
      for (var i = 0; i < this.startingColumns; i++) {
        headers.push(this.createHeaderCell());
        alignments.push(this.createAlignmentCell());
      }

      // cell for the + button.
      headers.push(tag("td"));

      // Add a tbody for all the table's contents.
      var alignmentRow = tag("tr", {
        className : "alignment-row"
      }, alignments);

      var headerRow = tag("tr", {
        className : "header-row"
      }, headers);
      this.tbody = tag("tbody", null, [
        alignmentRow,
        headerRow
      ]);

      // Add a + button to the last alignment cell.
      var removeButton = this.createRemoveRowButton(headerRow, "");
      removeButton.onclick = function () {};
      removeButton.disabled = true;
      headers[headers.length - 1].appendChild(removeButton);
      headers[headers.length - 1].appendChild(this.createAddRowButton(headerRow, t("Insert a new row above the first row.")));

      // Add all default content rows.
      for (var j = 0; j < this.startingRows; j++) {
        this.tbody.appendChild(this.createContentRow());
      }

      // Add a row with +/- buttons for adding and removing columns.
      this.addColumnModificationRow();

      // Set the table and form to instance variables.
      this.table = tag("table", null, this.tbody);
      this.fieldset = tag("fieldset", null, this.table);
      this.form = tag("form", {
        id : "markdowneditor-dialog-form"
      }, this.fieldset);
      this.setFirstAlignmentColumnBehavior();
    },

    /**
     * Sets a CSS class name for the first alignment column and removes it from
     * the second one if it exists.
     */
    setFirstAlignmentColumnBehavior : function () {
      var modificationRow = this.getColumnModificationRow();

      // Add the classname  to the first cell, and  remove it from the
      // second.
      ClassNames.add(modificationRow.cells[1], "first");
      if (modificationRow.cells [2]) {
        ClassNames.del(modificationRow.cells[2], "first");
      }
    },

    /**
     * Removes the column the given cell belongs to
     *
     * @param cell
     *   A table cell whose column is removed.
     */
    removeColumn : function (cell) {
      if (this.columns > 1) {
        // Get the index of the column to remove.
        var cellIndex = collection.indexOf(this.getColumnModificationRow().cells, cell);

        // Loop through and remove the cell on each row.
        var rows = this.table.rows;
        for (var i = 0; i < rows.length; i++) {
          rows[i].removeChild(rows[i].cells[cellIndex]);
        }
        this.columns--;

        // Disable the button if this column is the last one.
        if (this.columns === 1) {
          this.getRemoveColumnButton(this.getColumnModificationRow().cells[1]).disabled = true;
        }
      }

      this.setFirstAlignmentColumnBehavior();
    },

    /**
     * Gets the button that removes a column from a cell.
     *
     * @param cell
     *   The cell to look for the button in.
     * @return
     *   The button that removes columns.
     */
    getRemoveColumnButton : function (cell) {
      var buttons = cell.getElementsByTagName("button");
      // Fetch the button with the correct title.
      for (var i = 0; i < buttons.length; i++) {
        if (buttons[i].innerHTML === "-") {
          return buttons[i];
        }
      }
      throw new Error("No button found.");
    },

    /**
     * @return
     *   The table row containing column modification buttons.
     */
    getColumnModificationRow : function () {
      return this.table.rows[this.table.rows.length - 1];
    },

    /**
     * @return
     *   The row containing header inputs.
     */
    getHeaderRow : function () {
      return this.table.rows[1];
    },

    /**
     * @return
     *   The row containing alignment selects.
     */
    getAlignmentRow : function () {
      return this.table.rows[0];
    },

    /**
     * @return
     *   The rows containing table content inputs.
     */
    getContentRows : function () {
      var rows = [];
      // The first two  rows are headers and alignments,  and the last
      // one is +/- buttons.
      for (var i = 2; i < this.table.rows.length - 1; i++) {
        rows.push(this.table.rows[i]);
      }
      return rows;
    },

    /**
     * Adds a new column after the given cell's column.
     *
     * @param cell
     *   The column this cell belongs to will be the previous sibling of the new
     *   one.
     */
    addColumn : function (cell) {
      if (this.columns === 1) {
        this.getRemoveColumnButton(this.getColumnModificationRow().cells[1]).disabled = false;
      }

      var cellIndex = collection.indexOf(this.getColumnModificationRow().childNodes, cell);

      var previousHeaderCell = this.getHeaderRow().cells[cellIndex];
      var previousAlignmentCell = this.getAlignmentRow().cells[cellIndex];
      var previousColumnModificationCell = this.getColumnModificationRow().cells[cellIndex];


      // Insert header and alignment.
      dom.insertAfter(this.createHeaderCell(), previousHeaderCell);
      dom.insertAfter(this.createAlignmentCell(), previousAlignmentCell);

      // Insert rows.
      var contentRows = this.getContentRows();
      for (var i = 0; i < contentRows.length; i++) {
        // Offset of 2 since header cells and alignment cells are above the content cells.
        dom.insertAfter(this.createContentCell(), contentRows[i].cells[cellIndex]);
      }

      // Insert a column modification cell.
      dom.insertAfter(this.createColumnModificationCell(), previousColumnModificationCell);

      this.columns++;

      this.setFirstAlignmentColumnBehavior();
    },

    prependColumn : function (cell) {
      this.addColumn(cell.previousSibling);
    },

    // If  the   form  was  submitted,   to  prevent  the   user  from
    // accidentally submitting twice.
    submitted : false,

    /**
     * Handles submissions, extracts the data from the form and serializes and
     * inserts into the document.
     */
    submitHandler : function () {
      // Make sure only one submit is triggered.
      if (this.submitted) return;

      this.submitted = true;

      // Get data from the dialog.
      var headers = [];
      var alignments = [];


      // Fetch headers and alignments.
      var headerElements = this.table.rows[1].getElementsByTagName("input");
      var alignmentElements = this.table.rows[0].getElementsByTagName("select");
      for (var i = 0; i < headerElements.length; i++) {
        headers.push(headerElements[i].value);
        var select = alignmentElements[i];
        alignments.push(select.options[select.selectedIndex].value);
      }
      delete headerElements;
      delete alignmentElements;

      // Fetch all content.
      var contentRows = [];
      var row, rowElements;
      for (var j = 2; j < this.table.rows.length; j++) {
        row = this.table.rows[j];
        rowElements = row.getElementsByTagName("input");
        contentRows.push([]);
        for (var k = 0; k < rowElements.length; k++) {
          contentRows[contentRows.length - 1].push(rowElements[k].value);
        }
      }
      delete row;
      delete rowElements;

      // Transform into the markdown extra string.
      var tableString = "";
      var headerString = "| " + headers.join(" | ") + " |";
      var alignmentString = "";
      var rowStrings = [];
      var rowString = "";

      // Gather the header/content separators, with their alignment.
      for (var l = 0; l < headers.length; l++) {
        var dashes = string.repeat("-", Math.max(headers[l].length, 3));
        switch (alignments[l]) {
          case "none":
            // Don't modify the string.
            break;
          case "right":
            // Suffix with a colon.
            dashes = dashes.replace(/(.+)-$/, "$1:");
            break;
          case "left":
            // Prefix with a colon.
            dashes = dashes.replace(/^-(.+)/, ":$1");
            break;
          case "center":
            // Surround by colons.
            dashes = dashes.replace(/^-(.+)-$/, ":$1:");
            break;
        }
        alignmentString += "| " + dashes + " ";
      }
      alignmentString += "|";

      // Format all the content rows.
      for (var m = 0; m < this.rows; m++) {
        rowStrings.push("| " + contentRows[m].join(" | ") + " |");
      }
      // Join the rows into a string.
      rowString = rowStrings.join("\n");

      // Create the final table string.
      tableString = headerString + "\n" + alignmentString + "\n" + rowString;

      // Insert the table and close the dialog.
      markdownEditor.selection.replaceAll(tableString);
      markdownEditor.selection.lineBreak();
      markdownEditor.selection.caretAtEnd();
      BUE.dialog.close();
    },

    /**
     * Opens the dialog, appends the content and creates buttons.
     */
    openDialog : function () {
      var mDialog = markdownEditor.dialog;

      // Add buttons.
      mDialog.setOnsubmit(this.form, this.submitHandler.bind(this));
      mDialog.addSubmitButton(this.form, this.submitHandler.bind(this));
      mDialog.addCancelButton(this.form);

      // Open and setup the dialog.
      mDialog.open(t("Insert table"), "table");
      mDialog.getContent().appendChild(this.form);
      mDialog.focusFirst();

      var tmp = document.getElementById("bue-dialog");
      function f () {
        tmp.style.left = parseInt(tmp.style.left) + 1 + "px";
      }
      setTimeout(f, 1);
      setTimeout(f, 250);
    }
  };

  return Table;
})();


/*******************************************************************************
 * DEFINITION LIST
 ******************************************************************************/

/**
 * Gives the user a dynamic form in a dialog for adding definition lists.
 * Rows can be added and removed.
 */
markdownEditor.DefinitionList = (function () {
  var t = markdownEditor.t;
  var tag = Cactus.DOM.tag;
  var dom = markdownEditor.extras.dom;
  var mDialog = markdownEditor.dialog;
  var ClassNames = Cactus.DOM.ClassNames;
  var getElementByClassName = markdownEditor.extras.getElementByClassName;
  var getElementsByClassName = markdownEditor.extras.getElementsByClassName;

  function DefinitionList () {
    this.createForm();
    this.openDialog();
  } DefinitionList.prototype = {
    // Current number of rows of the dialog.
    rows : 0,
    // Default amount of rows.
    startingRows : 2,

    form : null,

    /**
     * Creates the default dialog contents.
     */
    createForm : function () {

      this.fieldset = tag("fieldset");

      this.form = tag("form", {
        id : "markdowneditor-dialog-form",
        onsubmit : this.process.bind(this).wait(0).returning(false)
      }, this.fieldset);

      this.contentContainer = tag("table", {
        className : "content-container"
      });

      this.fieldset.appendChild(this.contentContainer);

      this.contentContainer.appendChild(this.createHeaderRow());

      // Create the default content rows.
      for (var i = 0; i < this.startingRows; i++) {
        this.contentContainer.appendChild(this.createContentRow());
      }
      this.setFirstRowBehavior();

      // Add buttons.
      mDialog.addSubmitButton(this.form, this.process.bind(this));
      mDialog.addCancelButton(this.form);

    },

    createHeaderRow : function () {
      return tag("tr", {
        className : "header-row"
      }, [tag("th", {
        className : "header-cell"
      }, t("Term")), tag("th", {
        className : "header-cell"
      }, t("Definition"))]);
    },

    /**
     * Creates a content row, a div with an input and textarea representing a dt
     * and dd combo. Buttons for adding a new row and removing the current one
     * are added.
     *
     * @return
     *   The created div.
     */
    createContentRow : function () {
      var row = tag("tr", {
        className : "content-row"
      });
      // Append the different parts of the row.
      row.appendChild(this.createTitleCell());
      row.appendChild(this.createDescriptionCell());
      row.appendChild(this.createModificationCell(row));

      this.rows++;
      return row;
    },

    // The default value for title inputs.
    titleCellDefaultValue : "",

    /**
     * @return
     *   A new element holding an input for a title text.
     */
    createTitleCell : function () {
      var self = this;
      // Create  a div  with an  input, the  input's default  value is
      // cleared when it gains focus.
      return tag("td", {
        className : "title-cell"
      }, tag("input", {
        value : this.titleCellDefaultValue,
        type : "text",
        className : "title-cell-input form-text",
        onfocus : function () {
          if (this.value === self.titleCellDefaultValue) {
            this.value = "";
          }
        }
      }));
    },

    // The default value for description textareas.
    descriptionCellDefaultValue : "",

    /**
     * @return
     *   A new element holding a textarea for a definition text.
     */
    createDescriptionCell : function () {
      var self = this;
      // Create a div with a textarea, the textarea's default value is
      // cleared when the element gains focus.
      return tag("td", {
        className : "description-cell"
      }, tag("textarea", {
        value : this.descriptionCellDefaultValue,
        className : 'form-textarea',
        onfocus : function () {
          if (this.value === self.descriptionCellDefaultValue) {
            this.value = "";
          }
        }
      }));
    },

    /**
     * @param row
     *   The content row this modification cell belongs to.
     * @return
     *   A new element containing buttons for adding and removing rows.
     */
    createModificationCell : function (row) {
      return tag("td", {
        className : "modification"
      }, [
        this.createPrependRowButton(row),
        this.createRemoveRowButton(row),
        this.createAddRowButton(row)
      ]);
    },

    /**
     * Creates a button for removing the given row.
     *
     * @param row
     *   The row to remove when the button is clicked.
     * @param title
     *   An optional title for the button.
     * @return
     *   The created HTMLButtonElement.
     */
    createRemoveRowButton : function (row, title) {
      title = title || t("Remove this row.");
      return tag("button", {
        title : title,
        className : "remove-row-button",
        onclick : this.removeRow.bind(this, row).wait(0).returning(false)
      }, "-");
    },

    /**
     * Creates a button for adding a row after the given row.
     *
     * @param row
     *   The row to add new rows after.
     * @param title
     *   An optional title for the button.
     * @param append
     *   Whether the new row should be appended if row is null.
     * @return
     *   The created HTMLButtonElement.
     */
    createAddRowButton : function (row, title, append) {
      title = title || t("Insert a row below this row.");
      var addButton = tag("button", {
        title : title,
        className : "add-row-button"
      }, "+");
      addButton.onclick = this.addRow.bind(this, row, append).wait(0).returning(false);
      return addButton;
    },

    /**
     * Creates a button that inserts a row above the given one.
     *
     * @param row
     *   The row to add new rows before.
     * @param title
     *   An optional title for the button.
     * @return
     *   The created button.
     */
    createPrependRowButton : function (row, title) {
      title = title || t("Insert a row above this row.");
      var prependButton = tag("button", {
        title : title,
        className : "prepend-row-button"
      }, "+");
      prependButton.onclick = this.prependRow.bind(this, row).wait(0).returning(false);
      return prependButton;
    },

    /**
     * @return
     *   The content containers.
     */
    getContentRows : function () {
      return markdownEditor.extras.getElementsByClassName("content-row", this.form);
    },

    /**
     * @param row
     *   The row whose modification cell that's wanted.
     * @return
     *   The cell of the row that contains buttons for modifying rows.
     */
    getModificationCell : function (row) {
      return markdownEditor.extras.getElementByClassName("modification", row);
    },

    /**
     * Gets the "remove row" button for a row.
     *
     * @param row
     *   The row to get the button from.
     * @return
     *   The "remove row" button.
     */
    getRemoveButton : function (row) {
      var button = getElementByClassName("remove-row-button", this.getModificationCell(row));

      if (!button) {
        throw new Error("No button found");
      }

      return button;
    },

    /**
     * Removes the given row from the table.
     *
     * @param row
     *   The row to remove
     */
    removeRow : function (row) {
      // Only remove  a row if there  will be at least  one left after
      // the removal.
      if (this.rows > 1) {
        this.contentContainer.removeChild(row);
        this.rows--;

        if (this.rows === 1) {
          this.getRemoveButton(this.getContentRows()[0]).disabled = true;
        }
        this.setFirstRowBehavior();
      }
    },

    /**
     * Adds special behavior to the first row, and makes sure no other rows
     * still have this behavior.
     */
    setFirstRowBehavior : function () {
      var firstRowClassName = "first";
      var contentRows = this.getContentRows();

      // Set to the first element if it doesn't have the class name already.
      if (contentRows[0]) {
        ClassNames.add(contentRows[0], firstRowClassName);

        if (contentRows[1]) {
          // Remove the behavior from the second element.
          ClassNames.del(contentRows[1], firstRowClassName);
        }
      }
    },

    /**
     * Adds a content row to the dialog.
     *
     * @param previousRow
     *   The row to insert the new row after.
     * @param append
     *   If true  the row will be appended to the document, otherwise it's added
     *   after the given row, or before the first row if previousRow is null.
     */
    addRow : function (previousRow, append) {
      if (this.rows === 1) {
        this.getRemoveButton(this.getContentRows()[0]).disabled = false;
      }

      // Append if append was set.
      if (append) {
        this.contentContainer.appendChild(this.createContentRow());
      }
      // prepend if there is no previousRow.
      else if (!previousRow) {
        dom.insertBefore(this.createContentRow(), this.contentContainer.firstChild, this.contentContainer);
        this.setFirstRowBehavior();
      }
      // Otherwise insert after the previousRow.
      else {
        dom.insertAfter(this.createContentRow(), previousRow);
      }

      this.setFirstRowBehavior();
    },

    /**
     * Adds a new row above the given row
     *
     * @param nextRow
     *   The row to add the new row above.
     */
    prependRow : function (nextRow) {
      // Enable the remove row button if there is only one row, since
      // another button will be added.
      if (this.rows === 1) {
        this.getRemoveButton(this.getContentRows()[0]).disabled = false;
      }

      // Insert the new row.
      dom.insertBefore(this.createContentRow(), nextRow);

      this.setFirstRowBehavior();
    },

    /**
     * Opens the dialog and appends the contents.
     */
    openDialog : function () {
      mDialog.open(t("Insert definition list"), "definition-list");
      mDialog.getContent().appendChild(this.form);
      mDialog.focusFirst();
    },

    /**
     * Handles form submissions. Serializes the information to markdown syntax
     * and inserts it into the document.
     */
    process :  function () {
      var dts = [];
      var dds = [];

      var titleElements = getElementsByClassName("title-cell-input", this.contentContainer);
      var descriptionElements = this.contentContainer.getElementsByTagName("textarea");

      for (var i = 0; i < titleElements.length; i++) {
        // Don't add the values if both form elements are empty.
        if (!((titleElements[i].value === "" || titleElements[i].value === this.titleCellDefaultValue) && (descriptionElements[i].value === "" || descriptionElements[i].value === this.descriptionCellDefaultValue))) {
          dts.push(titleElements[i].value);
          dds.push(descriptionElements[i].value);
        }
      }

      // Serialize the information into markdown extra syntax.
      var lines = [];
      for (var j = 0; j < dts.length; j++) {
        dds[j] = ":   " + dds[j];
        lines.push(dts[j] + "\n" + dds[j].replace(/^(?!: {3})/gm, "    "));
      }

      // Insert the data and close the dialog.
      BUE.active.replaceSelection(lines.join("\n\n"));
      markdownEditor.selection.lineBreak();
      markdownEditor.selection.caretAtEnd();
      BUE.dialog.close();
    }
  };

  return DefinitionList;
}) ();


/*******************************************************************************
 * REFERENCES
 ******************************************************************************/

markdownEditor.Reference = (function () {

  /**
   * Represents a reference stored at the bottom of a markdown document.
   * A Reference consists of a type, which are Links, Abbreviatons and so on,
   * an identifier which is the actual reference name, and text which is the
   * data the reference defines.
   *
   * @param type
   *   The type of reference, Abbreviations, Links, Footnotes or Images.
   * @param identifier
   *   The name of the referenc.
   * @param text
   *   The information the reference defines.
   */
  function Reference(type, identifier, text) {
    this.type = type;
    this.identifier = identifier;
    this.text = text;
  } Reference.prototype = {

    // Instance variables.
    prefix : "[",
    infix : "]: ",
    type : null,
    identifier : null,
    text : null,

    /**
     * @return
     *   The string representation of a reference, this is the string that's
     *   valid inside markdown documents.
     */
    toString : function () {
      return this.prefix + this.identifier + this.infix + this.text;
    },

    /**
     * Setter for prefix
     *
     * @param prefix
     *   A string, the prefix of the reference string.
     */
    setPrefix : function (prefix) {
      this.prefix = prefix;
    },

    /**
     * Setter for suffix
     *
     * @param suffix
     *   The new suffix for the reference string.
     */
    setSuffix : function (suffix) {
      this.suffix = suffix;
    },

    /**
     * Checks if two references are equal, meaning all properties are equal.
     *
     * @param reference
     *   The reference to compare to.
     * @return
     *   A boolean signifying whether the references are equal.
     */
    equals : function (reference) {
      return this.type === reference.type && this.identifier === reference.identifier && this.text === reference.text;
    }
  };

  /**
   * Unserializes a reference string. Extracts the correct prefix.
   *
   * @param referenceString
   *   The serialized reference.
   * @param type
   *   The type of reference, matching the type property of a Reference.
   */
  Reference.fromString = function (referenceString, type) {
    // Matches  a  serialized  reference,  also  extracts  the  prefix
    // format.
    if (!/^(\*?\[\^?)([^\]]+)\]: ([\s\S]*)$/.test(referenceString)) {
      throw new Error("String does not match a serialized Reference.");
    }
    var reference = new Reference(type, RegExp.$2, RegExp.$3);
    reference.setPrefix(RegExp.$1);
    return reference;
  };

  return Reference;
})();

markdownEditor.references = (function () {
  var Reference = markdownEditor.Reference;
  var collection = markdownEditor.extras.collection;

  // The available reference types,  in the order they should appear
  // in the document.
  var order = ["Abbreviations", "Notes", "Links", "Images"];

  /**
   * A singleton for working with references.
   */
  function References() {

  } References.prototype = {

    // Instance variables.
    headerPrefix : "<!-- ",
    headerSuffix : " -->",
    references : null,

    /**
     * Fetches any existing references from the document.
     *
     * @return
     *   A hash where each property is a reference header and the values are
     *   arrays of References.
     */
    parseReferences : function () {
      var lines = BUE.active.getContent().split(/\r?\n|\r/);

      // Available reference types.
      var references = {
        "Abbreviations" : "",
        "Links" : "",
        "Images" : "",
        "Notes" : ""
      };
      var line;
      var currentReference = null;
      for (var i = 0; i < lines.length; i++) {
        line = lines[i];

        // If this line is a reference header.
        if (/^<!-- (\S+) -->$/.test(line)) {
          if (RegExp.$1 in references) {
            currentReference = RegExp.$1;
          }
        }
        // If this line is below  a reference header it's a reference
        // or part of one.
        else if (currentReference) {
          references[currentReference] += line + "\n";
        }

      }

      // Convert to Reference instances.
      for (var type in references) if (references.hasOwnProperty(type)) {
        // If the contents is empty,  just replace with an empty array
        // and do nothing else.
        if (references[type] === "") {
          references[type] = [];
          continue;
        }

        // Fetch all  separate references  from the stored  string, it
        // also matches multi-line references.
        var referenceStrings = references[type].match(/(?:^|\n).?\[([^\]])+\]:[^\[]+(?=^\[|\n|$)/g) || [];
        references[type] = [];

        // Loop  through each  reference string  and convert  it  to a
        // Reference.
        for (var j = 0; j < referenceStrings.length; j++) {
          references[type].push(Reference.fromString(referenceStrings[j].replace(/\n+$/, "").replace(/^\n+/, ""), type));
        }
      }

      this.references = references;
      return references;
    },

    /**
     * Gets all references of a given type.
     *
     * @param type
     *   The reference type.
     * @return
     *   An Array of References.
     */
    getReferences : function (type) {
      return this.parseReferences()[type];
    },

    /**
     * Checks if the given reference is stored under the given type. The
     * match is attempted using only the identifier.
     *
     * @param reference
     *   The reference to look for.
     * @return
     *   Boolean signifying whether the reference exists.
     */
    hasReference : function (reference) {
      var references = this.getReferences(reference.type);
      for (var i = 0; i < references.length; i++) {
        if (references[i].identifier === reference.identifier) {
          return true;
        }
      }
      return false;
    },

    /**
     * Checks if the given header exists in the document.
     *
     * @param header
     *   The string name of the header.
     * @return
     *   Whether the header exists in the document.
     */
    hasHeader : function (header) {
      return new RegExp(this.headerPrefix + header + this.headerSuffix).test(BUE.active.getContent());
    },

    /**
     * Prints the references into the textarea.
     *
     *   The reference type.
     * @param references
     *   An Array of reference lines.
     */
    _printReferences : function (references) {
      // Remove existing references.
      this._clearReferences();
      var textArea = BUE.active.textArea;

      // Replace trailing line breaks with three line breaks.
      textArea.value = textArea.value.replace(/\n+$/, "\n\n\n");

      // Loop through all reference headers.
      for (var i = 0; i < order.length; i++) {
        var iReferences = this.references[order[i]];
        // If the reference header exists and has References under it.
        if (iReferences && iReferences.length > 0) {
          // Insert the header.
          textArea.value += this.headerPrefix + order[i] + this.headerSuffix + "\n";
          // Loop through references and insert.
          for (var j = 0; j < iReferences.length; j++) {
            textArea.value += iReferences[j].toString() + "\n";
          }

          textArea.value += "\n\n";
        }
      }

      // Make sure all headers have 3 line breaks in front of them.
      for (var k = 0; k < order.length; k++) {
        if (this.hasHeader(order[k])) {
          BUE.active.setContent(BUE.active.getContent().replace(new RegExp("\n*(?=<!-- " + order[k] + " -->)"), "\n\n"));
        }
      }

      // Truncate trailing white space.
      textArea.value = textArea.value.replace(/[\s\n\r]*$/, "\n");
    },

    /**
     * Removes all reference data from the document.
     */
    _clearReferences : function () {
      var content = BUE.active.getContent();
      var index;

      // Try to find the index of the first header.
      for (var i = 0; i < order.length; i++) {
        index = content.search(new RegExp(this.headerPrefix + order[i] + this.headerSuffix));
        if (index >= 0) {
          break;
        }
      }

      // If no reference data exists, nothing needs to be done.
      if (index == -1) return;

      // Set the content to itself but excluding the references.
      BUE.active.setContent(content.substring(0, index));
    },

    /**
     * Replaces a reference with the new specified text. This function
     * modifies the textarea.
     *
     * @type reference
     *   The Reference to replace to.
     */
    _replaceReference : function (reference) {
      var references = this.getReferences(reference.type);
      for (var i = 0; i < references.length; i++) {
        if (reference.identifier === references[i].identifier) {
          references[i].text = reference.text;
        }
      }

      this._printReferences(reference.type, references);
    },

    /**
     * Adds the reference at the end of the content area for its type.
     *
     * @param reference
     *   The reference to add to the textarea.
     */
    _pushReference : function (reference) {
      var references = this.getReferences(reference.type);
      references.push(reference);
      this._printReferences(reference.type, references);
    },

    /**
     * Adds a reference at the appropriate location in the textarea.
     *
     * @param reference
     *   The reference to add.
     */
    addReference : function (reference) {

      var references = this.getReferences(reference.type);

      if (this.hasReference(reference)) {
        this._replaceReference(reference);
      }
      else {
        this._pushReference(reference);
      }
    },

    /**
     * Tries to find a reference matching the given identifier from the
     * BUE.
     *
     * @param type
     *   A reference type.
     * @param identifier
     *   The identifier to look for.
     * @return
     *   The found Reference, or null if none is found.
     */
    getByIdentifier : function (type, identifier) {
      // Get the references of the given type and loop through them.
      var references = this.getReferences(type);
      for (var i = 0; i < references.length; i++) {
        if (references[i].identifier === identifier) {
          // Found the reference.
          return references[i];
        }
      }

      // Nothing was found.
      return null;
    },

    /**
     * Callback for adding references to the textarea.
     * If the reference exists the user is prompted for whether the reference
     * should be overwritten.
     *
     * @param textString
     *   The text string to add at the current selection. This text is the
     *   markup refering to the reference.
     * @param reference
     *   The reference to attempt to add.
     * @param allowOverride
     *   Whether the reference may override another reference with the same
     *   identifier. Default value is true.
     * @param message
     *   The message to display if the reference already exists. Has default
     *   values.
     * @param space
     *   Whether spaces should be added around the inserted text if it's next
     *   to non-whitespace characters. Defaults to true.
     */
    _callback : function (textString, reference, allowOverride, message, space) {
      var references = markdownEditor.references;
      var t = markdownEditor.t;

      // Only evaluates to false if allowOverride === false.
      allowOverride = !!allowOverride;
      space = !!(space === undefined || space);
      message = message || null;

      // Store the current selection, since it may be modified before we read it
      // otherwise.
      var selection = BUE.active.posSelection();

      // If there exists a reference by this name already.
      if (references.hasReference(reference)) {
        // If the user may choose to overwrite the existing reference.
        if (allowOverride) {
          // If the  reference doesn't  match the existing  one, and
          // the user does not want to overwrite it.
          if (!references.getByIdentifier(reference.type, reference.identifier).equals(reference) && !confirm(message || t("There is a reference with this name, should it be overwritten?"))) {
            // Let the user modify the dialog further.
            return;
          }
        }
        // The  user may not  modify an  existing reference,  an error
        // message is sent  and the user is returned  to the dialog to
        // modiy the reference name.
        else {
          markdownEditor.dialog.addError(message || t("There is an existing reference by that name, you may not modify it. Please choose a different name for the reference."));
          return;
        }
      }

      // Add the reference to the BUE.
      references.addReference(reference);

      // Select the initial selection, replace data and close the dialog.
      BUE.active.makeSelection(selection.start, selection.end);

      BUE.active.replaceSelection(textString);
      if (space) {
        markdownEditor.selection.space();
      }
      BUE.dialog.close();
    }

  };

  return new References();
})();
