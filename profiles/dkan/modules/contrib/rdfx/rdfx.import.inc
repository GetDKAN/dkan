<?php

/**
 * @file
 * Functions for importing and parsing RDF data.
 */

/**
 * Loads an RDF file from an HTTP URI or local file, parses it, and builds an
 * RDF schema representation (associative array) from it.
 *
 * @param $uri
 *    The namespace URI for this graph.
 * @param $prefix
 *    The prefix for this graph.
 * @param $filename (optional)
 *    A local RDF file to parse.
 * @return array
 * @throws Exception On network or parse error
 */
function rdfx_fetch_rdf($uri, $prefix, $filename = NULL) {
  if ($filename != NULL) {
    if (!is_file($filename)) {
      throw new Exception("File not found: '$filename'");
      return;
    }
    $content = file_get_contents($filename);
    return _rdfx_parse_rdf($uri, $content);
  }

  $schema_url = $uri;
  $i = strpos($schema_url, '#');
  if ($i !== false) {
    $schema_url = substr($schema_url, 0, $i);
  }
  return _rdfx_parse_rdf($uri);
}

function _rdfx_parse_rdf($base_uri) {
  $namespaces = array();

  $parser = ARC2::getRDFParser();
  $parser->parse($base_uri);
  // If this is an N3 file, the namespaces array isn't populated. Iterate
  // through the attached parser object's prefixes and remove the colon from
  // the end of the prefix.
  // @todo File an issue with ARC2.

  switch ($parser->format) {
    case 'turtle':
      foreach ($parser->parser->prefixes as $prefix => $uri) {
        $formatted_prefix = str_replace(':', '', $prefix);
        $namespaces[$formatted_prefix] = $uri;
      }
      break;
    default:
      foreach ($parser->parser->nsp as $uri => $prefix) {
        $namespaces[$prefix] = $uri;
      }
      break;
  }
  return array($parser->getTriples(), $namespaces);
}
