<?php
/**
 * ARC2 RDF Store INSERT Query Handler
 *
 * @author Benjamin Nowack <bnowack@semsol.com>
 * @license http://arc.semsol.org/license
 * @homepage <http://arc.semsol.org/>
 * @package ARC2
*/

ARC2::inc('StoreQueryHandler');

class ARC2_StoreInsertQueryHandler extends ARC2_StoreQueryHandler {

  function __construct($a, &$caller) {/* caller has to be a store */
    parent::__construct($a, $caller);
  }
  
  function __init() {/* db_con */
    parent::__init();
    $this->store = $this->caller;
  }

  /*  */
  
  function runQuery($infos, $keep_bnode_ids = 0) {
    $this->infos = $infos;
    $con = $this->store->getDBCon();
    /* insert */
    if (!$this->v('pattern', array(), $this->infos['query'])) {
      $triples = $this->infos['query']['construct_triples'];
      /* don't execute empty INSERTs as they trigger a LOAD on the graph URI */
      if ($triples) {
        return $this->store->insert($triples, $this->infos['query']['target_graph'], $keep_bnode_ids);
      }
      else {
        return array('t_count' => 0, 'load_time' => 0);
      }
    }
    else {
      $keep_bnode_ids = 1;
      ARC2::inc('StoreConstructQueryHandler');
      $h = new ARC2_StoreConstructQueryHandler($this->a, $this->store);
      $sub_r = $h->runQuery($this->infos);
      if ($sub_r) {
        return $this->store->insert($sub_r, $this->infos['query']['target_graph'], $keep_bnode_ids);
      }
      return array('t_count' => 0, 'load_time' => 0);
    }
  }
  
  /*  */

}
