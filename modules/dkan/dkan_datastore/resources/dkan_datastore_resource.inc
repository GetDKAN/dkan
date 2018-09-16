<?php

/**
 * @file
 * Resource definition and callback for dkan_datastore API endpoints.
 */

use Dkan\Datastore\Manager\Factory;
use Dkan\Datastore\Resource;

/**
 * Resource definition.
 */
function _dkan_datastore_resource_definition() {
  $file = array(
    'type' => 'inc',
    'module' => 'dkan_datastore',
    'name' => 'resources/dkan_datastore_resource',
  );

  return array(
    'datastore' => array(
      'operations' => array(
        'retrieve' => array(
          'help' => 'Retrieve information about a datastore',
          'file' => $file,
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
          'access arguments' => array('manage'),
          'access arguments append' => TRUE,
        ),
        'create' => array(
          'help' => 'Create a datastore for the given resource.',
          'file' => $file,
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
          'access arguments' => array('manage'),
          'access arguments append' => TRUE,
        ),
        'update' => array(
          'help' => 'Update/modify the datastore for a given resource.',
          'file' => $file,
          'callback' => '_dkan_datastore_resource_update',
          'access arguments' => array('manage'),
          'access arguments append' => TRUE,
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
        'file' => $file,
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
        'access arguments' => array('delete'),
        'access arguments append' => TRUE,
      ),
      'targeted_actions' => array(
        'drop' => array(
          'help' => 'Drop a datastore table but keep configuration.',
          'file' => $file,
          'callback' => '_dkan_datastore_resource_drop',
          'access callback' => '_dkan_datastore_resource_access',
          'access arguments' => array('drop'),
          'access arguments append' => TRUE,
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
          'file' => $file,
          'callback' => '_dkan_datastore_resource_import',
          'access callback' => '_dkan_datastore_resource_access',
          'access arguments' => array('import'),
          'access arguments append' => TRUE,
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

/**
 * Get datastore.
 */
function _dkan_datastore_resource_get_datastore_manager($resource_nid) {
  try {
    $resource = Resource::createFromDrupalNodeNid($resource_nid);
  }
  catch (\Exception $e) {
    services_error("Resource {$resource_nid} does not exist | {$e->getMessage()}");
    die();
  }

  /* @var $manger \Dkan\Datastore\Manager\ManagerInterface */
  $manager = (new Factory($resource))->get();

  return $manager;
}

/**
 * Datastore retrieve.
 */
function _dkan_datastore_resource_retrieve($resource_nid) {
  /* @var $manager \Dkan\Datastore\Manager\ManagerInterface */
  $manager = _dkan_datastore_resource_get_datastore_manager($resource_nid);

  if (!$manager) {
    services_error("The datastore for Resource {$resource_nid} has not been configured.");
  }

  return $manager->getStatus();
}

/**
 * Datastore create.
 */
function _dkan_datastore_resource_create($resource_nid, $data) {
  $data = _services_arg_value($data, 'data');
  $manager_name = $data['manager'];
  $configuration = $data['configuration'];
  try {
    if (_dkan_datastore_resource_get_datastore_manager($resource_nid)) {
      services_error("Configuration for this resource's datastore already exists.");
      die();
    }
  }
  catch (\Exception $e) {
    return $e->getMessage();
  }

  $class = _dkan_datastore_resource_get_class($manager_name);
  if (!$class) {
    services_error("The manager {$manager_name} does not exist or is not active.");
    die();
  }
  else {
    $resource = Resource::createFromDrupalNodeNid($resource_nid);

    $factory = new Factory($resource);
    $factory->setClass($class);
    /* @var $manager \Dkan\Datastore\Manager\ManagerInterface */
    $manager = $factory->get();
    $properties = $manager->getConfigurableProperties();
    foreach ($properties as $property_name => $default_value) {
      if (!isset($configuration[$property_name])) {
        services_error("The configuration property {$property_name} was not set.");
      }
      $manager->setConfigurableProperties($configuration);
    }
    // Now get the stored object and return.
    unset($manager);
    $manager = _dkan_datastore_resource_get_datastore_manager($resource_nid);

    if (!$manager) {
      services_error("The datastore for Resource {$resource_nid} was not properly configured.");
    }
    return $manager->getStatus();
  }
}

/**
 * Datastore update.
 */
function _dkan_datastore_resource_update($resource_nid, $data) {
  $data = _services_arg_value($data, 'data');
  $manager_name = $data['manager'];
  $configuration = $data['configuration'];
  try {
    $manager = _dkan_datastore_resource_get_datastore_manager($resource_nid);
    if (empty($manager)) {
      services_error("This resource's datastore is not configured or does not exist.");
    }
  }
  catch (\Exception $e) {
    return $e->getMessage();
  }

  if ($manager_name) {
    $class = _dkan_datastore_resource_get_class($manager_name);
    $status = $manager->getStatus();
    // If the manager class has changed, drop and create a new one.
    if ($status['class'] != $class) {
      services_error("You may not change the manager of an existing datastore. Drop the datastore and create a new one to use a different manager.");
    }
  }

  $properties = $manager->getConfigurableProperties();
  foreach ($properties as $property_name => $default_value) {
    if (isset($configuration[$property_name])) {
      $manager->setConfigurableProperties($configuration);
    }
  }
}

/**
 * Datastore delete.
 */
function _dkan_datastore_resource_delete($resource_nid) {
  try {
    $manager = _dkan_datastore_resource_get_datastore_manager($resource_nid);
    if (empty($manager)) {
      services_error("This resource's datastore is not configured or does not exist.");
    }
  }
  catch (\Exception $e) {
    return $e->getMessage();
  }

  $manager->drop();
  $manager->dropState();

  return TRUE;
}

/**
 * Datastore import.
 */
function _dkan_datastore_resource_import($nid) {
  /* @var $manager \Dkan\Datastore\Manager\ManagerInterface */
  $manager = _dkan_datastore_resource_get_datastore_manager($nid);

  if (!$manager) {
    services_error("The datastore for Resource {$nid} has not been configured.");
  }

  $finished = $manager->import();
  if ($finished) {
    return TRUE;
  }
  else {
    return $manager->getErrors();
  }
}

/**
 * Datastore drop.
 */
function _dkan_datastore_resource_drop($nid) {
  $manager = _dkan_datastore_resource_get_datastore_manager($nid);

  if (!$manager) {
    services_error("The datastore for Resource {$nid} has not been configured.");
  }

  $finished = $manager->drop();
  if ($finished) {
    return TRUE;
  }
  else {
    return $manager->getErrors();
  }
}

/**
 * Get manager class from machine name.
 */
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

/**
 * Resource info.
 */
function _dkan_datastore_resource_info() {
  $info = dkan_datastore_managers_info();
  return $info;
}

/**
 * Resource access.
 */
function _dkan_datastore_resource_access($op = 'view', $args = array()) {
  // Make sure we have an object or this all fails, some servers can
  // mess up the types.
  if (is_array($args[0])) {
    $args[0] = (object) $args[0];
  }
  // This is to determine if it is just a string happens on node/%NID.
  elseif (!is_array($args[0]) && !is_object($args[0])) {
    $args[0] = (object) ['nid' => $args[0]];
  }

  if ($op != 'create' && !empty($args)) {
    $node = node_load($args[0]->nid);
  }
  elseif ($op == 'create') {
    if (isset($args[0]->type)) {
      $node = $args[0]->type;
      return node_access($op, $node);
    }
    else {
      return services_error(t('Node type is required'), 406);
    }
  }
  if (isset($node->nid) && $node->nid) {
    return dkan_datastore_access($op, $node);
  }
  else {
    return services_error(t('Node @nid could not be found', array('@nid' => $args[0]->nid)), 404);
  }

}
