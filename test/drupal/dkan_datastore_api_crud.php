<?php
/**
 * @file
 * Functions for DKAN Datastore CRUD.
 */

/**
 * Initiates curl request.
 */
function dkan_datastore_services_curl_init($request_url, $csrf_token = FALSE) {
  // cURL.
  $curl = curl_init($request_url);
  if ($csrf_token) {
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'X-CSRF-Token: ' . $csrf_token));
  }
  else {
    // Accept JSON response.
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
  }
  // Ask to not return Header.
  curl_setopt($curl, CURLOPT_HEADER, FALSE);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
  // Withtout the next line I get cURL errors.
  return $curl;
}

/**
 * Initiates curl request.
 */
function dkan_datastore_services_curl_parse($curl) {
  $response = curl_exec($curl);
  $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if ($http_code == 200) {
    $response = json_decode($response);
  }
  else {
    $http_message = curl_error($curl);
    die($http_message);
  }
  return $response;
}

/**
 * Logs in user.
 */
function dkan_datastore_services_user_login($username, $password, $request_url) {
  // User data.
  $user_data = array(
    'username' => $username,
    'password' => $password,
  );
  $user_data = http_build_query($user_data);

  $curl = dkan_datastore_services_curl_init($request_url);
  // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_POST, 1);
  // Set POST data.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $user_data);

  $logged_user = dkan_datastore_services_curl_parse($curl);

  // Define cookie session.
  $cookie_session = $logged_user->session_name . '=' . $logged_user->sessid;
  return array('cookie_session' => $cookie_session, 'curl' => $curl);
}

/**
 * Retrives CSRF token.
 */
function dkan_datastore_services_get_csrf($cookie_session, $curl, $base_url) {
  // GET CSRF TOKEN.
  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $base_url . '/services/session/token',
  ));
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

  $ret = new stdClass();

  $ret->response = curl_exec($curl);
  $ret->error    = curl_error($curl);
  $ret->info     = curl_getinfo($curl);

  $csrf_token = $ret->response;
  return $csrf_token;
}

/**
 * Server REST: Datastore - Create.
 */
function dkan_datastore_services_datastore_create($node_data, $csrf_token, $cookie_session, $base_url, $endpoint, $entity) {

  // REST Server URL.
  $request_url = $base_url . $endpoint . '/' . $entity;

  $node_data = http_build_query($node_data);

  $curl = dkan_datastore_services_curl_init($request_url, $csrf_token);
  // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_POST, 1);
  // Set POST data.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data);
  // Use the previously saved session.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

  $node = dkan_datastore_services_curl_parse($curl);

  return $node;
}

/**
 * Server REST - node.create.
 */
function dkan_datastore_services_delete_file($nid, $csrf_token, $cookie_session, $base_url, $endpoint, $entity) {
  // REST Server URL.
  $request_url = $base_url . $endpoint . '/' . $entity . '/' . $nid;

  $curl = dkan_datastore_services_curl_init($request_url, $csrf_token);
  // Set POST data.
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
  // Use the previously saved session.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

  $response = dkan_datastore_services_curl_parse($curl);

  return $response;
}

/**
 * Server REST - node.update.
 */
function dkan_datastore_services_datastore_update($node_data, $nid, $csrf_token, $cookie_session, $base_url, $endpoint, $entity) {
  // REST Server URL.
  $request_url = $base_url . $endpoint . '/' . $entity . '/' . $nid;

  $node_data = http_build_query($node_data);

  $curl = dkan_datastore_services_curl_init($request_url, $csrf_token);
  // Do a regular HTTP POST.
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
  // Set POST data.
  curl_setopt($curl, CURLOPT_POSTFIELDS, $node_data);
  // Use the previously saved session.
  curl_setopt($curl, CURLOPT_COOKIE, "$cookie_session");

  $response = curl_exec($curl);
  $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  $node = dkan_datastore_services_curl_parse($curl);

  return $node;
}
