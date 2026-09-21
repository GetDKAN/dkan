Documentation
=============

What follows is a style guide for the DKAN documentation. Use it both to follow the conventions used throughout the site,
and for your own contributions. DKAN's documentation is written in a combination of `Markdown <https://daringfireball.net/projects/markdown>`_
and `ReStructuredText (RST) <http://www.sphinx-doc.org/en/stable/rest.html>`_, and built with the `Sphinx documentation generator <http://www.sphinx-doc.org/en/stable/index.html>`_.
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
Terminal commands should be expressed in a sphinx prompt block:

  .. code-block:: restructuredtext

    .. prompt:: bash $

      drush cim -y
      drush cr


Which will render like this:

  .. prompt:: bash $

    drush cim -y
    drush cr


Code blocks
^^^^^^^^^^^^^^^^^
Code blocks are expressed as... code blocks:

  .. code-block:: restructuredtext

    ..code-block:: php

      /**
       * Your php code here
       */

to produce something like this:

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


Architectural Decision Records
------------------------------

Major architectural decisions are documented in the :ref:`adr` section of the docs.
The easiest way to add a new ADR is with the `adr-tools <https://github.com/npryce/adr-tools>`_ command line utility. Install it locally and then run the following command from the root of the repository:

  .. prompt:: console $

    adr new "Title of your ADR"

Building these docs
-------------------
If you contribute significantly to this documentation, at some point you will want build them locally
to preview your formatting and other markup. This will require some degree of comfort with command-line tools but is
otherwise fairly straightforward.

Sphinx
^^^^^^
`Sphinx`_ is the Python tool that generates the HTML from the documentation RST
files. 

.. seealso::

    Refer to the `Sphinx installation instructions <https://www.sphinx-doc.org/en/master/usage/installation.html>`_
    for more information if the instructions here are not sufficient.

To build Sphinx documentation, you will need to have a `Python 3 <https://docs.python-guide.org/>`_ 
runtime installed locally. You can also install Sphinx and the other dependencies
(listed in ``docs/requirements.txt``) at the local system level, but we recommend
using a Python virtual environment:

.. prompt:: console $

    cd docs
    python3 -m venv .venv
    source .venv/bin/activate

You will now see a ``(.venv)`` prefix in your terminal, indicating that the
virtual environment is active. You can deactivate it at any time by running the
``deactivate`` command.

Now that your virtual environment is active, install the dependencies. Make sure you are in the `/docs` directory:

  .. prompt:: console $

    pip install -r requirements.txt

If you need to activate your virtual environment again in the future, just run
the ``source .venv/bin/activate`` command again from the `/docs` directory. The
dependencies only need to be installed once, but the virtual environment is only
active for the current terminal session.

Now you should be able to build the Sphinx site by typing

  .. prompt:: console $

    make html

.. tip::

  Depending on your local environment, the tools installed by pip may not be available in the make process' ``$PATH``.
  If the ``make`` command produces an error like

    .. code-block:: console

      /bin/sh: line 1: sphinx-build: command not found

  you can pass it the full ``sphinx-build`` command explicitly like this:

    .. code-block:: console

      SPHINXBUILD=$(which sphinx-build) make html

The documentation will build in ``docs/build/html``, you can then open the
``docs/build/html/index.html`` file in a browser to preview your changes.

Sometimes changes to indexes are not picked up very well. If you see issues with the sidebar
table of contents, delete the ``docs/build`` directory by running:

  .. prompt:: console $

    make clean

Autobuilding
^^^^^^^^^^^^

You can have the documentation automatically rebuild when you make
changes to the source files using the ``sphinx-autobuild`` command 
instead of ``make``.

First, install ``sphinx-autobuild`` in your virtual environment (we have not 
added it as an explicit dependency in ``requirements.txt``):

  .. prompt:: console $

    pip install sphinx-autobuild

Then run the following command from the `/docs` directory:

  .. prompt:: console $

    sphinx-autobuild ./source ./build/html

The process will stay attached to your terminal. You should see a link to a
local server where you can now see the live documentation, usually at http://127.0.0.1:8000.
Whenever you make changes to the source files, the documentation will
automatically rebuild and refresh in the browser.