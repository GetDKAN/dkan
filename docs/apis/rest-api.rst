#####################
Dataset REST API
#####################

The `DKAN Dataset REST API <https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_dataset/modules/dkan_dataset_rest_api>`_ uses the `Services <https://www.drupal.org/project/services>`_ module to create CRUD endpoint at ``api/dataset/node``. By default, this endpoint provides full CRUD access to a website's content nodes, and limited access to users (to allow authentication). The endpoint can be customized at ``/admin/structure/services/list/dkan_dataset_api/resources``.

**********************
Services Documentation
**********************

The DKAN Dataset API module is only a light wrapper around the `Services module <https://www.drupal.org/project/services>`_, which has extensive documentation. Here are some entries of interest:

* `Testing Resources <https://www.drupal.org/node/783722>`_
* `Identifying field names <https://www.drupal.org/node/1354202>`_
* `Using REST Server with 2-Legged OAuth Authentication (Example with Java Servlet) <https://www.drupal.org/node/1827698>`_
* `Services CSRF Token with FireFox Poster <http://tylerfrankenstein.com/code/drupal-services-csrf-token-firefox-poster>`_

The Sessions module also `has a thriving community on the Drupal Stack Exchange <http://drupal.stackexchange.com/questions/tagged/services>`_.

************
Server Types
************

DKAN Dataset REST API comes with a REST Server. Other server types are also incldued in the Services module but not turned on. Those include:

* OAUTH
* XML-RPC

****************
Repsonse Formats
****************

* bencode
* json
* jsonp
* php
* xml
* yaml

********************
Request Parser Types
********************

* application/json
* application/vnd.php.serialized
* application/x-www-form-urlencoded
* application/x-yaml
* application/xml
* multipart/form-data
* text/xml

********************
Authentication Modes
********************

Session Authentication
======================

Session authentication is enabled by default. With session authentication an inital request is made to the user login to request a session cookie. That session cookie is then stored locally and sent with a request in the X-CSRF-Token header to authenticate the request.

Token Authentication
====================

Token authenticaion is not currently available out-of-the-box. However, it can be enabled by adding the `Services Token Access <https://www.drupal.org/project/services_token_access>`_ module to your site. This is less secure but is easier for community members to use, and may be added to the DKAN distribution in a future release.

Authentication Permissions
==========================

The permissions with which a user is granted depend on the user role. User roles and permissions are easily configured in the user administration screen at ``admin/people``, and DKAN comes with a number of pre-configured default roles via the `DKAN Permissions <https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_permissions>`_ module.

********
Examples
********

Below you can find examples in PHP for the most common use cases, using session authentication.

For an example of a fully-functional python-based client to the DKAN REST API, see the `pydkan <https://github.com/NuCivic/pydkan>`_ project.

Log In and get the Session Cookie
=================================

.. code-block:: php

  // Setup request URL.
  $request_url = 'http://example.com/api/dataset/user/login';

  // Prepare user data.
  $user_data = array(
      'username' => 'theusername',
      'password' => 'theuserpassword',
  );
  $user_data = http_build_query($user_data);

  // Setup request.
  $curl = curl_init($request_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json')); 

  // Accept JSON response.
  curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $user_data); // Set POST data.
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

  // Execute request and get response.
  $response = curl_exec($curl);

  // Process response.
  $logged_user = json_decode($response);

  // Save cookie session to be used on future requests.
  $cookie_session = $logged_user->session_name . '=' . $logged_user->sessid;

Get the CSRF Token
==================

.. code-block:: php

  // Setup request URL.
  $request_url = 'http://example.com/services/session/token';

  // Setup request.
  $curl = curl_init($request_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json')); // Accept JSON response.
  curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session"); // Send the cookie session that we got after login.
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

  // Execute request and save CSRF Token.
  $csrf_token = curl_exec($curl);

Create a Resource
=================

.. code-block:: php

  // Set up request URL.
  $request_url = 'http://example.com/api/dataset/node';

  // Setup resource data.
  // A great explanation on how to target each node field can be found on the 'Identifying field names' article linked on the 'Documentation' section.
  $resource_data = array(
      'type' => 'resource',
      'title' => 'Example resource',
      'status' => 1,
      'body[und][0][value]' => 'The description'
  );
  $resource_data = http_build_query($resource_data);

  // Setup request.
  $curl = curl_init($request_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'X-CSRF-Token: ' . $csrf_token));
  curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $resource_data); // Set POST data.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

  // Execute request and get response.
  $response = curl_exec($curl);

