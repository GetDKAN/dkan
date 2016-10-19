## DKAN Dataset REST API

DKAN Dataset REST API uses the <a href="https://www.drupal.org/project/services">Services</a> module to create CRUD endpoint at ``api/dataset/node``. By default, this endpoint provides full CRUD access to a website's content nodes, and limited access to users (to allow authentication). The endpoint can be customized at ``/admin/structure/services/list/dkan_dataset_api/resources``.

### Documentation
The DKAN Dataset API module is only a light wrapper around the <a href="https://www.drupal.org/project/services">Services module</a>, which has extensive documentation. Here are some entries of interest:

* <a href="https://www.drupal.org/node/783722">Testing Resources</a>
* <a href="https://www.drupal.org/node/1354202">Identifying field names</a>
* <a href="https://www.drupal.org/node/1827698">Using REST Server with 2-Legged OAuth Authentication (Example with Java Servlet)</a>
* <a href="http://tylerfrankenstein.com/code/drupal-services-csrf-token-firefox-poster">Services CSRF Token with FireFox Poster</a>

The Sessions module also [has a thriving community on the Drupal Stack Exchange](http://drupal.stackexchange.com/questions/tagged/services).

### Server Types
DKAN Dataset REST API comes with a REST Server. Other server types are also incldued in the Services module but not turned on. Those include:

* OAUTH
* XML-RPC

### Repsonse Formats

* bencode
* json
* jsonp
* php
* xml
* yaml

### Request Parser Types
* application/json
* application/vnd.php.serialized
* application/x-www-form-urlencoded
* application/x-yaml
* application/xml
* multipart/form-data
* text/xml

### Authentication Modes

#### Session Authentication
Session authentication is enabled by default. With session authentication an inital request is made to the user login to requet a session cookie. That session cookie is then stored locally and sent with a request in the X-CSRF-Token header to authenticate the request.

#### Token Authentication
Token authenticaion is not currently available out of the box. However, it can be enabled by adding the <a href="https://www.drupal.org/project/services_token_access">Services Token Access</a> module to your site. This is less secure but is easier for community members to use, and may be added to the DKAN distribution in a future release.

#### Authentication Permissions
The permissions with which a user is granted depend on the user role. User roles and permissions are easily configured in the user administration screen at ``admin/people``, and DKAN comes with a number of pre-configured default roles via the [DKAN Permissions](https://github.com/NuCivic/dkan/tree/7.x-1.x/modules/dkan/dkan_permissions) module.

### Examples

Below you can find examples in PHP for the most common use cases, using session authentication.

For an example of a fully-functional python-based client to the DKAN REST API, see the [pydkan](https://github.com/NuCivic/pydkan) project.

#### How to Log In and get the Session Cookie

```
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
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json')); // Accept JSON response.
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
```

#### How to get the CSRF Token

```
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
```

#### How to create a Resource

```
// Setup request URL.
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
```

#### How to attach a file to a resource

```
// Setup request URL.
$request_url = 'http://example.com/api/dataset/node/' . $resource_id . '/attach_file';

// Setup file data.
$file_data = array(
    'files[1]' => curl_file_create($file),
    'field_name' => 'field_upload',
    'attach' => 1
);

// Setup request.
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
```

#### How to create a Dataset

```
// Setup request URL.
$request_url = 'http://example.com/api/dataset/node';

// Setup dataset data.
// A great explanation on how to target each node field can be found on the 'Identifying field names' article linked on the 'Documentation' section.
$dataset_data = array(
    'type' => 'dataset',
    'title' => 'Example dataset',
    'status' => 1,
    'body[und][0][value]' => 'The description',
    'field_resources[und][0][target_id]' => 'Madison Polling Places (5)' // Resource title plus node id
    'field_author[und][0][value]' => 'Bob Lafollette'
);
$dataset_data = http_build_query($dataset_data);

// Setup request.
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
```

### Terminal Command Examples
If you just want to quickly test that the funcitonality is working you can run the following commands from a terminal.
#####Replace the IP and PORT values in the commands below to match your development environment.

#### Users
##### Authentication (login)

```sh
curl -X POST -i -H "Content-type: application/json" -H "Accept: application/json" -c cookies.txt -X POST http://192.168.99.100:32770/api/dataset/user/login -d '{
  "username":"admin",
  "password":"admin"
}'
```

This will return the cookie and the **CSRF token** that we need to reuse for all
the authenticated user iteration via the API.


#### Content (Datasets, resource)

##### Retrive dataset

```sh
curl http://192.168.99.100:32770/api/dataset/node/43.json
```

Example output:
```json
{"vid":"82","uid":"1","title":"A node created with services 3.x and REST server","log":"","status":"1","comment":"0","promote":"0","sticky":"0","vuuid":"d7ffecfd-a1e2-4f33-8528-9768f3e838c0","nid":"43","type":"dataset","language":"und","created":"1473173658","changed":"1473173658","tnid":"0","translate":"0","uuid":"55350015-d8fa-4861-96f7-78b808f39dac","revision_timestamp":"1473173658","revision_uid":"1","body":{"und":[{"value":"This should be the description","summary":"","format":"html","safe_value":"<p>This should be the description</p>\n","safe_summary":""}]},"field_additional_info":[],"field_author":[],"field_contact_email":[],"field_contact_name":[],"field_data_dictionary":[],"field_frequency":[],"field_granularity":[],"field_license":{"und":[{"value":"notspecified","format":null,"safe_value":"notspecified"}]},"field_modified_source_date":[],"field_public_access_level":[],"field_related_content":[],"field_resources":[],"field_spatial":[],"field_spatial_geographical_cover":[],"field_tags":[],"field_temporal_coverage":[],"og_group_ref":[],"field_topic":[],"rdf_mapping":{"rdftype":["sioc:Item","foaf:Document"],"title":{"predicates":["dc:title"]},"created":{"predicates":["dc:date","dc:created"],"datatype":"xsd:dateTime","callback":"date_iso8601"},"changed":{"predicates":["dc:modified"],"datatype":"xsd:dateTime","callback":"date_iso8601"},"body":{"predicates":["content:encoded"]},"uid":{"predicates":["sioc:has_creator"],"type":"rel"},"name":{"predicates":["foaf:name"]},"comment_count":{"predicates":["sioc:num_replies"],"datatype":"xsd:integer"},"last_activity":{"predicates":["sioc:last_activity_date"],"datatype":"xsd:dateTime","callback":"date_iso8601"}},"path":"http://192.168.99.100:32770/dataset/node-created-services-3x-and-rest-server-1","name":"admin","picture":"0","data":"b:0;"}
```

##### Create new dataset
This will need an authenticated user with appropriate permissions. The headers
includes the user credentials (cookie and CSRF token).

```sh
curl -X POST -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt -X POST http://192.168.99.100:32770/api/dataset/node -d '{
  "title":"A node created via DKAN REST API",
  "type":"dataset",
  "body": {
    "und": [{"value": "This should be the description"}]
  }
}'
```

##### Update dataset title
To update content we use the PUT HTTP method.

```sh
curl -X PUT -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt http://192.168.99.100:32770/api/dataset/node/43 -d '{
  "title":"A node created with services 3.x and REST server - UPDATED"
}'
```

##### Update each POD field
Updating POD fields should be the same with a slight variation depending on the
field type.

```sh
curl -X PUT -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt http://192.168.99.100:32770/api/dataset/node/43 -d '{
  "field_odfe_bureau_code": {"und": {"value": "001:12"}}
}'
```

##### Update field isChild(collection) to parent UID
Updating the isPartOf field (`field_odfe_is_part_of`) should be the similar to
updating any other regular field.

##### Add new resource to dataset
API wise this is a 2 step process:

1. Create the resource node.
  ```sh
  curl -X POST -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt -X POST http://192.168.99.100:32770/api/dataset/node -d '{
  "title":"A resource created via DKANs REST APIs",
  "type":"resource",
  "body": {"und": [{"value": "This should be the description for the resource."}]},
  "field_link_api": {"und": [{"url": "http://data.worldbank.org/"}]}
  }'
  ```

2. Attach the newly created resource node to the parent dataset. Use the node ids that match the dataset and resource created by the commands above.
  ```sh
  curl -X PUT -i -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt http://192.168.99.100:32770/api/dataset/node/43 -d '{
  "field_resources": {"und": [{"target_id": "A resource create via DKANs REST APIs (45)"}]}
  }'
  ```
Note that the provided value (`A resource create via DKANs REST APIs (45)`) is
the value expected from the dataset entry form.

##### Query for url/values of previous revision of file.
The assumption is that the file is stored remotely and we are looking to get the
link as it was set in a previous revision of the resource node. 

Version (revision) are tracked via the VID Durpal identifier. We can query a
specific node revision (for example version id 89) using the vid as parameter:

```sh
curl -X GET -gi -H "Content-type: application/json" -H "X-CSRF-Token: 8RniaOCwrsK8Mvue0al_C6EMAraTg26jzklDdLLgvns" -b cookies.txt 'http://192.168.99.100:32770/api/dataset/node.json?parameters[vid]=89'
```
