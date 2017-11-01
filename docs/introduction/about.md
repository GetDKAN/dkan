# About this documentation

What follows is a style guide for the DKAN documentation. Use it both to follow the conventions used throughout the site, and for your own contributions. DKAN's docs are written in a combination of [Markdown](https://daringfireball.net/projects/markdown) (specifiically, [CommonMark](http://commonmark.org/)) and [ReStructuredText (RST)](http://www.sphinx-doc.org/en/stable/rest.html), and built with [Sphynx](http://www.sphinx-doc.org/en/stable/index.html). The docs live in the `/docs` folder of the [DKAN Project](https://github.com/GetDKAN/dkan); to suggest modifications, submit a pull request as you would for any suggested code change.

## File types

Index files should always be in RST, to render correctly in the sidebar when built. Additional files can be in markdown or RST format depending on your preference. Currently, most DKAN documentation is in Markdown, mainly for historical reasons.

In some cases, `README.md` files are pulled into the docs site from elsewhere in the repository. This is accomplished with symbolic links in the docs folder.

## Images

Screenshots should be taken at standard desktop resolution (no retina!) and avoid showing any browser chrome. If necessary they may contain arrows and annotations in red with sans-serif typeface.

## Text conventions

### Modules

Module names are written in Title Case with no additional styling. Quotes can be used if needed for clarity -- for instance, it might be confusing to talk about how the "Data" module affects data on the site without quote marks. When possible, a module name is linked to its home page (on Drupal.org or Github) on its first mention in a page.

### Entities and bundles

A specific content type or other entity bundle is written in italics, as in referring to a `dataset` node or a `chloropleth` visualization. Entity types, like "node," require no additional styling.

### Files

Filenames are written as inline code as in this example: `thisfile.txt` will do the trick.

### Terminal commands

Terminal commands should be expressed in a full code block, with each line starting with$:

```bash
$ first -i "run" this-command
$ ../then.this --one
```

### Code blocks

Code blocks are also expressed as... code blocks:

```php
/**
 * Adds declared endpoint to list.
 *
 * This and hook_open_data_schema_map_load() are necessary so that modules can
 * declare more than one endpoint.
 */
function hook_open_data_schema_map_endpoints_alter(&$records) {
  $records[] = 'my_machine_name';
}
```

### Code objects
When referring to **`$variables`**, **`function_names()`** and **`classNames`** inline, use bold inline code style. This can be achieved in markdown like this:

```
**`This text`** will be code-styled and bold
```

## Building this documentation

If you contribute significantly to this documentation, at some point you will want to be able to build them locally to preview your formatting and other markup. This will require some degree of comfort with command-line tools but is otherwise fairly straightforward.

### Sphinx

`Sphinx <http://www.sphinx-doc.org/en/1.5.1/>`_ is the Python application that generates the HTML from the documentation markup.

To work on sphinx documentation locally, install the Sphinx Python tools. This requires having the `easy_install` tool in your environment.

Install pip (the python package manager):

```bash
$ sudo easy_install pip
```

Then install sphinx

```bash
$ sudo pip install sphinx
```

Install the dependencies for this project. Make sure you are in the `/docs` directory:

```bash
$ cd docs
$ sudo pip install -r requirements.txt
```

Now you should be able to build the Sphinx site by typing

```bash
$ make html
```

The site will build in `_build/html`

### Auto-build server

If you install the `sphinx-autobuild package` with pip, you can run a server that will build automatically when it senses a file change, and refresh your browser. Run

```bash
$ sudo pip install sphinx-autobuild
```

...then, from the /docs directory, run:

```bash
$ sphinx-autobuild ./ _build/html
```

The autobuild tool sometimes does not pick up changes to indexes very well. If you see issues with the sidebar table of contents, stop the server, delete the `/_build` directory and then re-start the server:

```bash
$ rm -rf _build && sphinx-autobuild ./ _build/html
```
