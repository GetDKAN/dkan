<?php

/**
 * @file
 * Set permissions on group to upgrade group visibility.
 */

class OgUiSetRoles extends Migration {

  /**
   * Indicate we are updating existing data.
   */
  protected $systemOfRecord = Migration::DESTINATION;

  public function __construct() {
    parent::__construct();
    $this->description = t('Set permissions on group to upgrade group visibility.');

    foreach (node_type_get_names() as $bundle => $value) {
      // Dependent on a dynamic migration.
      $machine_name = 'OgUiPopulateField' . ucfirst($bundle);
      if (MigrationBase::getInstance($machine_name, 'OgUiPopulateField', array('bundle' => $bundle))) {
        $this->dependencies[] = $machine_name;
      }
    }

    $query = db_select('d6_og', 'og');
    $query->innerJoin('node', 'n', 'og.nid = n.nid');

    $query
      ->fields('og', array('og_selective'))
      ->fields('n', array('nid', 'type'))
      ->orderBy('n.nid');

    $this->query = $query;

    $source_key = MigrateDestinationNode::getKeySchema();
    // Set the alias, so the query in MigrateSourceSQL::performRewind()
    // will not fail.
    $source_key['nid']['alias'] = 'n';

    $this->map = new MigrateSQLMap($this->machineName, $source_key, MigrateDestinationNode::getKeySchema());
    $this->source = new MigrateSourceSQL($this->query);
    $this->destination = new MigrateDestinationTable('node');

    $this->addFieldMapping('nid', 'nid');
  }

  public function prepareRow($row) {
    $anon_permissions = array();
    $auth_permissions = array('unsubscribe' => TRUE);
    switch ($row->og_selective) {
      // Open
      case 0:
        $anon_permissions = array(
          'subscribe' => FALSE,
          'subscribe without approval' => TRUE,
        );
        break;

      // Moderated.
      case 1:
        $anon_permissions = array(
          'subscribe' => TRUE,
          'subscribe without approval' => FALSE,
        );
        break;

      // Invite only.
      case 2:
        $anon_permissions = array(
          'subscribe' => FALSE,
          'subscribe without approval' => FALSE,
        );
        break;

      // Closed.
      case 3:
        $anon_permissions = array(
          'subscribe' => FALSE,
          'subscribe without approval' => FALSE,
        );

        $auth_permissions = array('unsubscribe' => FALSE);
        break;
    }

    $og_roles = og_roles('node', $row->type, $row->nid);
    $permissions = og_role_permissions($og_roles);

    $anon_rid = array_search(OG_ANONYMOUS_ROLE, $og_roles);
    $auth_rid = array_search(OG_AUTHENTICATED_ROLE, $og_roles);

    // Set the new permissions.
    og_role_change_permissions($anon_rid, $anon_permissions);
    og_role_change_permissions($auth_rid, $auth_permissions);

    // We don't need to really save the node, but if we return FALSE,
    // then the row is not counted as imported.
    return TRUE;
  }
}
