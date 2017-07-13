<?php

namespace Drupal\DKANExtension\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DKANExtension\ServiceContainer\Page;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use EntityMetadataWrapperException;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Defines application features from the specific context.
 */
class RawDKANEntityContext extends RawDKANContext implements SnippetAcceptingContext {

  protected $entityType = '';
  protected $bundle = '';
  protected $bundleKey = FALSE;
  protected $fieldMap = array();
  protected $fieldProperties = array();
  protected $fieldMapCustom = array();
  protected $pageContext;
  protected $searchContext;

  /**
   * Constructor.
   */
  public function __construct($entity_type, $bundle, $field_map_overrides = array('published' => 'status'), $field_map_custom = array()) {
    $entity_info = entity_get_info($entity_type);
    $this->entityType = $entity_type;
    $this->fieldProperties = array();
    
    $default_field_map_overrides = array(
      'published' => 'status',
    );

    if ($field_map_overrides == NULL) {
      $field_map_overrides = $default_field_map_overrides;
    }
    else {
      $field_map_overrides = array_merge($default_field_map_overrides, $field_map_overrides);
    }

    $this->fieldMapCustom = $field_map_custom;

    // Check that the bundle specified actually exists, or if none given,
    // that this is an entity with no bundles (single bundle w/ name of entity)
    $entity_bundles = array_keys($entity_info['bundles']);
    if (!in_array($bundle, $entity_bundles) && !in_array($this->entityType, $entity_bundles)) {
      throw new \Exception("Bundle $bundle doesn't exist for entity type $this->entityType.");
    }
    // Handle entities without bundles and identify the bundle key name (i.e. 'type')
    if ($bundle == '' && in_array($this->entityType, $entity_info['bundles'])) {
      $this->bundle = $this->entityType;
      $this->bundleKey = FALSE;
    }
    else {
      $this->bundle = $bundle;
      $this->bundleKey = $entity_info['entity keys']['bundle'];
    }

    // Store the field properties for later.
    $property_info = entity_get_property_info($this->entityType);
    // Store the fields for this bundle, but only if the bundle has fields.
    if (isset($property_info['bundles'][$this->bundle])) {
      $this->fieldProperties += $property_info['bundles'][$this->bundle]['properties'];
    }
    // Store the properties shared by all entities of this type.
    $this->fieldProperties += $property_info['properties'];

    // Collect the default and overridden field mappings.
    foreach ($this->fieldProperties as $field => $info) {
      // First check if this field mapping is overridden.
      if ($label = array_search($field, $field_map_overrides)) {
        $this->fieldMap[$label] = $field;
      }
      // Use the default label from fieldProperties;.
      else {
        $this->fieldMap[strtolower($info['label'])] = $field;
      }
    }
  }

  /**
   * Gather needed contexts.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
    $this->pageContext = $environment->getContext('Drupal\DKANExtension\Context\PageContext');
    $this->searchContext = $environment->getContext('Drupal\DKANExtension\Context\SearchAPIContext');
  }

  /**
   * Delete all created content after each scenario.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The afterscenario scope.
   *
   * @AfterScenario
   */
  public function deleteAll(AfterScenarioScope $scope) {
    $wrappers = $this->entityStore->retrieve($this->entityType, $this->bundle);
    if ($wrappers === FALSE) {
      return;
    }
    foreach ($wrappers as $wrapper) {
      // The behat user teardown deletes all the content of a user automatically,
      // so we want to get a fresh entity instead of relying on the wrapper
      // (or a bool that confirms it's deleted)
      $entities_to_delete = entity_load($this->entityType, array($wrapper->getIdentifier()));

      if (!empty($entities_to_delete)) {
        foreach ($entities_to_delete as $entity_to_delete) {
          $entity_to_delete = entity_metadata_wrapper($this->entityType, $entity_to_delete);
          entity_delete($this->entityType, $entity_to_delete->getIdentifier());
        }
      }
      $wrapper->clear();
    }

    // For Scenarios Outlines, EntityContext is not deleted and recreated
    // and thus the entities array is not deleted and houses stale entities
    // from previous examples, so we clear it here.
    $this->entityStore->delete($this->entityType, $this->bundle);
    $this->entityStore->names_flush();

    // Make sure that we process any index items if they were deleted.
    $this->searchContext->process();
  }

