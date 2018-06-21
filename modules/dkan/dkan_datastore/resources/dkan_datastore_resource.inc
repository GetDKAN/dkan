<?php
/**
 * @file
 * Resource definition and callback for dkan_datastore endpoint (used in
 * Dataset REST API).
 */

/**
 * Resource definition.
 */
function _dkan_datastore_resource_definition() {
  return array(
    'datastore' => array(
      'operations' => array(
        'retrieve' => array(
          'help' => 'Retrieve information about a datastore',
          'file' => array('type' => 'inc', 'module' => 'dkan_datastore', 'name' => 'resources/dkan_datastore_resource'),
          'callback' => '_dkan_datastore_resource_retrieve',
          'args' => array(
            array(
              'name' => 'resource_nid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The nid of the resource the datastore is connected to.',
            ),
          ),
          'access callback' => '_dkan_datastore_resource_access',
        ),
        'create' => array(
          'help' => 'Create a datastore for the given resource.',
          'file' => array('type' => 'inc', 'module' => 'dkan_datastore', 'name' => 'resources/dkan_datastore_resource'),
          'callback' => '_dkan_datastore_resource_create',
          'args' => array(
            array(
              'name' => 'nid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The nid of the resource to configure a datastore for.',
            ),
            array(
              'name' => 'data',
              'optional' => FALSE,
              'type' => 'string',
              'source' => 'data',
              'description' => 'The machine name of the datastore manager that should be used.',
            ),
          ),
          'access callback' => '_dkan_datastore_resource_access',
        ),
        'update' => array(
          'help' => 'Update/modify the datastore for a given resource.',
          'file' => array('type' => 'inc', 'module' => 'dkan_datastore', 'name' => 'resources/dkan_datastore_resource'),
          'callback' => '_dkan_datastore_resource_update',
          'args' => array(
            array(
              'name' => 'nid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The nid of the resource to configure a datastore for.',
            ),
            array(
              'name' => 'data',
              'optional' => FALSE,
              'type' => 'string',
              'source' => 'data',
              'description' => 'Configuration data for datastore.',
            ),
          ),
          'access callback' => '_dkan_datastore_resource_access',
        ),
      ),
      'delete' => array(
        'help' => 'Remove the datastore for a given resource.',
        'file' => array('type' => 'inc', 'module' => 'dkan_datastore', 'name' => 'resources/dkan_datastore_resource'),
        'callback' => '_dkan_datastore_resource_delete',
        'args' => array(
          array(
            'name' => 'nid',
            'optional' => FALSE,
            'source' => array('path' => 0),
            'type' => 'int',
            'description' => 'The nid of the resource for which to drop datastore.',
          ),
        ),
        'access callback' => '_dkan_datastore_resource_access',
      ),
      'targeted_actions' => array(
        'drop' => array(
          'help' => 'Drop a datastore table but keep configuration.',
          'file' => array('type' => 'inc', 'module' => 'dkan_datastore', 'name' => 'resources/dkan_datastore_resource'),
          'callback' => '_dkan_datastore_resource_drop',
          'access callback' => '_dkan_datastore_resource_access',
          'args' => array(
            array(
              'name' => 'nid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The nid of the node to attach a file to',
            ),
          ),
        ),
        'import' => array(
          'help' => 'Queue a datastore for import.',
          'file' => array('type' => 'inc', 'module' => 'dkan_datastore', 'name' => 'resources/dkan_datastore_resource'),
          'callback' => '_dkan_datastore_resource_import',
          'access callback' => '_dkan_datastore_resource_access',
          'args' => array(
            array(
              'name' => 'nid',
              'optional' => FALSE,
              'source' => array('path' => 0),
              'type' => 'int',
              'description' => 'The nid of the node to attach a file to',
            ),
          ),
        ),
      ),
    ),
  );
}

function _dkan_datastore_resource_get_resource($resource_nid) {
  try {
    $resource = \Dkan\Datastore\Resource::createFromDrupalNodeNid($resource_nid);
  }
  catch (\Exception $e) {
    services_error("Resource {$resource_nid} does not exist | {$e->getMessage()}");
    die();
  }
  return $resource;
}

function _dkan_datastore_resource_get_datastore($resource_nid) {
  /* @var $datastore \Dkan\Datastore\Manager\ManagerInterface */
  $datastore = \Dkan\Datastore\Manager\Factory::create(_dkan_datastore_resource_get_resource($resource_nid));

  return $datastore;
}

