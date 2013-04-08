<?php

/**
 * A base formatter for all RDF serialization formats.
 */
class RDFxRestWSBaseFormat extends RestWSBaseFormat {
  /**
   * Read only support.
   */
  public function createResource($resourceController, $data) {
    throw new RestWSException('Not implemented', 501);
  }

  public function updateResource($resourceController, $id, $data) {
    throw new RestWSException('Not implemented', 501);
  }

  public function deleteResource($resourceController, $id) {
    throw new RestWSException('Not implemented', 501);
  }
}

/**
 * Formatters for all RDF serialization formats.
 */
class RDFxRestWSFormatRDFXML extends RDFxRestWSBaseFormat {
  public function viewResource($resourceController, $id) {
    $resource = rdfx_get_rdf_model($resourceController->resource(), $id);
    $serializer = ARC2::getSer('RDFXML', array('ns' => $resource->ns));
    return $serializer->getSerializedIndex($resource->index);
  }
}

class RDFxRestWSFormatTurtle extends RDFxRestWSBaseFormat {
  public function viewResource($resourceController, $id) {
    $resource = rdfx_get_rdf_model($resourceController->resource(), $id);
    $serializer = ARC2::getSer('Turtle', array('ns' => $resource->ns));
    return $serializer->getSerializedIndex($resource->index);
  }
}

class RDFxRestWSFormatNTriples extends RDFxRestWSBaseFormat {
  public function viewResource($resourceController, $id) {
    $resource = rdfx_get_rdf_model($resourceController->resource(), $id);
    $serializer = ARC2::getSer('NTriples', array('ns' => $resource->ns));
    return $serializer->getSerializedIndex($resource->index);
  }
}

class RDFxRestWSFormatRDFJSON extends RDFxRestWSBaseFormat {
  public function viewResource($resourceController, $id) {
    $resource = rdfx_get_rdf_model($resourceController->resource(), $id);
    $serializer = ARC2::getSer('RDFJSON', array('ns' => $resource->ns));
    return $serializer->getSerializedIndex($resource->index);
  }
}
