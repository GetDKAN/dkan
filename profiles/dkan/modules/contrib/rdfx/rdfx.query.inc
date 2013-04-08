<?php

/**
 * @file
 * Functions for querying with SPARQL or extracting triples from an ARC2-style
 * data structure.
 */

function _rdfx_query_ask(&$model, $queries) {
  foreach ($queries as $query) {
    list($s, $p, $o) = $query;
    if (_rdfx_query_find_first($model, $s, $p, $o)) return true;
  }
  return false;
}

function _rdfx_query_find_literal(&$model, $queries) {
  $literal = array();
  foreach ($queries as $query) {
    list($s, $p, $o) = $query;
    $triples = _rdfx_query_find_all($model, $s, $p, $o);
    // We create an associative array based on the language code of the
    // literal. The language codes Drupal uses are specified in includes/iso.inc.
    foreach ($triples as $triple) {
      if ($triple['o_lang'] !== '') {
        // Chinese and Portuguese are the only languages with a >2 letter
        // langcode.
        if (preg_match('/(zh-hans)|(zh-hant)|(pt-pt)|(pt-br)/', $triple['o_lang'])) {
          $langcode = $triple['o_lang'];
        }
        // Remove the region code if it is used (i.e. en-US is changed to en).
        else {
          $lang_array = explode('-', $triple['o_lang']);
          $langcode = !empty($lang_array[0]) ? $lang_array[0] : $triple['o_lang'];
        }
      }
      else {
        $langcode = 'und';
      }
      $literal[$langcode] = $triple['o'];
    }
  }
  return $literal;
}

function _rdfx_query_find_uris(&$model, $queries) {
  $uris = array();
  foreach ($queries as $query) {
    list($s, $p, $o) = $query;
    $result = _rdfx_query_find_all($model, $s, $p, $o);
    foreach ($result as $triple) {
      if ($s == '?' && $triple['s_type'] == 'uri') {
        $uris[] = $triple['s'];
      }
      if ($p == '?') {
        $uris[] = $triple['p'];
      }
      if ($o == '?' && $triple['o_type'] == 'uri') {
        $uris[] = $triple['o'];
      }
    }
  }
  return array_unique($uris);
}

function _rdfx_query_find_qnames(&$model, $queries) {
  $uris = _rdfx_query_find_uris($model, $queries);
  $qnames = array();
  foreach ($uris as $uri) {
    $qnames[] = $uri;
  }
  return $qnames;
}

function _rdfx_query_find_first(&$model, $s, $p, $o) {
  foreach ($model as $triple) {
    if (!is_null($s) && $s != '?' && ($triple['s'] != $s || $triple['s_type'] != 'uri')) continue;
    if (!is_null($p) && $p != '?' && ($triple['p'] != $p)) continue;
    if (!is_null($o) && $o != '?' && ($triple['o'] != $o || $triple['o_type'] != 'uri')) continue;
    return $triple;
  }
  return null;
}

function _rdfx_query_find_all(&$model, $s, $p, $o) {
  $result = array();
  foreach ($model as $triple) {
    if (!is_null($s) && $s != '?' && ($triple['s'] != $s)) continue;
    if (!is_null($p) && $p != '?' && ($triple['p'] != $p)) continue;
    if (!is_null($o) && $o != '?' && ($triple['o'] != $o)) continue;
    $result[] = $triple;
  }
  return $result;
}
