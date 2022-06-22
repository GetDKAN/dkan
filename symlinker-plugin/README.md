# Symlinker Plugin

A Composer plugin to specify paths to be symlinked to each other.

Designed to be used alongside Drupal Composer Scaffold, to organize your project
directory so that parts of your project which you need are in one place.

## What?

Symlinks are specified in the composer.json file, similar to the way they are
specified in the Drupal Composer Scaffold plugin.

The scaffold plugin can not make symlinks to directories. The symlinker plugin
can.

## Why?

Some projects may wish to have a source directory outside of the docroot. This
necessitates a strategy where we can symlink elements of the source directory
into places within the docroot.

## How?

You can specify locations within the project, and you can specify source and
destination links.

These configurations are all designed to mimic the Drupal Composer Scaffold
configuration.

If this plugin is used with the Drupal Composer Scaffold project, it will
attempt to use the `locations` specified by that configuration.

Here is a sample configuration:
```json
        "symlinker-plugin": {
            "locations": {
                "project-root": ".",
                "web-root": "docroot/"
            },
            "file-mapping": {
                "[web-root]/libraries": "[project-root]/src/libraries",
                "[web-root]/modules/custom": "[project-root]/src/modules",
                "[web-root]/sites/default": "[project-root]/src/site",
                "[web-root]/schema": "[project-root]/src/schema",
                "[web-root]/themes/custom": "[project-root]/src/themes"
            }
        },
```

File mappings are specified as `"destination": "source"`

## The Rules

### Locations, locations, locations

As mentioned above, symlinker plugin will try to glean the `locations`
configuration from the Drupal Composer Scaffold plugin if that config
is available.

If `locations` is defined within both sets of configuration, and they are
different, then an error will be thrown and whatever Composer process you're
engaged in will cease. The `locations` config should either be identical for
both, or removed from `symlinker-plugin`.

### File Mappings

If a symlink already exists for a specified file mapping, then no action will
be taken.

If the destination already exists and is not a symlink, then the symlinker
plugin will offer you the option to copy the contents of that directory to
the source directory, and then make the symlink. This process uses the
Symfony filesystem `mirror()` method, which synchronizes the contents, rather
than overwriting.

### Use The Commmand

This process can be commanded to occur, as follows:
```shell
composer makesymlinks
```
