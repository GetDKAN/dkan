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
Response Formats
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

****************
Request Examples
****************

Below you can find examples in PHP for a basic set of CRUD operations on datasets and resources. This documentation is a work in progress. The examples are raw HTTP requests, with a short example of how to execute a query in PHP as well.

For an example of a fully-functional python-based client to the DKAN REST API, see the `pydkan <https://github.com/NuCivic/pydkan>`_ project.

Login
=====

Request
-------

.. code-block:: bash

  POST http://docker:32774/api/dataset/user/login
  Accept: application/json
  Content-Length: 29
  Content-Type: application/x-www-form-urlencoded

  {
    "username": "admin",
    "password": "admin"
  }

Response
--------

.. code-block:: json

  {
      "sessid": "OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk",
      "session_name": "SESSd14344a17ca11d13bda8baf612c0efa5",
      "token": "C2dfCUcN4hjgt6u2Xmv15mc1nkj5uv1Iqpa8QE3d7E8",
      "user": {
          "access": "1492546345",
          "created": "1488377334",
          "data": false,
          "field_about": [],
          "init": "admin@example.com",
          "language": "",
          "login": 1492546519,
          "mail": "admin@example.com",
          "name": "admin",
          "og_user_node": {
              "und": [
                  {
                      "target_id": "1"
                  },
                  {
                      "target_id": "2"
                  },
                  {
                      "target_id": "3"
                  }
              ]
          },
          "picture": null,
          "roles": {
              "2": "authenticated user"
          },
          "signature": "",
          "signature_format": null,
          "status": "1",
          "theme": "",
          "timezone": "UTC",
          "uid": "1",
          "uuid": "5eb4da39-cfba-4d43-bb45-691ebcde8f70"
      }
  }

Retrieve session token
======================

Request
-------

.. code::

  POST http://docker:32774/services/session/token
  Accept: application/json
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk
  Content-Length: 0

Response
--------

.. code::

  XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4

Create dataset
==============

Request
-------

.. code-block:: bash

  POST http://docker:32774/api/dataset/node
  Content-Type: application/json
  Accept: application/json
  X-CSRF-Token: XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk
  Content-Length: 44

  {
    "type": "dataset",
    "title": "Test Dataset"
  }

Response
--------

.. code-block:: json

  {
      "nid": "75",
      "uri": "http://docker:32774/api/dataset/node/75"
  }

Create resource
===============

Request
-------

.. code-block:: bash

  POST http://docker:32774/api/dataset/node
  Content-Type: application/json
  Accept: application/json
  X-CSRF-Token: XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk
  Content-Length: 97

  {
    "type": "resource",
    "field_dataset_ref": {"und": {"target_id": "75"}},
    "title": "Test Resource"
  }

Response
--------

.. code-block:: json

  {
      "nid": "76",
      "uri": "http://docker:32774/api/dataset/node/76"
  }

Retrieve parent dataset to check resource ID
============================================

Request
-------

.. code-block:: bash

  GET http://docker:32774/api/dataset/node/75
  Accept: application/json
  X-CSRF-Token: XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk

Response
--------

.. code-block:: json

  {
      "body": [],
      "changed": "1492544349",
      "comment": "0",
      "created": "1492544348",
      "data": "b:0;",
      "field_additional_info": [],
      "field_author": [],
      "field_conforms_to": [],
      "field_contact_email": [],
      "field_contact_name": [],
      "field_data_dictionary": [],
      "field_data_dictionary_type": [],
      "field_frequency": [],
      "field_granularity": [],
      "field_harvest_source_ref": [],
      "field_is_part_of": [],
      "field_landing_page": [],
      "field_language": [],
      "field_license": {
          "und": [
              {
                  "format": null,
                  "safe_value": "notspecified",
                  "value": "notspecified"
              }
          ]
      },
      "field_modified_source_date": [],
      "field_orphan": {
          "und": [
              {
                  "value": "0"
              }
          ]
      },
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
                  "target_id": "76"
              }
          ]
      },
      "field_rights": [],
      "field_spatial": [],
      "field_spatial_geographical_cover": [],
      "field_tags": [],
      "field_temporal_coverage": [],
      "field_topic": [],
      "language": "und",
      "log": "",
      "name": "admin",
      "nid": "75",
      "og_group_ref": [],
      "path": "http://docker:32774/dataset/test-dataset-16",
      "picture": "0",
      "promote": "0",
      "revision_timestamp": "1492544349",
      "revision_uid": "1",
      "status": "1",
      "sticky": "0",
      "title": "Test Dataset",
      "tnid": "0",
      "translate": "0",
      "type": "dataset",
      "uid": "1",
      "uuid": "d53881b3-d80f-49c2-8815-897321fe926e",
      "vid": "117",
      "vuuid": "c4663ada-0162-4780-8ee5-347c6c037429"
  }

.. note::

  The correct resource ID, `76`, has been added to `field_resources`.

Update dataset
==============

Request
-------

.. code-block:: bash

  PUT http://docker:32774/api/dataset/node/75
  Content-Type: application/json
  Accept: application/json
  X-CSRF-Token: XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk
  Content-Length: 34

  {"title": "Updated dataset title"}

Response
--------

.. code-block:: json

  {
      "nid": "75",
      "uri": "http://docker:32774/api/dataset/node/75"
  }

Add a file to a resource
========================

Request
-------

