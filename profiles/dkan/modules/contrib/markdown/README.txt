Markdown filter Drupal module
================================================================================

Provides Markdown filter integration for Drupal input formats. The Markdown
syntax is designed to co-exist with HTML, so you can set up input formats with
both HTML and Markdown support. It is also meant to be as human-readable as
possible when left as "source".

This module is a continuation of the Markdown with Smartypants module (at
http://drupal.org/project/marksmarty), and only includes Markdown support
to simplify configuration. It is now suggested that you use Tipogrify module
(see http://drupal.org/project/typogrify) if you are interested in Smartypants
support.

Note that if you use the GeSHI filter for code syntax highlighting, arrange
this filter after that one.

For more information on Markdown, read:

 - http://daringfireball.net/projects/markdown/syntax
 - http://michelf.com/projects/php-markdown/extra/

Quickstart
--------------------------------------------------------------------------------

1. Move the entire "markdown" directory into your Drupal installation's
   sites/all/modules folder (or your site specific directory).

2. Enable the module on Administer >> Site building >> Modules

3. Set up a new input format or add Markdown support to an existing format at
   Administer >> Site configuration >> Input formats

4. For best security, ensure that the HTML filter is after the Markdown filter
   on the "Reorder" page of the input format and that only markup you would
   like to allow in via HTML and/or Markdown is configured to be allowed via the
   HTML filter.

Credits
--------------------------------------------------------------------------------
Markdown created                     by John Gruber: <http://daringfireball.net>  
PHP executions                       by Michel Fortin: <http://www.michelf.com/>  
Drupal filter originally             by Noah Mittman: <http://www.teradome.com/>  
