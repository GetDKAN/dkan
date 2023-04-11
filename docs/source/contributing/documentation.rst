Contributing to DKAN documentation
==================================

What follows is a style guide for the DKAN documentation. Use it both to follow the conventions used throughout the site,
and for your own contributions. DKAN's documentaion is written in a combination of `Markdown <https://daringfireball.net/projects/markdown>`_
and `ReStructuredText (RST) <http://www.sphinx-doc.org/en/stable/rest.html>`_, and built with `Sphynx <http://www.sphinx-doc.org/en/stable/index.html>`_.
The docs live in the `/docs/source` folder of the `DKAN Project <https://github.com/GetDKAN/dkan>`_; to suggest modifications,
submit a pull request as you would for any suggested code change.

File types
----------

Index files should always be in RST, to render correctly in the sidebar when built. Additional files can be in markdown
or RST format depending on your preference.

In some cases, `README.md` files are pulled into the docs site from elsewhere in the repository. This is accomplished
with an include directive.

  .. code-block:: restructuredtext

      .. include:: ../../../path/to/README.md
        :parser: myst_parser.sphinx_

Images
------

Screenshots should be taken at standard desktop resolution (no retina!) and avoid showing any browser chrome.
If necessary they may contain arrows and annotations in red with sans-serif typeface.
DKAN team to post files to S3. External teams can submit images attached to the PR.

Text conventions
----------------
Modules
^^^^^^^
Module names are written in Title Case with no additional styling. Quotes can be used if needed for clarity --
for instance, it might be confusing to talk about how the "Data" module affects data on the site without quote marks.
When possible, a module name is linked to its home page (on Drupal.org or Github) on its first mention in a page.

Files
^^^^^
Filenames are written as inline code as in this example: ``thisfile.txt`` will do the trick.

Terminal commands
^^^^^^^^^^^^^^^^^
Terminal commands should be expressed in a full code block:

  .. code-block:: console

    drush cim -y
    drush cr


Code blocks
^^^^^^^^^^^^^^^^^
Code blocks are also expressed as... code blocks:

  .. code-block:: php

    /**
    * Adds declared endpoint to list.
    *
    * This and hook_open_data_schema_map_load() are necessary so that modules can
    * declare more than one endpoint.
    */
    function hook_open_data_schema_map_endpoints_alter(&$records) {
      $records[] = 'my_machine_name';
    }


Code objects
^^^^^^^^^^^^^^^^^
When referring to **`$variables`**, **`function_names()`** and **`classNames`** inline, use bold inline code style.
This can be achieved in markdown like this:

  .. code-block:: restructuredtext

    **`This text`** will be code-styled and bold


Building this documentation
---------------------------
If you contribute significantly to this documentation, at some point you will want to be able to build them locally
to preview your formatting and other markup. This will require some degree of comfort with command-line tools but is
otherwise fairly straightforward.

Sphinx
^^^^^^
`Sphinx <http://www.sphinx-doc.org/en/1.5.1/>`_ is the Python application that generates the HTML from the documentation markup.

To work on Sphinx documentation locally, you will need to install `Python3 <https://docs.python-guide.org/>`_.

Then follow the `Sphinx installation instructions <https://www.sphinx-doc.org/en/master/usage/installation.html>`_ that match your
local platform.

Install the dependencies for this project. Make sure you are in the `/docs` directory:

  .. code-block:: console

    cd docs
    pip install -r requirements.txt

Now you should be able to build the Sphinx site by typing

  .. code-block:: console

    make html

.. tip::

  Depending on your local environment, the tools installed by pip may not be available in the make process' PATH.
  If the ``make`` command produces an error like

    .. code-block:: console

      /bin/sh: line 1: sphinx-build: command not found

  you can pass it the full ``sphinx-build`` command explicitly like this:

    .. code-block:: console

      SPHINXBUILD=$(which sphinx-build) make html

The documentation will build in `docs/build/html`, you can then open the
`dkan/docs/build/html/index.html` file in a browser to preview your changes.


Sometimes changes to indexes are not picked up very well. If you see issues with the sidebar
table of contents, delete the `docs/build` directory by running:

  .. code-block:: console

    make clean