  /**
   * Get Entity by name.
   *
   * @param string $name
   *   The name of the entity.
   *
   * @return \EntityDrupalWrapper
   *   Returns the wrapped entity or FALSE.
   */
  public function getByName($name) {
    return $this->entityStore->retrieve_by_name($name);
  }

  /**
   * Explode a comma separated string in a standard way.
   */
  public function explodeList($string) {
    $array = explode(',', $string);
    $array = array_map('trim', $array);
    return is_array($array) ? $array : array();
  }

  /**
   * Helper function to create an entity as an EntityMetadataWrapper.
   *
   * Takes a array of key-mapped values and creates a fresh entity
   * using the data provided. The array should correspond to the context's field_map.
   *
   * @return \stdClass
   *   Returns the entity or FALSE if fails.
   */
  public function newWrapper() {
    $entity = array();
    if ($this->bundleKey) {
      $entity[$this->bundleKey] = $this->bundle;
    }
    $entity = entity_create($this->entityType, $entity);

    // Entity API doesn't automatically apply node settings like revision,
    // status, promote, etc.
    // See http://drupal.stackexchange.com/questions/115710/why-entity-metadata-wrapper-save-doesnt-update-nodes-revision
    if ($this->entityType == 'node') {
      node_object_prepare($entity);
    }
    $wrapper = entity_metadata_wrapper($this->entityType, $entity);

    return $wrapper;
  }

  /**
   * Apply field values to entity.
   */
  public function applyFields($wrapper, $fields) {
    foreach ($fields as $label => $value) {
      if (in_array($label, $this->fieldMapCustom)) {
        continue;
      }
      if (isset($this->fieldMap[$label]) && $this->fieldMap[$label] === 'status') {
        $value = $this->convertStringToBool($value);
      }
      $this->setField($wrapper, $label, $value);
    }
    return $wrapper;
  }

  /**
   * Set field on entity.
   */
  public function setField($wrapper, $label, $value) {
    $property = NULL;
    try {
      // Make sure there is a mapping to an actual property.
      if (!isset($this->fieldMap[$label])) {
        $all_fields = implode(", \n", array_keys($this->fieldMap));
        throw new \Exception("There is no field mapped to label '$label'. Available fields are: $all_fields");
      }
      $property = $this->fieldMap[$label];

      // If no type is set for this property, then try to just output as-is.
      if (!isset($this->fieldProperties[$property]['type'])) {
        $wrapper->$property = $value;
        return;
      }

      $field_type = $this->fieldProperties[$property]['type'];

      switch ($field_type) {
        // Can be NID.
        case 'integer':
          $wrapper->$property->set((int) $value);
          break;

        // Do our best to handle 0, false, "false", or "No".
        case 'boolean':
          if (gettype($value) == 'string') {
            $value = $this->convertStringToBool($value);
          }
          $wrapper->$property->set((bool) $value);
          break;

        // Dates - handle strings as best we can. See http://php.net/manual/en/datetime.formats.relative.php
        case 'date':
          if (is_numeric($value)) {
            $timestamp = (int) $value;
          }
          else {
            $timestamp = strtotime($value);
          }
          if ($timestamp === FALSE) {
            throw new \Exception("Couldn't create a date with '$value'");
          }
          $wrapper->$property->set($timestamp);
          break;

        // User reference.
        case 'user':
          $user = user_load_by_name($value);
          if ($user === FALSE) {
            throw new \Exception("Can't find a user with username '$value'");
          }
          $wrapper->$property->set($user);
          break;

        // Simple text field.
        case 'text':
        case "list<text>":
          $wrapper->$property->set($value);
          break;

        // Formatted text like body.
        case 'text_formatted':
          // For now just apply the value directly.
          if (is_array($value)) {
            $wrapper->$property->set($value);
          }
          else {
            $wrapper->$property->set(array('value' => $value));
          }
          break;

        case 'taxonomy_term':
          if (!isset($value)) {
            break;
          }
          if ($found_term = $this->tidFromTermName($property, $value)) {
            $tid = $found_term;
          }
          else {
            throw new \Exception("Term '$value'' not found for field '$property'");
          }
          $wrapper->$property->set($tid);
          break;

        case "list<taxonomy_term>":
          // Convert the tags to tids.
          $tids = array();
          foreach ($this->explodeList($value) as $term) {
            if ($found_term = $this->tidFromTermName($property, $term)) {
              $tids[] = $found_term;
            }
            else {
              throw new \Exception("Term '$term'' not found for field '$property'");
            }
          }
          $wrapper->$property->set($tids);
          break;

        /* TODO BELOW */

        // Node reference.
        case 'node':
        case 'list<node>':
          $nids = array();
          foreach ($this->explodeList($value) as $name) {
            if (empty($name)) {
              continue;
            }
            $found_node_wrapper = $this->entityStore->retrieve_by_name($name);
            if ($found_node_wrapper !== FALSE) {
              $nids[] = $found_node_wrapper->nid->value();
            }
            else {
              throw new \Exception("Named Node '$name' not found, was it created during the test?");
            }
          }

          if ($field_type == "node") {
            // If the field type is node only one nid is expected.
            // Default to the first element.
            $wrapper->$property->set(reset($nids));
          }
          else {
            $wrapper->$property->set($nids);
          }
          break;

        // Not sure (something more complex)
        case 'struct':
          // Images.
        case 'field_item_image':
          // Links.
        case 'field_item_link':
          $wrapper->$property->set(array("url" => $value));
          break;

        // Files.
        case 'field_item_file':
          $file = (object) array(
            'uri' => $value,
            'status' => FILE_STATUS_PERMANENT,
            'filename' => basename($value),
            'filemime' => file_get_mimetype($value),
            'timestamp' => time(),
          );
          file_save($file);
          $wrapper->$property->file->set($file);
          break;

        case 'token':
          // References to nodes.
        case 'safeword_field':
          $wrapper->$property->set(array("machine" => $value));
          break;

        default:
          // For now, just error out as we can't handle it yet.
          throw new \Exception("Not sure how to handle field '$label' with type '$field_type'");
        break;
      }
    }
    catch (EntityMetadataWrapperException $e) {
      $print_val = print_r($value, TRUE);
      throw new \Exception("Error when setting field '$property' with value '$print_val': Error Message => {$e->getMessage()}");
    }
  }

