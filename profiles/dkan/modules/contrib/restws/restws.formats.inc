<?php

/**
 * @file
 * RESTful web services module formats.
 */

/**
 * Interface implemented by formatter implementations for the http client.
 */
interface RestWSFormatInterface {

  /**
   * Gets the representation of a resource.
   *
   * @param RestWSResourceControllerInterface $resourceController
   *   The controller used to retrieve the resource.
   * @param int|string $id
   *   The id of the resource that should be returned.
   *
   * @return string
   *   The representation of the resource.
   */
  public function viewResource($resourceController, $id);

  /**
   * Create a resource.
   *
   * @param RestWSResourceControllerInterface $resourceController
   *   The controller used to create the resource.
   * @param string $data
   *   The representation of the resource.
   *
   * @return int|string
   *   The id of the newly created resource.
   */
  public function createResource($resourceController, $data);

  /**
   * Update a resource.
   *
   * @param RestWSResourceControllerInterface $resourceController
   *   The controller used to update the resource.
   * @param int|string $id
   *   The id of the resource that should be updated.
   * @param string $data
   *   The representation of the resource.
   */
  public function updateResource($resourceController, $id, $data);

  /**
   * Delete a resource.
   *
   * @param RestWSResourceControllerInterface $resourceController
   *   The controller used to update the resource.
   * @param int|string $id
   *   The id of the resource that should be deleted.
   */
  public function deleteResource($resourceController, $id);

  /**
   * Query for a resource.
   *
   * If a format doesn't want to implement querying, then it should throw an
   * RestWSException with the 501 HTTP status code.
   *
   * @param RestWSResourceControllerInterface $resourceController
   *   The controller used to query the resource.
   * @param array $payload
   *   An optional way to pass the query parameters.
   *
   * @return string
   *   The serialized representation of a list of resources.
   */
  public function queryResource($resourceController, $payload);


  /**
   * Returns the mime type of this format, e.g. 'application/json' or
   * 'application/xml'.
   */
  public function mimeType();

  /**
   * Returns the short name of this format.
   *
   * @return string
   *   The format name, example: "json".
   */
  public function getName();
}

/**
 * A base for all simple formats that are just serializing/unserializing an
 * array of property values.
 */
abstract class RestWSBaseFormat implements RestWSFormatInterface {

  protected $formatName;
  protected $formatInfo;

  public function __construct($name, $info) {
    $this->formatName = $name;
    $this->formatInfo = $info;
  }

  /**
   * Gets the representation of a resource.
   */
  public function viewResource($resourceController, $id) {
    $values = self::getData($resourceController->wrapper($id));
    $function = __FUNCTION__;
    drupal_alter('restws_response', $values, $function, $this->formatName);

    return $this->serialize($values);
  }

  /**
   * Creates a new resource.
   */
  public function createResource($resourceController, $data) {
    $values = $this->unserialize($resourceController->propertyInfo(), $data);
    $id = $resourceController->create($values);
    $ref = self::getResourceReference($resourceController->resource(), $id);
    $function = __FUNCTION__;
    drupal_alter('restws_response', $ref, $function, $this->formatName);

    return $this->serialize($ref);
  }

  /**
   * Updates a resource.
   */
  public function updateResource($resourceController, $id, $data) {
    $values = $this->unserialize($resourceController->propertyInfo(), $data);
    $resourceController->update($id, $values);
    // Return an empty representation by default.
    $value = array();
    $function = __FUNCTION__;
    drupal_alter('restws_response', $value, $function, $this->formatName);

    return $this->serialize($value);
  }

  /**
   * Deletes a resource.
   */
  public function deleteResource($resourceController, $id) {
    $resourceController->delete($id);
    // Return an empty representation by default.
    $value = array();
    $function = __FUNCTION__;
    drupal_alter('restws_response', $value, $function, $this->formatName);

    return $this->serialize($value);
  }

