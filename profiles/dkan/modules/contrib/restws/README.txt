
--------------------------------------------------------------------------------
                 RESTful Web Services for Drupal (restws)
--------------------------------------------------------------------------------

Maintainers:
 * Wolfgang Ziegler (fago), wolfgang.ziegler@epiqo.com
 * Klaus Purer (klausi), klaus.purer@epiqo.com

Exposes Drupal resources (e.g. entities) as RESTful web services. The module
makes use of the Entity API and the information about entity properties
(provided via hook_entity_property_info()) to provide resource representations.
It aims to be fully compliant to the REST principles.

Installation
------------

 * Copy the whole restws directory to your modules directory
   (e.g. DRUPAL_ROOT/sites/all/modules) and activate the RESTful Web Services
   module.
 * There is no user interface or such needed.

Usage / Testing
---------------

 * To obtain the JSON representation of an entity use your browser to visit

   http://example.com/node/1.json or
   http://example.com/user/1.json or
   http://example.com/<entity type name>/<entity id>.json

   for an example.
   
 * In order to access entities via this interface, permissions must be granted
   for the desired operation (e.g. "access content" or "create content" for
   nodes). Additionally each resource is protected with a RESTWS permission
   that can be configured at "admin/people/permissions#module-restws".

 * Some example outputs are given in the example_exports folder.


Design goals and concept
------------------------

 * Create a module that simply exposes Drupal's data (e.g. entities) as web
   resources, thus creating a simple RESTful web service. It aims to be fully
   compliant to the REST principles.

 * The module is strictly resource-oriented. No support for message-oriented or
   RPC-style web services.

 * Plain and simple. No need for endpoint configuration, all resources are
   available on the same path pattern. Access via HTTP only.

 * When the module is enabled all entities should be available at the URL path
   /<entity type name>/<entity id>, e.g. /node/123, /user/1 or /profile/789.

 * Resources are represented and exchanged in different formats, e.g. JSON or
   XML. The format has to be specified in every request.
   
 * The module defines resource for all entity types supported by the entity API
   as well as a JSON format. Modules may provide further resources and formats
   via hooks.

 * The module supports full CRUD (Create, Read, Update, Delete) for resources:
 
     * Create: HTTP POST /<entity type name> (requires HTTP Content-Type header
       set to the MIME type of <format>)

     * Read: HTTP GET /<entity type name>/<entity id>.<format>

     * Update: HTTP PUT /<entity type name>/<entity id>.<format>
       or      HTTP PUT /<entity type name>/<entity id> (requires HTTP
       Content-Type header set to the MIME type of the posted format)

     * Delete: HTTP DELETE /<entity type name>/<entity id>

      Note: if you use cookie-based authentication then you also need to set the
      HTTP X-CSRF-Token header on all writing requests (POST, PUT and DELETE).
      You can retrieve the token from /restws/session/token with a standard HTTP
      GET request.

 * The representation <format> can be json, xml etc.

 * The usual Drupal permission system is respected, thus permissions are checked
   for the logged in user account of the received requests. 

 * Authentication can be achieved via separate modules, maybe making use of the
   standard Drupal cookie and session handling. The module comes with an
   optional HTTP Basic Authentication module (restws_auth_basic) that performs
   a user login with the credentials provided via the usual HTTP headers.

Querying
--------
The module also supports querying for resources:

  Query: HTTP GET /<entity type name>.<format>?<filter>=<value1>&
  <meta_control>=<value2>

By default RestWS simply outputs all resources available for the given type:

/user.json

The example above will output a JSON object containing up to 100 users
available in an sub object called list. The XML output will simply create
tags with the given type in parent type, which also is called list. The hard
limit of 100 resources per request ensures that the database and webserver
won't overload. The hard limit is defined by the system variable
restws_query_max_limit, which can be overridden if necessary.

You can filter for certain resources by passing parameters in the URL. These
parameters consist of properties of the resource and a value for that property.
The value of a property is always the schema field of it. So if you want to
filter for an author, the value of the filter has to be the uid. If you want to
filter for nodes with a certain term, then you have to use the tid of that term.
You can only specify one value, so filtering for more than one tag, is currently
not supported.

/node.json?type=article&field_tags=17&author=1

By default the first field column will be used for the query. If you want
another column, you can specify it in square brackets.

/node.json?body[format]=filtered_html

If a certain property isn't valid an HTTP status code 412 will be returned
containing an error message.

Meta Controls
-------------

Additionally to the filters RestWS also supports meta controls, which allow you
to control your output. Currently only sort and direction are supported.
This two meta controls allow you to sort your output by a specific property of
your resource for a certain direction. By default the direction will be
ascending but if want to sort your output descending you have to use the keyword
DESC for the meta control direction.

/taxonomy_term.json?sort=tid&direction=DESC

You can limit the results with the meta control limit which is by default 100.
To navigate through the generated pages, you have to use meta control page.

/node.json?limit=10&page=3

The output always has a self, a first and a last element, which contain a link
to the current, first and last page. If your current page isn't the last or the
first one, RestWS will also generate prev and next links. For xml they can be
found in the tags <link /> in the first hierarchy.

Sometimes it might be helpful to retrieve only the references to resources of a
query. You can tell RestWS to output them by setting the meta control full to 0,
by default it will be 1 and output the whole resources.

/node.json?full=0

If one of your resource properties collides with one of RestWS meta control
keywords, you have prefix it with property_, when specifying it as filter.

Debugging
---------

You can enable a debug logging facility by setting a variable in settings.php
(e.g. in DRUPAL_ROOT/sites/default/settings.php):

$conf['restws_debug_log'] = DRUPAL_ROOT . '/restws_debug.log';

It will write the details of every web service request to the file you have
specified, e.g. to DRUPAL_ROOT/restws_debug.log.
