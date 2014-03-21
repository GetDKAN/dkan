<?php

/**
 * @file
 * Main class for OG membership entities provided by Entity API.
 */
class OgMembership extends Entity {
  public function __construct(array $values = array(), $entityType = NULL) {
    parent::__construct($values, 'og_membership');
  }

  /**
   * Override Entity::save().
   *
   * Make sure we can save an OG membership, and that it is properly registered
   * under a valid field.
   */
  public function save() {
    $entity_type = $this->entity_type;
    $etid = $this->etid;

    if ($entity_type == 'user' && !$etid) {
      throw new OgException('OG membership can not be created for anonymous user.');
    }

    $wrapper = entity_metadata_wrapper($entity_type, $etid);
    $bundle = $wrapper->getBundle();

    $group_type = $this->group_type;
    $gid = $this->gid;
    $group = entity_load_single($group_type, $gid);
    list(,,$group_bundle) = entity_extract_ids($group_type, $group);

    $field_name = $this->field_name;

    // Placeholder for exceptions, in case we need to throw one.
    $params = array(
      '%entity-type' => $entity_type,
      '%entity-bundle' => $bundle,
      '%etid' => $this->etid,
      '%group-type' => $group_type,
      '%group-bundle' => $group_bundle,
      '%gid' => $this->gid,
      '%field-name' => $field_name,
    );

    $field = field_info_field($field_name);

    if (!$field || !field_info_instance($entity_type, $field_name, $bundle)) {
      throw new OgException(format_string('OG membership can not be created in entity %entity-type and bundle %entity-bundle using the field %field-name as the field does not exist.', $params));
    }

    if (!og_is_group_audience_field($field_name)) {
      throw new OgException(format_string('OG membership can not be created with %field-name as it is not a valid group-audience type.', $params));
    }

    if ($field['settings']['target_type'] != $group_type) {
      throw new OgException(format_string('OG membership can not be created in entity %entity-type and bundle %entity-bundle using the field %field-name as the field does not reference %group-type entity type.', $params));
    }

    if (!empty($field['settings']['handler_settings']['target_bundles']) && !in_array($group_bundle, $field['settings']['handler_settings']['target_bundles'])) {
      throw new OgException(format_string('OG membership can not be created in entity %entity-type and bundle %entity-bundle using the field %field-name as the field does not reference %group-bundle bundle in %group-type entity type.', $params));
    }

    $params += array('@cardinality' => $field['cardinality']);
    // Check field cardinality, that we may add another value, if we have a new
    // OG membership.
    if (empty($this->id) && !og_check_field_cardinality($entity_type, $etid, $field_name)) {
      throw new OgException(format_string('OG membership can not be created in entity %entity-type and bundle %entity-bundle using the field %field-name as the field cardinality is set to @cardinality.', $params));
    }

    if (empty($this->id) && $og_membership = og_get_membership($this->group_type, $this->gid, $this->entity_type, $this->etid)) {
      throw new OgException(format_string('OG membership for %etid - %entity-type in group %gid - %group-type already exists.', $params));
    }

    // We can now safely save the entity.
    parent::save();
    og_membership_invalidate_cache();
    // Clear the group content entity field cache.
    cache_clear_all('field:'. $entity_type . ':' . $etid, 'cache_field');

    // Supporting the entity cache module.
    if (module_exists('entitycache')) {
      cache_clear_all($etid, 'cache_entity_' . $entity_type);
    }
  }

  public function delete() {
    parent::delete();

    og_membership_invalidate_cache();
    // Clear the group content entity field cache.
    cache_clear_all('field:'. $this->entity_type . ':' . $this->etid, 'cache_field');

    // Supporting the entity cache module.
    if (module_exists('entitycache')) {
      cache_clear_all($this->etid, 'cache_entity_' . $this->entity_type);
    }
  }

  /**
   * Return the group assocaited with the OG membership.
   */
  public function group() {
    return entity_load_single($this->group_type, $this->gid);
  }

  /**
   * Gets the associated OG membership type.
   *
   * @return OgMembershipType
   */
  public function type() {
    return og_membership_type_load($this->name);
  }
}