  /**
   * Implements RestWSFormatInterface::queryResource().
   */
  public function queryResource($resourceController, $payload) {
    // Get the parameter from the URL.
    $parameters = drupal_get_query_parameters();

    $rest_controls = restws_meta_controls();
    $properties = $resourceController->propertyInfo();
    $split_parameters = $this->splitParameters($properties, $parameters);

    $values = $this->generateQueryURIs($resourceController, $parameters, $split_parameters['filters']);

    $full = (isset($split_parameters['meta_controls'][$rest_controls['full']])) ? $split_parameters['meta_controls'][$rest_controls['full']] : 1;

    $result = $resourceController->query($split_parameters['filters'], $split_parameters['meta_controls']);
    if ($full === '0') {
      foreach ($result as $id) {
        $values['list'][] = $this->getResourceReference($resourceController->resource(), $id);
      }
    }
    else {
      foreach ($result as $id) {
        $values['list'][] = self::getData($resourceController->wrapper($id));
      }
    }

    $function = __FUNCTION__;
    drupal_alter('restws_response', $values, $function, $this->formatName);

    return $this->serialize($values);
  }

  public function mimeType() {
    return $this->formatInfo['mime type'];
  }

  public function getName() {
    return $this->formatName;
  }

  /**
   * Gets a simple PHP array using URI references for some wrapped data.
   *
   * This is the counter-part of self::getPropertyValues().
   */
  public static function getData($wrapper) {
    $data = array();
    $filtered = restws_property_access_filter($wrapper);
    foreach ($filtered as $name => $property) {
      try {
        if ($property instanceof EntityDrupalWrapper) {
          // For referenced entities only return the URI.
          if ($id = $property->getIdentifier()) {
            $data[$name] = self::getResourceReference($property->type(), $id);
          }
        }
        elseif ($property instanceof EntityValueWrapper) {
          $data[$name] = $property->value();
        }
        elseif ($property instanceof EntityListWrapper || $property instanceof EntityStructureWrapper) {
          $data[$name] = self::getData($property);
        }
      }
      catch (EntityMetadataWrapperException $e) {
        // A property causes problems - ignore that.
      }
    }
    return $data;
  }

  public static function getResourceReference($resource, $id) {
    $return = array(
      'uri' => restws_resource_uri($resource, $id),
      'id' => $id,
      'resource' => $resource,
    );
    if (module_exists('uuid') && entity_get_info($resource)) {
      $ids = entity_get_uuid_by_id($resource, array($id));
      if ($id = reset($ids)) {
        $return['uuid'] = $id;
      }
    }
    return $return;
  }

  /**
   * Transforms simple-array data values to valid entity property values.
   *
   * This is the counter-part of self::getData(), thus it converts resource
   * references to the required value(s).
   *
   * @param array $values
   *   The array representation of the data values.
   * @param $property_info
   *   The property info array of the entity type for which we are transforming
   *   the values.
   */
  protected function getPropertyValues(array &$values, $property_info) {
    foreach ($values as $name => &$property_value) {
      if (isset($property_info[$name]) && $info = $property_info[$name]) {

        // Check if there is a resource array and if the property has a type.
        if (is_array($property_value) && isset($info['type'])) {

          // Check if the field is a list or a single value field.
          if (entity_property_list_extract_type($info['type'])) {
            // Check if the list values consist of structure wrappers.
            if (array_key_exists('property info', $info)) {
              foreach ($property_value as &$list_values) {
                $this->getPropertyValues($list_values, $info['property info']);
              }
            }
            else {
              $list_type = entity_property_list_extract_type($info['type']);
              foreach ($property_value as &$list_value) {
                $list_value = $this->getResourceReferenceValue($list_type, $list_value);
              }
            }
          }
          else {
            // Check if the property is a structure wrapper.
            if (array_key_exists('property info', $info)) {
              $this->getPropertyValues($property_value, $info['property info']);
            }
            else {
              $property_value = $this->getResourceReferenceValue($info['type'], $property_value);
            }
          }
        }
      }
    }
  }