Attach a file to a resource
===========================

.. code-block:: php

  // Set up request URL.
  $request_url = 'http://example.com/api/dataset/node/' . $resource_id . '/attach_file';

  // Setup file data.
  $file_data = array(
      'files[1]' => curl_file_create($file),
      'field_name' => 'field_upload',
      'attach' => 1
  );

  // Set up request.
  $curl = curl_init($request_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data','Accept: application/json', 'X-CSRF-Token: ' . $csrf_token));
  curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $file_data); // Set POST data.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

  // Execute request and get response.
  $response = curl_exec($curl);

Create a Dataset
================

.. code-block:: php

  // Set up request URL.
  $request_url = 'http://example.com/api/dataset/node';

  // Set up dataset data.
  // A great explanation on how to target each node field can be found on the 'Identifying field names' article linked on the 'Documentation' section.
  $dataset_data = array(
      'type' => 'dataset',
      'title' => 'Example dataset',
      'status' => 1,
      'body[und][0][value]' => 'The description',
      'field_resources[und][0][target_id]' => 'Madison Polling Places (5)', // Resource title plus node id
      'field_author[und][0][value]' => 'Bob Lafollette'
  );
  $dataset_data = http_build_query($dataset_data);

  // Set up request.
  $curl = curl_init($request_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'X-CSRF-Token: ' . $csrf_token));
  curl_setopt($curl, CURLOPT_POST, 1); // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $dataset_data); // Set POST data.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);

  // Execute request and get response.
  $response = curl_exec($curl);

***********************
Testing in the terminal 
***********************

If you want to quickly test that the functionality is working, you can run the following commands from a terminal.

Replace the domain, username and password in the commands below to match your development environment, and then replace the token value with the token from the response to the authentication request.

Users
=====

Authentication (login)
----------------------

.. code-block:: bash

  curl -X POST -i -H "Content-type: application/json" -H "Accept: application/json" -c cookies.txt -X POST http://demo.getdkan.com/api/dataset/user/login -d '{
    "username":"admin",
    "password":"password"
  }'


This will return the cookie and the **CSRF token** that we need to reuse for all
the authenticated user iteration via the API.


Content (Datasets, resource)
============================

Retrive dataset
---------------

.. code-block:: bash

  curl http://demo.getdkan.com/api/dataset/node/22.json

Example response:

