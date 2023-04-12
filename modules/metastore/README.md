# Metastore

The metastore's job is to ingest data in JSON format, validate the JSON data against a JSON Schema, and to store the valid data.

As we take ownership of the management of these data, the metastore also provides ways to retrieve, update and delete the data.

## Entrypoints

There are 3 main ways to interact with the metastore module:
- Drush commands
- Endpoints
- Services

### Drush commands
To see the Drush commands that are made available by this module, look at the Drupal::metastore::Commands::MetastoreCommands class.

### Endpoints
To see the endpoints that are made available by this module, check the metastore.routing.yml file.

### Services
For development, we want to interact with the code/classes directly. Drupal's dependency injection mechanism makes it fairly easy to get a fully-built class/service injected into your own classes in your own module. To see the services available reference the metastore.services.yml file.