function _dkan_datastore_resource_retrieve($resource_nid) {
  /* @var $datastore \Dkan\Datastore\Manager\ManagerInterface */
  $datastore = _dkan_datastore_resource_get_datastore($resource_nid);

  if (!$datastore) {
    services_error("The datastore for Resource {$resource_nid} has not been configured.");
  }
  return $datastore->getStatus();
}

function _dkan_datastore_resource_create($resource_nid, $data) {
  $data = _services_arg_value($data, 'data');
  $manager = $data['manager'];
  $configuration = $data['configuration'];
  try {
    if (_dkan_datastore_resource_get_datastore($resource_nid)) {
      services_error("Configuration for this resource's datastore already exists.");
      die();
    }
  }
  catch (\Exception $e) {
    return $e->getMessage();
  }

  $class = _dkan_datastore_resource_get_class($manager);
  if (!$class) {
    services_error("The manager {$manager} does not exist or is not active.");
    die();
  }
  else {
    $resource = \Dkan\Datastore\Resource::createFromDrupalNodeNid($resource_nid);

    /* @var $datastore \Dkan\Datastore\Manager\ManagerInterface */
    $datastore = \Dkan\Datastore\Manager\Factory::create($resource, $class);
    $properties = $datastore->getConfigurableProperties();
    foreach ($properties as $property_name => $default_value) {
      if (!isset($configuration[$property_name])) {
        services_error("The configuration property {$property_name} was not set.");
      }
      $datastore->setConfigurableProperties($configuration);
    }
    // Now get the stored object and return
    unset($datastore);
    $datastore = _dkan_datastore_resource_get_datastore($resource_nid);

    if (!$datastore) {
      services_error("The datastore for Resource {$resource_nid} was not properly configured.");
    }
    return $datastore->getStatus();
  }
}

function _dkan_datastore_resource_update($resource_nid, $data) {
  $data = _services_arg_value($data, 'data');
  $manager = $data['manager'];
  $configuration = $data['configuration'];
  try {
    $datastore = _dkan_datastore_resource_get_datastore($resource_nid);
    if (empty($datastore)) {
      services_error("This resource's datastore is not configured or does not exist.");
    }
  }
  catch (\Exception $e) {
    return $e->getMessage();
  }

  if ($manager) {
    $class = _dkan_datastore_resource_get_class($manager);
    $status = $datastore->getStatus();
    // If the manager class has changed, drop and create a new one.
    if ($status['class'] != $class) {
      services_error("You may not change the manager of an existing datastore. Drop the datastore and create a new one to use a different manager.");
    }
  }

  $properties = $datastore->getConfigurableProperties();
  foreach ($properties as $property_name => $default_value) {
    if (isset($configuration[$property_name])) {
      $datastore->setConfigurableProperties($configuration);
    }
  }
}

function _dkan_datastore_resource_delete($resource_nid) {
  try {
    $datastore = _dkan_datastore_resource_get_datastore($resource_nid);
    if (empty($datastore)) {
      services_error("This resource's datastore is not configured or does not exist.");
    }
  }
  catch (\Exception $e) {
    return $e->getMessage();
  }

  $datastore->drop();
  $datastore->dropState();

  return TRUE;
}

function _dkan_datastore_resource_import($nid) {
  /* @var $datastore \Dkan\Datastore\Manager\ManagerInterface */
  $datastore = _dkan_datastore_resource_get_datastore($nid);

  if (!$datastore) {
    services_error("The datastore for Resource {$resource_nid} has not been configured.");
  }

  $finished = $datastore->import();
  if ($finished) {
    return TRUE;
  }
  else {
    return $datastore->getErrors();
  }
}

function _dkan_datastore_resource_drop($nid) {
  $datastore = _dkan_datastore_resource_get_datastore($nid);

  if (!$datastore) {
    services_error("The datastore for Resource {$resource_nid} has not been configured.");
  }

  $finished = $datastore->drop();
  if ($finished) {
    return TRUE;
  }
  else {
    return 'untrue';
    return $datastore->getErrors();
  }
}

function _dkan_datastore_resource_get_class($manager_machine_name) {
  $info = dkan_datastore_managers_info();
  /* @var $i \Dkan\Datastore\Manager\Info */
  foreach ($info as $i) {
    if ($i->getMachineName() == $manager_machine_name) {
      return $i->getClass();
    }
  }
  return NULL;
}

function _dkan_datastore_resource_info() {
  $info = dkan_datastore_managers_info();
  return $info;
}

function _dkan_datastore_resource_access() {
  return TRUE;
}
