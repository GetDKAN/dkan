<?php
/**
 * ARC2 core class (static, not instantiated)
 *
 * @author Benjamin Nowack
 * @license <http://arc.semsol.org/license>
 * @homepage <http://arc.semsol.org/>
 * @package ARC2
 */

/* E_STRICT hack */
if (function_exists('date_default_timezone_get')) {
  date_default_timezone_set(@date_default_timezone_get());
}

class ARC2 {

  static function getVersion() {
    return '2011-12-01';
  }

  /*  */
  
  static function getIncPath($f = '') {
    $r = realpath(dirname(__FILE__)) . '/';
    $dirs = array(
      'plugin' => 'plugins',
      'trigger' => 'triggers',
      'store' => 'store', 
      'serializer' => 'serializers', 
      'extractor' => 'extractors', 
      'sparqlscript' => 'sparqlscript', 
      'parser' => 'parsers', 
    );
    foreach ($dirs as $k => $dir) {
      if (preg_match('/' . $k . '/i', $f)) {
        return $r . $dir . '/';
      }
    }
    return $r;
  }
  
  static function getScriptURI() {
    if (isset($_SERVER) && (isset($_SERVER['SERVER_NAME']) || isset($_SERVER['HTTP_HOST']))) {
      $proto = preg_replace('/^([a-z]+)\/.*$/', '\\1', strtolower($_SERVER['SERVER_PROTOCOL']));
      $port = $_SERVER['SERVER_PORT'];
      $server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
      $script = $_SERVER['SCRIPT_NAME'];
      /* https */
      if (($proto == 'http') && $port == 443) {
        $proto = 'https';
        $port = 80;
      }
      return $proto . '://' . $server . ($port != 80 ? ':' . $port : '') . $script;
      /*
      return preg_replace('/^([a-z]+)\/.*$/', '\\1', strtolower($_SERVER['SERVER_PROTOCOL'])) . 
        '://' . $_SERVER['SERVER_NAME'] .
        ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '') .
        $_SERVER['SCRIPT_NAME'];
      */
    }
    elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
      return 'file://' . realpath($_SERVER['SCRIPT_FILENAME']);
    }
    return 'http://localhost/unknown_path';
  }

  static function getRequestURI() {
    if (isset($_SERVER) && isset($_SERVER['REQUEST_URI'])) {
      return preg_replace('/^([a-z]+)\/.*$/', '\\1', strtolower($_SERVER['SERVER_PROTOCOL'])) . 
        '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']) .
        ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '') .
        $_SERVER['REQUEST_URI'];
    }
    return ARC2::getScriptURI();
  }
  
  static function inc($f, $path = '') {
    $prefix = 'ARC2';
    if (preg_match('/^([^\_]+)\_(.*)$/', $f, $m)) {
      $prefix = $m[1];
      $f = $m[2];
    }
    $inc_path = $path ? $path : ARC2::getIncPath($f);
    $path = $inc_path . $prefix . '_' . urlencode($f) . '.php';
    if (file_exists($path)) return include_once($path);
    /* safe-mode hack */
    if (@include_once($path)) return 1;
    /* try other path */
    if ($prefix != 'ARC2') {
      $path = $inc_path . strtolower($prefix) . '/' . $prefix . '_' . urlencode($f) . '.php';
      if (file_exists($path)) return include_once($path);
      /* safe-mode hack */
      if (@include_once($path)) return 1;
    }
    return 0;
  }
  
  /*  */

  static function mtime(){
    list($msec, $sec) = explode(" ", microtime());
    return ((float)$msec + (float)$sec);
  }
  
  static function x($re, $v, $options = 'si') {
    return preg_match("/^\s*" . $re . "(.*)$/" . $options, $v, $m) ? $m : false;
  }

  /*  */

  static function getFormat($val, $mtype = '', $ext = '') {
    ARC2::inc('getFormat');
    return ARC2_getFormat($val, $mtype, $ext);
  }
  
  static function getPreferredFormat($default = 'plain') {
    ARC2::inc('getPreferredFormat');
    return ARC2_getPreferredFormat($default);
  }
  
  /*  */
  
  static function toUTF8($v) {
    if (urlencode($v) === $v) return $v;
    //if (utf8_decode($v) == $v) return $v;
    $v = (strpos(utf8_decode(str_replace('?', '', $v)), '?') === false) ? utf8_decode($v) : $v;
    /* custom hacks, mainly caused by bugs in PHP's json_decode */
    $mappings = array(
      '%18' => '‘',
      '%19' => '’',
      '%1C' => '“',
      '%1D' => '”',
      '%1E' => '„',
      '%10' => '‐',
      '%12' => '−',
      '%13' => '–',
      '%14' => '—',
      '%26' => '&',
    );
    $froms = array_keys($mappings);
    $tos = array_values($mappings);
    foreach ($froms as $i => $from) $froms[$i] = urldecode($from);
    $v = str_replace($froms, $tos, $v);
    /* utf8 tweaks */
    return preg_replace_callback('/([\x00-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5}|[^\x00-\x7f])/', array('ARC2', 'getUTF8Char'), $v);
  }
  
  static function getUTF8Char($v) {
    $val = $v[1];
    if (strlen(trim($val)) === 1) return utf8_encode($val);
    if (preg_match('/^([\x00-\x7f])(.+)/', $val, $m)) return $m[1] . ARC2::toUTF8($m[2]);
    return $val;
  }

  /*  */

  static function splitURI($v) {
    /* the following namespaces may lead to conflated URIs,
     * we have to set the split position manually
    */
    if (strpos($v, 'www.w3.org')) {
      $specials = array(
        'http://www.w3.org/XML/1998/namespace',
        'http://www.w3.org/2005/Atom',
        'http://www.w3.org/1999/xhtml',
      );
      foreach ($specials as $ns) {
        if (strpos($v, $ns) === 0) {
          $local_part = substr($v, strlen($ns));
          if (!preg_match('/^[\/\#]/', $local_part)) {
            return array($ns, $local_part);
          }
        }
      }
    }
    /* auto-splitting on / or # */
    //$re = '^(.*?)([A-Z_a-z][-A-Z_a-z0-9.]*)$';
    if (preg_match('/^(.*[\/\#])([^\/\#]+)$/', $v, $m)) return array($m[1], $m[2]);
    /* auto-splitting on last special char, e.g. urn:foo:bar */
    if (preg_match('/^(.*[\:\/])([^\:\/]+)$/', $v, $m)) return array($m[1], $m[2]);
    return array($v, '');
  }
  
  /*  */

  static function getSimpleIndex($triples, $flatten_objects = 1, $vals = '') {
    $r = array();
    foreach ($triples as $t) {
      $skip_t = 0;
      foreach (array('s', 'p', 'o') as $term) {
        $$term = $t[$term];
        /* template var */
        if (isset($t[$term . '_type']) && ($t[$term . '_type'] == 'var')) {
          $val = isset($vals[$$term]) ? $vals[$$term] : '';
          $skip_t = isset($vals[$$term]) ? $skip_t : 1;
          $type = '';
          $type = !$type && isset($vals[$$term . ' type']) ? $vals[$$term . ' type'] : $type;
          $type = !$type && preg_match('/^\_\:/', $val) ? 'bnode' : $type;
          if ($term == 'o') {
            $type = !$type && (preg_match('/\s/s', $val) || !preg_match('/\:/', $val)) ? 'literal' : $type;
            $type = !$type && !preg_match('/[\/]/', $val) ? 'literal' : $type;
          }
          $type = !$type ? 'uri' : $type;
          $t[$term . '_type'] =  $type;
          $$term = $val;
        }
      }
      if ($skip_t) {
        continue;
      }
      if (!isset($r[$s])) $r[$s] = array();
      if (!isset($r[$s][$p])) $r[$s][$p] = array();
      if ($flatten_objects) {
        if (!in_array($o, $r[$s][$p])) $r[$s][$p][] = $o;
      }
      else {
        $o = array('value' => $o);
        foreach (array('lang', 'type', 'datatype') as $suffix) {
          if (isset($t['o_' . $suffix]) && $t['o_' . $suffix]) {
            $o[$suffix] = $t['o_' . $suffix];
          }
          elseif (isset($t['o ' . $suffix]) && $t['o ' . $suffix]) {
            $o[$suffix] = $t['o ' . $suffix];
          }
        }
        if (!in_array($o, $r[$s][$p])) {
          $r[$s][$p][] = $o;
        }
      }
    }
    return $r;
  }
  
  static function getTriplesFromIndex($index) {
    $r = array();
    foreach ($index as $s => $ps) {
      foreach ($ps as $p => $os) {
        foreach ($os as $o) {
          $r[] = array(
            's' => $s,
            'p' => $p,
            'o' => $o['value'],
            's_type' => preg_match('/^\_\:/', $s) ? 'bnode' : 'uri',
            'o_type' => $o['type'],
            'o_datatype' => isset($o['datatype']) ? $o['datatype'] : '',
            'o_lang' => isset($o['lang']) ? $o['lang'] : '',
          );
        }
      }
    }
    return $r;
  }

  static function getMergedIndex() {
    $r = array();
    foreach (func_get_args() as $index) {
      foreach ($index as $s => $ps) {
        if (!isset($r[$s])) $r[$s] = array();
        foreach ($ps as $p => $os) {
          if (!isset($r[$s][$p])) $r[$s][$p] = array();
          foreach ($os as $o) {
            if (!in_array($o, $r[$s][$p])) {
              $r[$s][$p][] = $o;
            }
          }
        }
      }
    }
    return $r;
  }
  
  static function getCleanedIndex() {/* removes triples from a given index */
    $indexes = func_get_args();
    $r = $indexes[0];
    for ($i = 1, $i_max = count($indexes); $i < $i_max; $i++) {
      $index = $indexes[$i];
      foreach ($index as $s => $ps) {
        if (!isset($r[$s])) continue;
        foreach ($ps as $p => $os) {
          if (!isset($r[$s][$p])) continue;
          $r_os = $r[$s][$p];
          $new_os = array();
          foreach ($r_os as $r_o) {
            $r_o_val = is_array($r_o) ? $r_o['value'] : $r_o;
            $keep = 1;
            foreach ($os as $o) {
              $del_o_val = is_array($o) ? $o['value'] : $o;
              if ($del_o_val == $r_o_val) {
                $keep = 0;
                break;
              }
            }
            if ($keep) {
              $new_os[] = $r_o;
            }
          }
          if ($new_os) {
            $r[$s][$p] = $new_os;
          }
          else {
            unset($r[$s][$p]);
          }
        }
      }
    }
    /* check r */
    $has_data = 0;
    foreach ($r as $s => $ps) {
      if ($ps) {
        $has_data = 1;
        break;
      }
    }
    return $has_data ? $r : array();
  }
  
  /*  */

  static function getStructType($v) {
    /* string */
    if (is_string($v)) return 'string';
    /* flat array, numeric keys */
    if (in_array(0, array_keys($v))) {/* numeric keys */
      /* simple array */
      if (!is_array($v[0])) return 'array';
      /* triples */
      //if (isset($v[0]) && isset($v[0]['s']) && isset($v[0]['p'])) return 'triples';
      if (in_array('p', array_keys($v[0]))) return 'triples';
    }
    /* associative array */
    else {
      /* index */
      foreach ($v as $s => $ps) {
        if (!is_array($ps)) break;
        foreach ($ps as $p => $os) {
          if (!is_array($os) || !is_array($os[0])) break;
          if (in_array('value', array_keys($os[0]))) return 'index';
        }
      }
    }
    /* array */
    return 'array';
  }

  /*  */

  static function getComponent($name, $a = '', $caller = '') {
    ARC2::inc($name);
    $prefix = 'ARC2';
    if (preg_match('/^([^\_]+)\_(.+)$/', $name, $m)) {
      $prefix = $m[1];
      $name = $m[2];
    }
    $cls = $prefix . '_' . $name;
    if (!$caller) $caller = new stdClass();
    return new $cls($a, $caller);
  }
  
  /* resource */

  static function getResource($a = '') {
    return ARC2::getComponent('Resource', $a);
  }

  /* reader */

  static function getReader($a = '') {
    return ARC2::getComponent('Reader', $a);
  }

  /* parsers */

  static function getParser($prefix, $a = '') {
    return ARC2::getComponent($prefix . 'Parser', $a);
  }

  static function getRDFParser($a = '') {
    return ARC2::getParser('RDF', $a);
  }

  static function getRDFXMLParser($a = '') {
    return ARC2::getParser('RDFXML', $a);
  }

  static function getTurtleParser($a = '') {
    return ARC2::getParser('Turtle', $a);
  }

  static function getRSSParser($a = '') {
    return ARC2::getParser('RSS', $a);
  }

  static function getSemHTMLParser($a = '') {
    return ARC2::getParser('SemHTML', $a);
  }

  static function getSPARQLParser($a = '') {
    return ARC2::getComponent('SPARQLParser', $a);
  }

  static function getSPARQLPlusParser($a = '') {
    return ARC2::getParser('SPARQLPlus', $a);
  }

  static function getSPARQLXMLResultParser($a = '') {
    return ARC2::getParser('SPARQLXMLResult', $a);
  }

  static function getJSONParser($a = '') {
    return ARC2::getParser('JSON', $a);
  }

  static function getSGAJSONParser($a = '') {
    return ARC2::getParser('SGAJSON', $a);
  }

  static function getCBJSONParser($a = '') {
    return ARC2::getParser('CBJSON', $a);
  }

  static function getSPARQLScriptParser($a = '') {
    return ARC2::getParser('SPARQLScript', $a);
  }

  /* store */

  static function getStore($a = '', $caller = '') {
    return ARC2::getComponent('Store', $a, $caller);
  }

  static function getStoreEndpoint($a = '', $caller = '') {
    return ARC2::getComponent('StoreEndpoint', $a, $caller);
  }

  static function getRemoteStore($a = '', $caller = '') {
    return ARC2::getComponent('RemoteStore', $a, $caller);
  }

  static function getMemStore($a = '') {
    return ARC2::getComponent('MemStore', $a);
  }
  
  /* serializers */

  static function getSer($prefix, $a = '') {
    return ARC2::getComponent($prefix . 'Serializer', $a);
  }

  static function getTurtleSerializer($a = '') {
    return ARC2::getSer('Turtle', $a);
  }

  static function getRDFXMLSerializer($a = '') {
    return ARC2::getSer('RDFXML', $a);
  }

  static function getNTriplesSerializer($a = '') {
    return ARC2::getSer('NTriples', $a);
  }

  static function getRDFJSONSerializer($a = '') {
    return ARC2::getSer('RDFJSON', $a);
  }

  static function getPOSHRDFSerializer($a = '') {/* deprecated */
    return ARC2::getSer('POSHRDF', $a);
  }

  static function getMicroRDFSerializer($a = '') {
    return ARC2::getSer('MicroRDF', $a);
  }

  static function getRSS10Serializer($a = '') {
    return ARC2::getSer('RSS10', $a);
  }

  /* sparqlscript */

  static function getSPARQLScriptProcessor($a = '') {
    return ARC2::getComponent('SPARQLScriptProcessor', $a);
  }

  /*  */
  
}
