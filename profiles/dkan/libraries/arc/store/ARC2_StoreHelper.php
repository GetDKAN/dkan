<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 RDF Store Helper
author:   Benjamin Nowack
version:  2010-11-16
*/

ARC2::inc('Class');

class ARC2_StoreHelper extends ARC2_Class {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {/* db_con */
    parent::__init();
    $this->store = $this->caller;
  }

  /*  */

  function changeNamespaceURI($old_uri, $new_uri) {
    $id_changes = 0;
    $t_changes = 0;
    /* table lock */
    if ($this->store->getLock()) {
      $con = $this->store->getDBCon();
      foreach (array('id', 's', 'o') as $id_col) {
        $tbl = $this->store->getTablePrefix() . $id_col . '2val';
        $sql = 'SELECT id, val FROM ' . $tbl . ' WHERE val LIKE "' . mysql_real_escape_string($old_uri, $con). '%"';
        $rs = mysql_query($sql, $con);
        if (!$rs) continue;
        while ($row = mysql_fetch_array($rs)) {
          $new_val = str_replace($old_uri, $new_uri, $row['val']);
          $new_id = $this->store->getTermID($new_val, $id_col);
          if (!$new_id) {/* unknown ns uri, overwrite current id value */
            $sub_sql = "UPDATE " . $tbl . " SET val = '" . mysql_real_escape_string($new_val, $con) . "' WHERE id = " . $row['id'];
            $sub_r = mysql_query($sub_sql, $con);
            $id_changes++;
          }
          else {/* replace ids */
            $t_tbls = $this->store->getTables();
            foreach ($t_tbls as $t_tbl) {
              if (preg_match('/^triple/', $t_tbl)) {
                foreach (array('s', 'p', 'o', 'o_lang_dt') as $t_col) {
                  $sub_sql = "UPDATE " . $this->store->getTablePrefix() . $t_tbl . " SET " . $t_col . " = " . $new_id . " WHERE " . $t_col . " = " . $row['id'];
                  $sub_r = mysql_query($sub_sql, $con);
                  $t_changes += mysql_affected_rows($con);
                }
              }
            }
          }
        }
      }
      $this->store->releaseLock();
    }
    return array('id_replacements' => $id_changes, 'triple_updates' => $t_changes);
  }
  
  /*  */

}