.. code-block:: json

  {
    "vid": "52",
    "uid": "1",
    "title": "Wisconsin Polling Places",
    "log": "Update to resource 'Madison Polling Places'",
    "status": "1",
    "comment": "0",
    "promote": "0",
    "sticky": "0",
    "vuuid": "30daa43f-aa4a-477a-b011-047ce3d5007e",
    "nid": "22",
    "type": "dataset",
    "language": "und",
    "created": "1360541580",
    "changed": "1477369101",
    "tnid": "0",
    "translate": "0",
    "uuid": "934400f2-a5dc-4abf-bf16-3f17335888d3",
    "revision_timestamp": "1477369101",
    "revision_uid": "1",
    "body": {
      "und": [
        {
          "value": "<p>Polling places in the state of Wisconsin.</p>\n",
          "summary": null,
          "format": "html",
          "safe_value": "<p>Polling places in the state of Wisconsin.</p>\n",
          "safe_summary": ""
        }
      ]
    },
    "field_additional_info": [],
    "field_author": {
      "und": [
        {
          "value": "Wisconsin Board of Elections",
          "format": null,
          "safe_value": "Wisconsin Board of Elections"
        }
      ]
    },
    "field_conforms_to": [],
    "field_contact_email": {
      "und": [
        {
          "value": "datademo@nucivic.com",
          "format": null,
          "safe_value": "datademo@nucivic.com"
        }
      ]
    },
    "field_contact_name": {
      "und": [
        {
          "value": "Couch, Aaron",
          "format": null,
          "safe_value": "Couch, Aaron"
        }
      ]
    },
    "field_data_dictionary": [],
    "field_data_dictionary_type": [],
    "field_frequency": {
      "und": [
        {
          "value": "5"
        }
      ]
    },
    "field_granularity": [],
    "field_harvest_source_ref": [],
    "field_is_part_of": [],
    "field_landing_page": [],
    "field_language": [],
    "field_license": {
      "und": [
        {
          "value": "cc-by",
          "format": null,
          "safe_value": "cc-by"
        }
      ]
    },
    "field_harvest_source_issued": [],
    "field_harvest_source_modified": [],
    "field_pod_theme": [],
    "field_public_access_level": {
      "und": [
        {
          "value": "public"
        }
      ]
    },
    "field_related_content": [],
    "field_resources": {
      "und": [
        {
          "target_id": "4"
        }
      ]
    },
    "field_rights": [],
    "field_spatial": {
      "und": [
        {
          "wkt": "POLYGON ((-90.415429 46.568478, -90.229213 46.508231, -90.119674 46.338446, -89.09001 46.135799, -88.662808 45.987922, -88.531362 46.020784, -88.10416 45.922199, -87.989145 45.796229, -87.781021 45.675736, -87.791975 45.500474, -87.885083 45.363551, -87.649574 45.341643, -87.742682 45.199243, -87.589328 45.095181, -87.627666 44.974688, -87.819359 44.95278, -87.983668 44.722749, -88.043914 44.563917, -87.928898 44.536533, -87.775544 44.640595, -87.611236 44.837764, -87.403112 44.914442, -87.238804 45.166381, -87.03068 45.22115, -87.047111 45.089704, -87.189511 44.969211, -87.468835 44.552964, -87.545512 44.322932, -87.540035 44.158624, -87.644097 44.103854, -87.737205 43.8793, -87.704344 43.687607, -87.791975 43.561637, -87.912467 43.249452, -87.885083 43.002989, -87.76459 42.783912, -87.802929 42.493634, -88.788778 42.493634, -90.639984 42.510065, -90.711184 42.636034, -91.067185 42.75105, -91.143862 42.909881, -91.176724 43.134436, -91.056231 43.254929, -91.204109 43.353514, -91.215062 43.501391, -91.269832 43.616407, -91.242447 43.775238, -91.43414 43.994316, -91.592971 44.032654, -91.877772 44.202439, -91.927065 44.333886, -92.233773 44.443425, -92.337835 44.552964, -92.545959 44.569394, -92.808852 44.750133, -92.737652 45.117088, -92.75956 45.286874, -92.644544 45.440228, -92.770513 45.566198, -92.885529 45.577151, -92.869098 45.719552, -92.639067 45.933153, -92.354266 46.015307, -92.29402 46.075553, -92.29402 46.667063, -92.091373 46.749217, -92.014696 46.705401, -91.790141 46.694447, -91.09457 46.864232, -90.837154 46.95734, -90.749522 46.88614, -90.886446 46.754694, -90.55783 46.584908))",
          "geo_type": "polygon",
          "lat": "44.635",
          "lon": "-90.0142",
          "left": "-92.8855",
          "top": "46.9573",
          "right": "-87.0307",
          "bottom": "42.4936",
          "srid": null,
          "accuracy": null,
          "source": null
        }
      ]
    },
    "field_spatial_geographical_cover": {
      "und": [
        {
          "value": "Wisconsin, United States",
          "format": null,
          "safe_value": "Wisconsin, United States"
        }
      ]
    },
    "field_tags": {
      "und": [
        {
          "tid": "9"
        }
      ]
    },
    "field_temporal_coverage": [],
    "og_group_ref": {
      "und": [
        {
          "target_id": "1"
        }
      ]
    },
    "field_topic": [],
    "field_orphan": {
      "und": [
        {
          "value": "0"
        }
      ]
    },
    "rdf_mapping": {
      "rdftype": [
        "sioc:Item",
        "foaf:Document"
      ],
      "title": {
        "predicates": [
          "dc:title"
        ]
      },
      "created": {
        "predicates": [
          "dc:date",
          "dc:created"
        ],
        "datatype": "xsd:dateTime",
        "callback": "date_iso8601"
      },
      "changed": {
        "predicates": [
          "dc:modified"
        ],
        "datatype": "xsd:dateTime",
        "callback": "date_iso8601"
      },
      "body": {
        "predicates": [
          "content:encoded"
        ]
      },
      "uid": {
        "predicates": [
          "sioc:has_creator"
        ],
        "type": "rel"
      },
      "name": {
        "predicates": [
          "foaf:name"
        ]
      },
      "comment_count": {
        "predicates": [
          "sioc:num_replies"
        ],
        "datatype": "xsd:integer"
      },
      "last_activity": {
        "predicates": [
          "sioc:last_activity_date"
        ],
        "datatype": "xsd:dateTime",
        "callback": "date_iso8601"
      }
    },
    "path": "http://demo.getdkan.com/dataset/wisconsin-polling-places",
    "name": "admin",
    "picture": "0",
    "data": "b:0;"
  }

