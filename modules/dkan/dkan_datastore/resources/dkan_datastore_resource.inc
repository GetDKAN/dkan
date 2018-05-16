<?php

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

function _dkan_datastore_resource_import($nid) {
  /* @var $datastore \Dkan\Datastore\Manager\ManagerInterface */
  $datastore = _dkan_datastore_resource_get_datastore($nid);

  if (!$datastore) {
    services_error("The datastore for Resource {$resource_nid} has not been configured.");
  }

  $finished = $datastore->import();
  if ($finished) {
    return "Success";
  }
  else {
    return $datastore->getErrors();
  }
}

function _dkan_datastore_resource_create($resource_nid, $data) {
  $data = _services_arg_value($data, 'data');
  $manager = $data['manager'];
  $configuration = $data['configuration'];
  // return $configuration;
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
  // return $configuration;
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

  return TRUE;
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
