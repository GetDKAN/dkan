# Database and file backups

## Restoring a database dump or site files

DKAN Tools' `restore` commands can restore from a local or remote dump of the database, as well as restore a files archive. This simplest way to do this is:

```bash
dktl dkan:restore --db_url=<path_to_db> --files_url=<path_to_files>
```

As described below, these options can be stored in a configuration file so that you can simply type `dktl restore`.

You may also restore from a local database backup, as long as it is placed in a folder under the project root called _/backups_. Type `dktl db:restore` with no argument, and the backup in _/backups_ will be restored if there is only one, or you will be allowed to select from a list if there are several.


## Configuring DKTL commands

You will probably want to set up some default arguments for certain commands, especially the urls for the `restore` command. This is what the dkan.yml file is for. You can provide options for any DKTL command in dkan.yml. For instance:

```yaml
command:
  restore:
    options:
      db_url: "s3://my-backups-bucket/my-db.sql.gz"
      files_url: "s3://my-backups-bucket/my-files.tar.gz"
```

If you include this in your dktl.yml file, typing `dktl restore` without any arguments will load these two options.


## Create and grab a database dump excluding tables

You can create a database dump excluding tables related to cache, devel, webform submissions and DKAN datastore. Running the command `dktl site:grab-database @alias` will create the database backup for the drush alias passed as argument.

This command needs to be run with DKTL_MODE set to "HOST". So you'll need to run `export DKTL_MODE="HOST"` and after the command finishes, you should set it back to its old value or just unset the variable by running `unset DKTL_MODE`.

If you want to import this dump into your local development site, then you can move the file _excluded\_tables.sql_ into the directory _backups_ in the root of your project, then you'll be able to import it by running `dktl restore:db excluded_tables.sql`.
