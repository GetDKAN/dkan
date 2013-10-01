Markdown filter Drupal module
=============================

Provides Markdown filter integration for Drupal input formats. The
Markdown syntax is designed to co-exist with HTML, so you can set up
input formats with both HTML and Markdown support. It is also meant to
be as human-readable as possible when left as "source".

There are many different Markdown implementation. Markdown filter uses
"PHP Markdown extra" that includes many common and useful extensions to
the original Markdown. This includes tables, footnotes and definition
lists.

Read more about Markdown at:

* Original Markdown Syntax by John Gruber
  <http://daringfireball.net/projects/markdown/syntax>
* PHP Markdown Extra by Michel Fortin
  <http://michelf.ca/projects/php-markdown/extra/>


Markdown editor:
---------------

If you are interested in a Markdown editor please check out
the Markdown editor for BUEditor module.

<http://drupal.org/project/markdowneditor>


Important note about running Markdown with other input filters:
--------------------------------------------------------------

Markdown may conflict with other input filters, depending on the order
in which filters are configured to apply. If using Markdown produces
unexpected markup when configured with other filters, experimenting with
the order of those filters will likely resolve the issue.

Filters that should be run before Markdown filter includes:

* Code Filter
* GeSHI filter for code syntax highlighting

Filters that should be run after Markdown filter includes:

* Typogrify

The "Limit allowed HTML tags" filter is a special case:

For best security, ensure that it is run after the Markdown filter and
that only markup you would like to allow in via HTML and/or Markdown is
configured to be allowed via the it.

If you on the other hand want to make sure that all converted Markdown
text is perserved, run it after the Markdown filter. Note that
blockquoting with Markdown doesn't work when run after "Limit allowed
HTML tags". It converts the ">" in to "&gt;".


Smartypants support:
-------------------

This module is a continuation of the Markdown with Smartypants module.
It only includes Markdown support and it is now suggested that you use
Typogrify module if you are interested in Smartypants support.

<http://drupal.org/project/typogrify>


Installation:
------------
1. Download and unpack the Markdown module directory in your modules folder
   (this will usually be "sites/all/modules/").
2. Go to "Administer" -> "Modules" and enable the module.
3. Set up a new text format or add Markdown support to an text format at
   Administer -> Configuration -> Content Authoring -> Text formats


Credits:
-------
Markdown created                     by John Gruber: <http://daringfireball.net>
PHP executions                       by Michel Fortin: <http://www.michelf.com/>
Drupal filter originally             by Noah Mittman: <http://www.teradome.com/>