  /**
   * Creates entities from a given table.
   *
   * Builds key-mapped arrays from a TableNode matching this context's field map,
   * then cycles through each array to start the entity build routine for each
   * corresponding array. This function will be called by sub-contexts to generate
   * their entities.
   */
  public function addMultipleFromTable(TableNode $entityTable) {
    foreach ($this->arrayFromTableNode($entityTable) as $entity) {
      $this->save($entity);
    }
  }

  /**
   * Build routine for an entity.
   */
  public function save($fields) {
    /** @var \EntityDrupalWrapper $wrapper */
    $wrapper = $this->newWrapper();
    $this->preSave($wrapper, $fields);
    $wrapper->save();
    $this->postSave($wrapper, $fields);
    return $wrapper;
  }

  /**
   * Do further processing after saving.
   */
  public function preSave($wrapper, $fields) {
    // Update the changed date after the entity has been saved.
    if (isset($fields['date changed'])) {
      unset($fields['date changed']);
    }
    if (!isset($fields['author']) && isset($this->fieldMap['author'])) {
      $field = $this->fieldMap['author'];
      $user = $this->getCurrentUser();
      if ($user) {
        $wrapper->$field->set($user);
      }
    }
    $this->dispatchDkanHooks('BeforeDKANEntityCreateScope', $wrapper, $fields);
    $this->applyMissingRequiredFields($fields);
    $this->applyFields($wrapper, $fields);
  }

  /**
   * Uses devel generate to produce default data for required missing fields.
   *
   * Works on either field or label tables.
   *
   * @param array $data
   *   Array of maps of label or field_name values.
   * */
  public function applyMissingRequiredFields(array &$data) {
    $wrapper = $this->newWrapper();
    $bundle = $wrapper->type->value();
    if ($bundle == "dataset") {
      module_load_include('inc', 'devel_generate', 'devel_generate');
      module_load_include('inc', 'devel_generate', 'devel_generate.fields');
      $node = new \stdClass();
      $node->type = $bundle;
      devel_generate_fields($node, 'node', $bundle);
      $devel_generate_wrapper = entity_metadata_wrapper('node', $node);

      foreach ($this->fieldProperties as $key => $field) {
        if ($key == 'type' || $key == 'author') {
          continue;
        }

        if (isset($field['required']) && $field['required']) {
          $k = array_search($key, $this->fieldMap);
          if (!isset($data[$k])) {
            $data[$k] = $devel_generate_wrapper->$key->value();
            // TODO: use param passed in from behat config for defaults.
            $defaults = array(
              'field_public_access_level' => 'public',
              'field_hhs_attestation_negative' => 1,
              'field_license' => 'odc-by',
            );
            foreach ($defaults as $default => $value) {
              if ($key == $default) {
                $data[$k] = $value;
              }
            }
          }
        }
      }
    }
  }