Create a new dataset
--------------------

This will need an authenticated user with appropriate permissions. The headers
include the user credentials (cookie and CSRF token).

.. code-block:: bash

  curl -X POST -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt -X POST http://demo.getdkan.com//api/dataset/node -d '{
    "title":"A node created via DKAN REST API",
    "type":"dataset",
    "body": {
      "und": [{"value": "This should be the description"}]
    }
  }'

Update dataset title
--------------------

To update content we use the PUT HTTP method. This will add the word "UPDATED" to the title:

.. code-block:: bash

  curl -X PUT -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt http://demo.getdkan.com//api/dataset/node/22 -d '{
    "title":"A node created with services 3.x and REST server - UPDATED"
  }'

Update a dataset field
----------------------

Titles are a core property for content in Drupal. Updating additional content-type-specific fields requires a slightly more complex data structure. To update a dataset's frequency, for instance:

.. code-block:: bash

  curl -X PUT -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt http://demo.getdkan.com/api/dataset/node/22 -d '{
    "field_frequency": {"und":{"value": 6}}
  }'


Because the REST API runs input through the dataset node form for validation, the data structure may differ for different fields. For instance, because it is a "Select or license" field, the structure for changing the License field on a dataset to "cc-nc" (Creative Commons Non-Commercial) would be: 

.. code-block:: json

  {
    "field_license": {"und": {"select": {"value": "cc-nc"}}}
  }


See the `Services documentation on custom fields <https://www.drupal.org/node/1354202>`_ for more detailed information.

Add new resource to dataset
---------------------------

This is a two-step process with the API:

1. Create the resource node:

.. code-block:: bash

  curl -X POST -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt -X POST http://demo.getdkan.com/api/dataset/node -d '{
    "title":"A resource created via the DKAN REST API",
    "type":"resource",
    "body": {"und": [{"value": "This should be the description for the resource."}]},
    "field_link_api": {"und": [{"url": "http://data.worldbank.org/"}]}
  }'


2. Attach the newly created resource node to the parent dataset. Use the node ids that match the dataset and resource created by the commands above.

.. code-block:: bash

  curl -X PUT -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt http://demo.getdkan.com/api/dataset/node/43 -d '{
    "field_resources": {"und": [{"target_id": "A resource created via the DKAN REST APIs (45)"}]}
  }'


.. note::

  The provided value (`A resource created via the DKAN REST API (45)`) is the value expected from the dataset entry form, with "45" being the resource node id.

Query for url/values of previous revision of file.
--------------------------------------------------

The assumption in this example is that the file is stored remotely and we are looking to get the link as it was set in a previous revision of the resource node. 

Versions/revisions are tracked via Durpal's ``vid`` identifier. We can query a specific node revision (for example `vid` 89) using the vid as parameter

.. code-block:: bash

  curl -X GET -gi -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt 'http://demo.getdkan.com/api/dataset/node.json?parameters[vid]=89'

Known issues
------------

  * Datasets and other content nodes can only be queried via node id or other entity. UUID support pending.
  * Upon attaching a file to a resource via the API, DKAN will immediately import this file to the Datastore if it is a valid CSV. This may not always be the desired behavior; more control over datastore behavior should be available to API clients.
