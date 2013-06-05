<?php
/**
 * @file
 * Contains the SearchApiAlterNodeAccess class.
 */

/**
 * Adds node access information to node indexes.
 */
class SearchApiAlterNodeAccess extends SearchApiAbstractAlterCallback {

  /**
   * Check whether this data-alter callback is applicable for a certain index.
   *
   * Returns TRUE only for indexes on nodes.
   *
   * @param SearchApiIndex $index
   *   The index to check for.
   *
   * @return boolean
   *   TRUE if the callback can run on the given index; FALSE otherwise.
   */
  public function supportsIndex(SearchApiIndex $index) {
    // Currently only node access is supported.
    return $index->getEntityType() === 'node';
  }

  /**
   * Declare the properties that are (or can be) added to items with this callback.
   *
   * Adds the "search_api_access_node" property.
   *
   * @see hook_entity_property_info()
   *
   * @return array
   *   Information about all additional properties, as specified by
   *   hook_entity_property_info() (only the inner "properties" array).
   */
  public function propertyInfo() {
    return array(
      'search_api_access_node' => array(
        'label' => t('Node access information'),
        'description' => t('Data needed to apply node access.'),
        'type' => 'list<token>',
      ),
    );
  }

  /**
   * Alter items before indexing.
   *
   * Items which are removed from the array won't be indexed, but will be marked
   * as clean for future indexing. This could for instance be used to implement
   * some sort of access filter for security purposes (e.g., don't index
   * unpublished nodes or comments).
   *
   * @param array $items
   *   An array of items to be altered, keyed by item IDs.
   */
  public function alterItems(array &$items) {
    static $account;

    if (!isset($account)) {
      // Load the anonymous user.
      $account = drupal_anonymous_user();
    }

    foreach ($items as $nid => &$item) {
      // Check whether all users have access to the node.
      if (!node_access('view', $item, $account)) {
        // Get node access grants.
        $result = db_query('SELECT * FROM {node_access} WHERE (nid = 0 OR nid = :nid) AND grant_view = 1', array(':nid' => $item->nid));

        // Store all grants together with it's realms in the item.
        foreach ($result as $grant) {
          if (!isset($items[$nid]->search_api_access_node)) {
            $items[$nid]->search_api_access_node = array();
          }
          $items[$nid]->search_api_access_node[] = "node_access_$grant->realm:$grant->gid";
        }
      }
      else {
        // Add the generic view grant if we are not using node access or the
        // node is viewable by anonymous users.
        $items[$nid]->search_api_access_node = array('node_access__all');
      }
    }
  }

  /**
   * Submit callback for the configuration form.
   *
   * If the data alteration is being enabled, set "Published" and "Author" to
   * "indexed", because both are needed for the node access filter.
   */
  public function configurationFormSubmit(array $form, array &$values, array &$form_state) {
    $old_status = !empty($form_state['index']->options['data_alter_callbacks']['search_api_alter_node_access']['status']);
    $new_status = !empty($form_state['values']['callbacks']['search_api_alter_node_access']['status']);

    if (!$old_status && $new_status) {
      $form_state['index']->options['fields']['status']['type'] = 'boolean';
      $form_state['index']->options['fields']['author']['type'] = 'integer';
      $form_state['index']->options['fields']['author']['entity_type'] = 'user';
    }

    return parent::configurationFormSubmit($form, $values, $form_state);
  }

}