  /**
   * Do further processing after saving.
   */
  public function postSave($wrapper, $fields) {
    $this->dispatchDkanHooks('AfterDKANEntityCreateScope', $wrapper, $fields);
    // Remove the base url from the url and add it
    // to the page array for easy navigation.
    $url = parse_url($wrapper->url->value());
    // Add the url to the page array for easy navigation.
    $page = new Page($wrapper->label(), $url['path']);
    $this->getPageStore()->store($page);

    if (isset($fields['date changed'])) {
      $this->setChangedDate($wrapper, $fields['date changed']);
    }

    if (isset($fields["dataset"])) {
      if ($fields["dataset"]) {
        node_save($wrapper->value());
      }
    }

    // Process any outstanding search items.
    $this->searchContext->process();

    // Add the created entity to the array so it can be deleted later.
    $id = $wrapper->getIdentifier();
    $this->entityStore->store($this->entityType, $this->bundle, $id, $wrapper, $wrapper->label());
  }

  /**
   * Converts a TableNode into an array.
   *
   * Takes an TableNode and builds a multi-dimensional array,
   */
  public function arrayFromTableNode(TableNode $itemsTable) {
    $items = array();
    foreach ($itemsTable as $itemHash) {
      $items[] = $itemHash;
    }
    return $items;
  }

  /**
   * Converts a string value to a boolean value.
   */
  public function convertStringToBool($value) {
    $value = strtolower($value);
    $value = ($value === 'yes') ? TRUE : $value;
    $value = ($value === 'no') ? FALSE : $value;
    return $value;
  }

  /**
   * Get the term ID from term name.
   */
  public function tidFromTermName($field_name, $term) {
    $info = field_info_field($field_name);
    $vocab_machine_name = $info['settings']['allowed_values'][0]['vocabulary'];
    if ($found_terms = taxonomy_get_term_by_name($term, $vocab_machine_name)) {
      $found_term = reset($found_terms);
      return $found_term->tid;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Forces the change of a entities changed date as drupal makes this difficult.
   *
   * Note that this is only supported for nodes currently. TODO Support all entities.
   * Also, there is no guarantee that another action won't cause the updated date to change.
   */
  public function setChangedDate($saved_wrapper, $time_str) {
    if (!($saved_wrapper->type() == 'node')) {
      throw new Exception("Specifying the 'changed' date is only supported for nodes currently.");
    }
    if (!$nid = $saved_wrapper->getIdentifier()) {
      throw new Exception("Node ID could not be found. A node must be saved first before the changed date can be updated.");
    }
    // Use REQUEST_TIME, because that will remain consistent across all tests.
    // See time formats supported by strtotime().
    if (!$timestamp = strtotime($time_str, REQUEST_TIME)) {
      throw new Exception("Could not create a timestamp from $time_str.");
    }
    else {
      db_update('node')
        ->fields(array('changed' => $timestamp))
        ->condition('nid', $nid, '=')
        ->execute();

      db_update('node_revision')
        ->fields(array('timestamp' => $timestamp))
        ->condition('nid', $nid, '=')
        ->execute();
    }
  }

  /**
   * Fire off a DKAN hook.
   *
   * Based on RawDrupalContext::dispatchHooks().
   */
  protected function dispatchDkanHooks($scopeType, \EntityDrupalWrapper $wrapper, &$fields) {
    $fullScopeClass = 'Drupal\\DKANExtension\\Hook\\Scope\\' . $scopeType;
    $drupal = $this->getDrupal();
    if (!$drupal) {
      return;
    }
    $scope = new $fullScopeClass($drupal->getEnvironment(), $this, $wrapper, $fields);
    $callResults = $this->dispatcher->dispatchScopeHooks($scope);

    // The dispatcher suppresses exceptions, throw them here if there are any.
    foreach ($callResults as $result) {
      if ($result->hasException()) {
        $exception = $result->getException();
        throw $exception;
      }
    }
  }

}
