<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 Legacy XML Serializer
author:   Benjamin Nowack
version:  2010-11-16
*/

ARC2::inc('Class');

class ARC2_LegacyHTMLSerializer extends ARC2_Class {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {
    parent::__init();
    $this->content_header = 'text/html';
  }

  /*  */
  
  function getSerializedArray($struct, $root = 1, $ind = ' ') {
    $n = "\n";
    $r = '';
    $is_flat = $this->isAssociativeArray($struct) ? 0 : 1;
    foreach ($struct as $k => $v) {
      if (!$is_flat) $r .= $n . $ind . $ind . '<dt>' . $k . '</dt>';
      $r .= $n . $ind . $ind . '<dd>' . (is_array($v) ? $this->getSerializedArray($v, 0, $ind . $ind . $ind) . $n . $ind . $ind : htmlspecialchars($v)) . '</dd>';
    }
    return $n . $ind . '<dl>' . $r . $n . $ind . '</dl>';
  }
  
  /*  */

  function isAssociativeArray($v) {
    foreach (array_keys($v) as $k => $val) {
      if ($k !== $val) return 1;
    }
    return 0;
  }
  
  /*  */

  function getSerializedNode($index, $node, $level = 0, $raw = 0) {
    $r = '';
    $tag = $this->v('tag', '', $node);
    if (preg_match('/^(comment|script)$/', $tag)) {
    }
    elseif ($tag == 'cdata') {
      $r .= $this->v('cdata', '', $node);
      $r .= $this->v('value', '', $node['a']);
    }
    else {
      /* open tag */
      if (preg_match('/^(div|form|p|section)$/', $tag)) {
        $r .= str_pad("\n", $level + 1, "  ");
      }
      $r .= '<' . $tag;
      $attrs = $this->v('a', array(), $node);
      foreach ($attrs as $k => $v) {
        /* use uri, if detected */
        if ($k != 'id') {
          $v = $this->v($k . ' uri', $v, $attrs);
        }
        /* skip arrays and other derived attrs */
        if (preg_match('/\s/s', $k)) continue;
        $r .= ' ' . $k . '="' . $v . '"';
      }
      if ($node['empty']) {
        $r .= '/>';
      }
      else {
        $r .= '>';
        /* cdata */
        $r .= $this->v('cdata', '', $node);
        /* sub-nodes */
        $sub_nodes = $this->v($node['id'], array(), $index);
        foreach ($sub_nodes as $sub_node) {
          $r .= $this->getSerializedNode($index, $sub_node, $level + 1, 1);
        }
        /* close tag */
        //$r .= str_pad("\n", $level + 1, "  ") . '</' . $tag . '>';
        $r .= '</' . $tag . '>';
        if (preg_match('/^(div|form|p|section)$/', $tag)) {
          $r .= str_pad("\n", $level + 1, "  ");
        }
      }
    }
    /* doc envelope, in case of sub-structure serializing */
    if (!$raw && ($level == 0) && ($node['level'] > 1)) {
      $r = '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <head>
  <body>
    ' . $r . '
  </body>
</html>
     ';
    }
    return $r;
  }

  /*  */
}