  /**
   * Gets the resource reference value.
   *
   * @param $type
   *   The data type of the reference property.
   * @param array $reference
   *   The input data specifying the resource reference in one supported way.
   *
   * @return mixed
   *   The value to be set for the reference. Usually this is an entity or
   *   resource id, but for generic entity references it's an
   *   EntityDrupalWrapper.
   *
   * @see RestWSBaseFormat::getResourceReference()
   */
  protected function getResourceReferenceValue($type, array $reference) {

    if (isset($reference['id']) && $type != 'entity') {
      return $reference['id'];
    }
    // Handle setting generic entity references, i.e. of type entity.
    elseif ($type == 'entity' && isset($reference['id']) && isset($reference['resource'])) {
      if (!entity_get_info($reference['resource'])) {
        throw new RestWSException('Invalid resource for entity reference given.', 406);
      }
      return entity_metadata_wrapper($reference['resource'], $reference['id']);
    }
    elseif (isset($reference['uri'])) {
      // @todo: Implement setting references by URI by parsing resource/id from
      // the URI.
    }
    elseif (isset($reference['uuid']) && module_exists('uuid') && $type != 'entity') {
      $ids = entity_get_id_by_uuid($type, array($reference['uuid']));
      if (!$ids) {
        throw new RestWSException('Invalid UUID for resource reference given.', 406);
      }
      return reset($ids);
    }

    throw new RestWSException('Invalid value for resource reference given.', 406);
  }

  /**
   * Splits a query parameter into two sub arrays containing the filters and
   * meta controls.
   *
   * @param array $properties
   *   An array containing the properties of the resource.
   *
   * @param array $parameters
   *   An array which contains filters and meta controls.
   *
   * @return array
   *   An array containing two sub arrays, one for filters and one for meta
   *   controls with corresponding keys.
   *
   * @throws RestWSException
   *   If a filter isn't valid, the function will throw a RestWSException with
   *   the 412 HTTP status code.
   */
  protected function splitParameters($properties, array $parameters) {
    $meta_controls = array();
    $rest_controls = restws_meta_controls();
    foreach ($parameters as $control_name => $property) {
      if (isset($rest_controls[$control_name])) {
        $meta_controls[$control_name] = $property;
        unset($parameters[$control_name]);
      }
    }

    $filters = array();
    foreach ($parameters as $parameter => $value) {
      // Check if the property is prefixed.
      if (substr($parameter, 0, 9) == 'property_') {
        $parameter = substr($parameter, 9, strlen($parameter) - 9);
      }

      // If the parameter doesn't exist, we can not filter for and need to
      // notify the client about it.
      if (!isset($properties[$parameter])) {
        throw new RestWSException('Not a valid filter: ' . $parameter, 412);
      }
      $filters[$parameter] = $value;
    }
    return array('meta_controls' => $meta_controls, 'filters' => $filters);
  }

  /**
   * Generates all navigation links for querying.
   *
   * @param RestWSResourceControllerInterface $resourceController
   *   The controller used to query the resource.
   *
   * @param array $parameters
   *   The HTTP GET parameters for the query.
   *
   * @param array $filters
   *   The filters for the query.
   *
   * @return array
   *   An array containing all navigation links.
   *
   * @throws RestWSException
   *   If the page is out of range the function will throw a new RestWSException
   *   with HTTP status code 404.
   */
  protected function generateQueryURIs(RestWSResourceControllerInterface $resourceController, array $parameters, array $filters) {
    $rest_controls = restws_meta_controls();

    $count = $resourceController->count($filters);
    $limit = isset($parameters[$rest_controls['limit']]) ? $parameters[$rest_controls['limit']] : NULL;
    $limit = $resourceController->limit($limit);
    $page = isset($parameters[$rest_controls['page']]) ? $parameters[$rest_controls['page']] : 0;

    $last = ceil($count / $limit) - 1;

    if ($page > $last || $page < 0) {
      throw new RestWSException('Page doesn\'t exist.', 404);
    }

    $uris = array();
    $options = array(
      'query' => &$parameters,
    );

    $uris['self'] = restws_resource_uri($resourceController->resource(), null, $options);
    $parameters['page'] = 0;
    $uris['first'] = restws_resource_uri($resourceController->resource(), null, $options);
    $parameters['page'] = $last;
    $uris['last'] = restws_resource_uri($resourceController->resource(), null, $options);


    if ($page != 0) {
      $parameters['page'] = $page - 1;
      $uris['prev'] = restws_resource_uri($resourceController->resource(), null, $options);
    }

    if ($page != $last) {
      $parameters['page'] = $page + 1;
      $uris['next'] = restws_resource_uri($resourceController->resource(), null, $options);
    }

    return $uris;
  }
}

