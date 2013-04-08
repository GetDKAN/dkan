<?php

function _evoc_load__error_handler($errno, $errstr) {
    throw new Exception($errstr);
}

function _evoc_query_for_term_description(&$model, $uri) {
  $label = _rdfx_query_find_literal($model, array(
    array($uri, 'http://www.w3.org/2000/01/rdf-schema#label', '?')
  ));

  $comment = _rdfx_query_find_literal($model, array(
    array($uri, 'http://www.w3.org/2004/02/skos/core#definition', '?'),
    array($uri, 'http://www.w3.org/2000/01/rdf-schema#comment', '?'),
  ));

  return array(
      'uri' => $uri,
      'label' => $label,
      'comment' => $comment,
  );
}