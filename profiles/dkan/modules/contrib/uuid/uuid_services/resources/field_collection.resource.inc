<?php

function _field_collection_resource_definition() {
  if (module_exists('field_collection')) {
    // We will allow uuid_services_services_resources_alter() to add the
    // default UUID-related operations to this resource.
    return array('field_collection_item' => array());
  }
  else {
    return array();
  }
}
