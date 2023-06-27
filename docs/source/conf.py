# Configuration file for the Sphinx documentation builder.
#
# This file only contains a selection of the most common options. For a full
# list see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Path setup --------------------------------------------------------------

# If extensions (or modules to document with autodoc) are in another directory,
# add these directories to sys.path here. If the directory is relative to the
# documentation root, use os.path.abspath to make it absolute, like shown here.
#
import os
import sys
#sys.path.insert(0, os.path.abspath('../../modules/'))
#sys.path.append(os.path.abspath('..'))


# -- Project information -----------------------------------------------------

project = 'DKAN Documentation'
copyright = '2023, CivicActions'
author = 'CivicActions'

# The full version, including alpha/beta/rc tags
release = '2.15.2'
version = '2.x'

# -- General configuration ---------------------------------------------------

# Add any Sphinx extension module names here, as strings. They can be
# extensions coming with Sphinx (named 'sphinx.ext.*') or your custom
# ones.
# sphinxcontrib-phpdomain is not compatible with myst.
extensions = [
  'myst_parser',
  'sphinx.ext.autosectionlabel',
  'sphinxcontrib.httpdomain',
  'sphinx_rtd_theme'
]

# Add any paths that contain templates here, relative to this directory.
templates_path = ['_templates']

# List of patterns, relative to source directory, that match files and
# directories to ignore when looking for source files.
# This pattern also affects html_static_path and html_extra_path.
exclude_patterns = ['_build', 'Thumbs.db', '.DS_Store']


# -- Options for HTML output -------------------------------------------------

# The theme to use for HTML and HTML Help pages.  See the documentation for
# a list of builtin themes.
#
html_theme = 'sphinx_rtd_theme'

# Add any paths that contain custom static files (such as style sheets) here,
# relative to this directory. They are copied after the builtin static files,
# so a file named "default.css" will overwrite the builtin "default.css".
html_static_path = ['_static']
html_css_files = ['custom.css']
html_logo = '_static/logo-white.svg'
html_favicon = '_static/favicon.ico'

# Set up PHP syntax highlights
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer
lexers["php"] = PhpLexer(startinline=True, linenos=1)
lexers["php-annotations"] = PhpLexer(startinline=True, linenos=1)

# The suffix of source filenames.
source_suffix = {
    '.rst': 'restructuredtext',
    '.md': 'markdown',
}
source_parsers = {'.md': 'myst_parser.sphinx_'}

autosectionlabel_prefix_document = True
autosectionlabel_maxdepth=None
