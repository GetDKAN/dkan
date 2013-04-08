<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 Store Turtle Loader
author:   Benjamin Nowack
version:  2010-11-16
*/

ARC2::inc('TurtleParser');

class ARC2_StoreTurtleLoader extends ARC2_TurtleParser {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {
    parent::__init();
  }

  /*  */
  
  function addT($t) {
    $this->caller->addT($t['s'], $t['p'], $t['o'], $t['s_type'], $t['o_type'], $t['o_datatype'], $t['o_lang']);
    $this->t_count++;
  }

  /*  */

}