/**
 * Filters out properties where view access is not allowed for the current user.
 *
 * @param EntityMetadataWrapper $wrapper
 *   EntityMetadataWrapper that should be checked.
 *
 * @return
 *   An array of properties where access is allowed, keyed by their property
 *   name.
 */
function restws_property_access_filter($wrapper) {
  $filtered = array();
  foreach ($wrapper as $name => $property) {
    if ($property->access('view')) {
      $filtered[$name] = $property;
    }
  }
  return $filtered;
}

/**
 * A formatter to format json.
 */
class RestWSFormatJSON extends RestWSBaseFormat {

  public function serialize($values) {
    return drupal_json_encode($values);
  }

  public function unserialize($properties, $data) {
    $values = drupal_json_decode($data);
    $this->getPropertyValues($values, $properties);
    return $values;
  }
}

/**
 * A formatter for XML.
 */
class RestWSFormatXML extends RestWSBaseFormat {

  /**
   * Gets the representation of a resource.
   */
  public function viewResource($resourceController, $id) {
    $xml = new DOMDocument('1.0', 'utf-8');
    $element = $xml->createElement($resourceController->resource());
    self::addToXML($xml, $element, $resourceController->wrapper($id));
    $xml->appendChild($element);

    $function = __FUNCTION__;
    drupal_alter('restws_response', $xml, $function, $this->formatName);

    return $xml->saveXML();
  }

  /**
   * Creates a new resource.
   */
  public function createResource($resourceController, $data) {
    $values = $this->unserialize($resourceController->propertyInfo(), $data);
    $id = $resourceController->create($values);

    $xml = new DOMDocument('1.0', 'utf-8');
    $element = $xml->createElement('uri');
    self::setXMLReference($element, $resourceController->resource(), $id);
    $xml->appendChild($element);

    $function = __FUNCTION__;
    drupal_alter('restws_response', $xml, $function, $this->formatName);

    return $xml->saveXML();
  }

  /**
   * Overrides RestWSBaseFormat::queryResource().
   */
  public function queryResource($resourceController, $payload) {
    $xml = new DOMDocument('1.0', 'utf-8');
    $element = $xml->createElement('list');

    $rest_controls = restws_meta_controls();
    $parameters = drupal_get_query_parameters();
    $properties = $resourceController->propertyInfo();
    $split_parameters = $this->splitParameters($properties, $parameters);

    $links = $this->generateQueryURIs($resourceController, $parameters, $split_parameters['filters']);

    foreach ($links as $rel => $link) {
      $item = $xml->createElement('link');
      $item->setAttribute('rel', $rel);
      $item->setAttribute('href', $link);
      $element->appendChild($item);
    }

    $full = (isset($split_parameters['meta_controls'][$rest_controls['full']])) ? $split_parameters['meta_controls'][$rest_controls['full']] : 1;

    $result = $resourceController->query($split_parameters['filters'], $split_parameters['meta_controls']);

    if ($full === '0') {
      foreach ($result as $id) {
        $item = $xml->createElement($resourceController->resource());
        self::setXMLReference($item, $resourceController->resource(), $id);
        $element->appendChild($item);
      }
    }
    else {
      foreach ($result as $id) {
        $item = $xml->createElement($resourceController->resource());
        self::addToXML($xml, $item, $resourceController->wrapper($id));
        $element->appendChild($item);
      }
    }

    $xml->appendChild($element);

    $function = __FUNCTION__;
    drupal_alter('restws_response', $xml, $function, $this->formatName);

    return $xml->saveXML();
  }

  public function serialize($data) {
    // Return an empty XML document.
    $xml = new DOMDocument('1.0', 'utf-8');
    return $xml->saveXML();
  }

  public function unserialize($properties, $data) {
    $xml = simplexml_load_string($data);
    return $this->xmlToArray($properties, $xml);
  }

