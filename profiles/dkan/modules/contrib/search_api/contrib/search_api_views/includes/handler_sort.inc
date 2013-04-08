<?php

/**
 * Class for sorting results according to a specified field.
 */
class SearchApiViewsHandlerSort extends views_handler_sort {

  /**
   * The associated views query object.
   *
   * @var SearchApiViewsQuery
   */
  public $query;

  /**
   * Called to add the sort to a query.
   */
  public function query() {
    // When there are exposed sorts, the "exposed form" plugin will set
    // $query->orderby to an empty array. Therefore, if that property is set,
    // we here remove all previous sorts.
    if (isset($this->query->orderby)) {
      unset($this->query->orderby);
      $sort = &$this->query->getSort();
      $sort = array();
    }
    $this->query->sort($this->real_field, $this->options['order']);
  }

}
