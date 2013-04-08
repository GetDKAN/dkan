
--------------------------------------------------------------------------------
                 RESTful Web Services for Drupal (restws)
--------------------------------------------------------------------------------

Example Exports
---------------
This folder contains some example outputs of RestWS. Note that the output
depends on the permissions and the field access of the viewing user. Nodes and
users are used as examples here, but the structure is the same for all entity
types.

Here is a list of the outputs with the URL paths:

- Retrieve a node

  /node/1.json
  node.1.json

  /node/1.xml
  node.1.xml

- Get a list of nodes with a limit of 3 nodes per page and display the second
  page (the first is page=0).

  /node.json?limit=3&page=1
  node.json

  /node.xml?limit=3&page=1
  node.xml

- Retrieve a user

  /user.json
  user.1.json

  /user.xml
  user.1.xml