  /**
   * Turns the xml structure into an array of values.
   */
  public function xmlToArray($properties, SimpleXMLElement $xml, $listItemType = NULL) {
    foreach ($xml->children() as $name => $element) {
      // Check if we are processing an entity, an item from a list or a list.
      if ((isset($properties[$name]['type']) && (entity_property_list_extract_type($properties[$name]['type']) || entity_get_info($properties[$name]['type'])))  || isset($listItemType)) {
        // If we are processing a list, then set the type of the list and save
        // the results into a a numeric array.
        if (isset($listItemType)) {
          $type = $listItemType;
          $result_pointer = &$result[];
        }
        else {
          $type = $properties[$name]['type'];
          $result_pointer = &$result[$name];
        }

        // Check if the type is a list.
        if (entity_property_list_extract_type($type)) {
          $result_pointer = $this->xmlToArray($properties, $element, entity_property_list_extract_type($type));
        }
        else {
          $attributes = $element->attributes();
          $values['id'] = (string)$attributes['id'];
          $values['resource'] = (string)$attributes['resource'];
          $values['uri'] = $this->xmlToArray($properties, $element);
          $id = $this->getResourceReferenceValue($type, $values);
          // If an id could be extracted, then a resource array was send.
          if ($id !== FALSE) {
            $result_pointer = $id;
          }
          else {
            // If no ID could be extracted, then save the inner text content of
            // the node, which is saved in the $values['uri'].
            $result_pointer = $values['uri'];
          }
        }
      }
      else {
        $result[$name] = $this->xmlToArray($properties, $element);
      }
      foreach ($xml->attributes() as $attribute_name => $attribute_value) {
        $result[$attribute_name] = $attribute_value;
      }
    }
    if (!isset($result)) {
      $result = ($string = (string) $xml) ? $string : NULL;
    }
    return $result;
  }

  /**
   * Adds the data of the given wrapper to the given XML element.
   */
  public static function addToXML(DOMDocument $doc, DOMNode $parent, $wrapper) {
    $filtered = restws_property_access_filter($wrapper);
    foreach ($filtered as $name => $property) {
      try {
        if ($property instanceof EntityDrupalWrapper) {
          // For referenced entities only return the URI.
          if ($id = $property->getIdentifier()) {
            $element = $doc->createElement(is_numeric($name) ? 'item' : $name);
            $parent->appendChild($element);
            self::setXMLReference($element, $property->type(), $id);
          }
        }
        elseif ($property instanceof EntityValueWrapper) {
          // Only primitive data types are allowed here. There might be complex
          // arrays/objects in EntityValueWrapper if no property information is
          // provided (example: the "data" property of commerce_price fields.
          if (is_scalar($property->value())) {
            $escaped = $doc->createTextNode($property->value());
            $element = $doc->createElement(is_numeric($name) ? 'item' : $name);
            $element->appendChild($escaped);
            $parent->appendChild($element);
          }
        }
        elseif ($property instanceof EntityListWrapper || $property instanceof EntityStructureWrapper) {
          $element = $doc->createElement(is_numeric($name) ? 'item' : $name);
          $parent->appendChild($element);
          self::addToXML($doc, $element, $property);
        }
      }
      catch (EntityMetadataWrapperException $e) {
        // A property causes problems - ignore that.
      }
    }
  }

  public static function setXMLReference(DOMElement $node, $resource, $id) {
    $node->nodeValue = restws_resource_uri($resource, $id);
    $node->setAttribute('resource', $resource);
    $node->setAttribute('id', $id);
  }
}

/**
 * A simple formatter for RDF. Requires the RDF module for the mapping.
 */
class RestWSFormatRDF extends RestWSBaseFormat {

  protected $namespaces;

