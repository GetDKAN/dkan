<?php


/**
 * @file
 * Contains various relationship handlers.
 */

/**
 * Specialized relationship handler associating groups and their entity.
 *
 * @ingroup views
 */
class og_handler_relationship extends views_handler_relationship {

  /**
   * Called to implement a relationship in a query.
   */
  function query() {

    // If the entity type is specific for the og_membership
    // filter the join to select membership of those entity types.
    if (isset($this->definition['entity'])) {
      $extra = array(
        'field' => 'entity_type',
        'value' => $this->definition['entity'],
      );

      // Only add the table if og_membership is the left table since when the
      // table is specified to views_join, it only translates the table alias
      // of tables in the left position, however if no table is specified,
      // then views_join correctly inserts the alias of the table in the right
      // position of the join.
      if ($this->definition['base'] != 'og_membership') {
        $extra['table'] = 'og_membership';
      }

      $this->definition['extra'][] = $extra;
    }

    // If the group type is specific for the og_membership
    // filter the join to select membership of those group types.
    if (isset($this->definition['group_type'])) {
      $extra = array(
        'field' => 'group_type',
        'value' => $this->definition['group_type'],
      );

      // Only add the table if og_membership is the left table since when the
      // table is specified to views_join, it only translates the table alias
      // of tables in the left position, however if no table is specified,
      // then views_join correctly inserts the alias of the table in the right
      // position of the join.
      if ($this->definition['base'] != 'og_membership') {
        $extra['table'] = 'og_membership';
      }

      $this->definition['extra'][] = $extra;
    }
    parent::query();
  }
}