.. code-block:: bash

  POST http://docker:32774/api/dataset/node/76/attach_file
  Accept: application/json
  X-CSRF-Token: XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk
  Content-Length: 474
  Content-Type: multipart/form-data; boundary=5f8b431c36a24c828044cd876b3aa669

  --5f8b431c36a24c828044cd876b3aa669
  Content-Disposition: form-data; name="attach"

  0
  --5f8b431c36a24c828044cd876b3aa669
  Content-Disposition: form-data; name="field_name"

  field_upload
  --5f8b431c36a24c828044cd876b3aa669
  Content-Disposition: form-data; name="files[1]"; filename="tension_sample_data.csv"

  tension,current,timestamp
  220,10,2016-05-27T19:56:41.870Z
  50,15,2016-05-27T19:51:21.794Z
  230,10,2016-05-27T19:56:41.870Z
  --5f8b431c36a24c828044cd876b3aa669--

.. note::

  Setting the ``attach`` parameter to ``0`` ensures that the file will replace any existing file on the resource. Setting it to `1` will result in a rejected request if the resource already has an attached file (but it will work if the resource's file upload field is empty).


Response
--------

.. code-block:: json

  {
    "fid": "38",
    "uri": "http://docker:32774/api/dataset/file/38"
  }


Retrieve the resource to check the file field
=============================================

Request
-------

.. code-block:: bash
  GET http://docker:32774/api/dataset/node/76
  Accept: application/json
  X-CSRF-Token: XBWI44XD33XBIANLpyK-rtvRa0N5OcaC03qLx0VQsP4
  Cookie: SESSd14344a17ca11d13bda8baf612c0efa5=OBoeXKMQx3zmaZrS_v3FOP7_Ze66fYA61TGhtm9s0Qk

  None

  .. note::

    We still use the `/api/dataset` endpoint to retrieve a resource node (or any other type of node) by nid.

Response
--------

.. code-block:: json

  {
      "body": [],
      "changed": "1492544350",
      "comment": "0",
      "created": "1492544349",
      "data": "b:0;",
      "field_dataset_ref": {
          "und": [
              {
                  "target_id": "75"
              }
          ]
      },
      "field_datastore_status": {
          "und": [
              {
                  "value": "2"
              }
          ]
      },
      "field_format": {
          "und": [
              {
                  "tid": "32"
              }
          ]
      },
      "field_link_api": [],
      "field_link_remote_file": [],
      "field_orphan": {
          "und": [
              {
                  "value": "0"
              }
          ]
      },
      "field_upload": {
          "und": [
              {
                  "alt": "",
                  "delimiter": null,
                  "embed": null,
                  "fid": "38",
                  "filemime": "text/csv",
                  "filename": "tension_sample_data.csv",
                  "filesize": "120",
                  "graph": null,
                  "grid": null,
                  "map": null,
                  "metadata": [],
                  "service_id": null,
                  "status": "1",
                  "timestamp": "1492544350",
                  "title": "",
                  "type": "undefined",
                  "uid": "1",
                  "uri": "public://tension_sample_data.csv",
                  "uuid": "87019111-7ef0-48e5-b8a4-ea2c392f2e56"
              }
          ]
      },
      "language": "und",
      "log": "",
      "name": "admin",
      "nid": "76",
      "og_group_ref": [],
      "path": "http://docker:32774/dataset/updated-dataset-title/resource/34b45055-bc10-416f-a8ba-8b9f27718362",
      "picture": "0",
      "promote": "0",
      "revision_timestamp": "1492544350",
      "revision_uid": "1",
      "status": "1",
      "sticky": "0",
      "title": "Test Resource",
      "tnid": "0",
      "translate": "0",
      "type": "resource",
      "uid": "1",
      "uuid": "34b45055-bc10-416f-a8ba-8b9f27718362",
      "vid": "118",
      "vuuid": "ac1c1aa3-f6ee-4f76-a2f9-6510d9504680"
  }

**************
Testing it out
**************

Command Line (curl)
===================

If you want to quickly test that the functionality is working, you can use curl to send requests a terminal.

Authentication
--------------

.. code-block:: bash

  curl -X POST -i -H "Content-type: application/json" -H "Accept: application/json" -c cookies.txt -X POST http://docker:32774/api/dataset/user/login -d '{
    "username":"admin",
    "password":"password"
  }'


This will return the cookie and the **CSRF token** that we need to reuse for all
the authenticated user iteration via the API.

Create a new dataset
--------------------

This will need an authenticated user with appropriate permissions. The headers
include the user credentials (cookie and CSRF token).

.. code-block:: bash

  curl -X POST -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt -X POST http://docker:32774/api/dataset/node -d '{
    "title":"A node created via DKAN REST API",
    "type":"dataset",
    "body": {
      "und": [{"value": "This should be the description"}]
    }
  }'

In a PHP script
===============

Log In and get the Session Cookie
---------------------------------

.. code-block:: php

  // Setup request URL.
  $request_url = 'http://docker:32774/api/dataset/user/login';

  // Prepare user data.
  $user_data = array(
      'username' => 'theusername',
      'password' => 'theuserpassword',
  );
  $user_data = http_build_query($user_data);

  // Set up request.
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
------------------

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

  Create a Dataset
  ----------------

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

Python client
=============

Be sure to look at the `pydkan Python client <https://github.com/NuCivic/pydkan>`_ to see a working API client you can build on for your own applications.

Safe FME Integration
====================

Building on the pydkan client, the `FME Workflows <https://github.com/NuCivic/fme_dkan_apis_workflows>`_ repo provides code and instructions for integrating DKAN into `Safe FME <https://www.safe.com/>`_ workflows.

************
Known issues
************

  * Datasets and other content nodes can only be queried via node id or other entity. UUID support pending.
  * There is currently no way to request a previous revision of a dataset or resource.
  * Upon attaching a file to a resource via the API, DKAN will immediately import this file to the Datastore if it is a valid CSV. This may not always be the desired behavior; more control over datastore behavior should be available to API clients.