  public function __construct($name, $info) {
    parent::__construct($name, $info);
    $this->namespaces = rdf_get_namespaces();
    $this->namespaces['rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
  }

  /**
   * Gets the representation of a resource.
   */
  public function viewResource($resourceController, $id) {
    $xml = new DOMDocument('1.0', 'utf-8');
    $rdf_element = $xml->createElementNS($this->namespaces['rdf'], 'rdf:RDF');
    $xml->appendChild($rdf_element);

    $element = $xml->createElementNS($this->namespaces['rdf'], 'rdf:Description');
    $element->setAttributeNS($this->namespaces['rdf'], 'rdf:about', restws_resource_uri($resourceController->resource(), $id));

    // Add the RDF type of the resource if there is a mapping.
    $entity = $resourceController->read($id);
    if (!empty($entity->rdf_mapping['rdftype'])) {
      foreach ($entity->rdf_mapping['rdftype'] as $rdf_type) {
        $type_element = $xml->createElementNS($this->namespaces['rdf'], 'rdf:type');
        list($ns, $name) = explode(':', $rdf_type);
        $type_element->setAttributeNS($this->namespaces['rdf'], 'rdf:resource', $this->namespaces[$ns] . $name);
        $element->appendChild($type_element);
      }
    }

    $this->addToXML($xml, $element, $resourceController->wrapper($id));
    $rdf_element->appendChild($element);

    $function = __FUNCTION__;
    drupal_alter('restws_response', $xml, $function, $this->formatName);

    return $xml->saveXML();
  }

  public function createResource($resourceController, $data) {
    throw new RestWSException('Not implemented', 501);
  }

  public function updateResource($resourceController, $id, $data) {
    throw new RestWSException('Not implemented', 501);
  }

  public function queryResource($resourceController, $parameters) {
    throw new RestWSException('Not implemented', 501);
  }

  /**
   * Adds the data of the given wrapper to the given XML element.
   */
  public function addToXML(DOMDocument $doc, DOMNode $parent, $wrapper) {
    $filtered = restws_property_access_filter($wrapper);
    foreach ($filtered as $name => $property) {
      try {
        if ($property instanceof EntityDrupalWrapper) {
          // For referenced entities only return the URI.
          if ($id = $property->getIdentifier()) {
            $element = $this->addRdfElement($doc, $wrapper, $name);
            $parent->appendChild($element);
            $this->addReference($doc, $element, $property->type(), $id);
          }
        }
        elseif ($property instanceof EntityValueWrapper) {
          $element = $this->addRdfElement($doc, $wrapper, $name);
          $parent->appendChild($element);
          $element->nodeValue = $property->value();
        }
        elseif ($property instanceof EntityListWrapper || $property instanceof EntityStructureWrapper) {
          $element = $this->addRdfElement($doc, $wrapper, $name);
          $parent->appendChild($element);
          $node = $doc->createElementNS($this->namespaces['rdf'], 'rdf:Description');
          $element->appendChild($node);
          $this->addToXML($doc, $node, $property);
        }
      }
      catch (EntityMetadataWrapperException $e) {
        // A property causes problems - ignore that.
      }
    }
  }

  public function addReference(DomDocument $doc, DOMElement $node, $resource, $id) {
    $element = $doc->createElementNS($this->namespaces['rdf'], 'rdf:Description');
    $element->setAttributeNS($this->namespaces['rdf'], 'rdf:about', restws_resource_uri($resource, $id));
    $node->appendChild($element);
  }

  /**
   * Adds an RDF element for the given property of the wrapper using the RDF
   * mapping.
   */
  public function addRdfElement(DOMDOcument $doc, EntityMetadataWrapper $wrapper, $name) {
    if ($wrapper instanceof EntityDrupalWrapper) {
      $entity = $wrapper->value();
      if (!empty($entity->rdf_mapping[$name])) {
        // Just make use of the first predicate for now.
        $predicate = reset($entity->rdf_mapping[$name]['predicates']);
        list($ns, $qname) = explode(':', $predicate);
        $element = $doc->createElementNS($this->namespaces[$ns], $predicate);

        if (!empty($entity->rdf_mapping[$name]['datatype'])) {
          $element->setAttributeNS($this->namespaces['rdf'], 'rdf:datatype', $entity->rdf_mapping[$name]['datatype']);
        }
      }
    }
    if (!isset($element)) {
      // For other elements just use the site URL as namespace.
      $element = $doc->createElementNS(url('', array('absolute' => TRUE)), 'site:' . (is_numeric($name) ? 'item' : $name));
    }
    return $element;
  }
}
