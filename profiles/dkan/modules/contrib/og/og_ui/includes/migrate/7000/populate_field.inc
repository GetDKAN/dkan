<?php

/**
 * @file
 * Upgrade group visibility (e.g. open, moderated, closed) from OG6.
 */

class OgUiPopulateField extends DynamicMigration {

  /**
   * Indicate we are updating existing data.
   */
  protected $systemOfRecord = Migration::DESTINATION;


  public function __construct(array $arguments) {
    $this->arguments = $arguments;
    parent::__construct();
    $bundle = $arguments['bundle'];
    $this->description = t('Upgrade group visibility (e.g. open, moderated, closed) from OG6.');

    $this->dependencies = array('OgUiMigrateAddField');

    $query = db_select('d6_og', 'og');
    $query->innerJoin('node', 'n', 'og.nid = n.nid');

    $query
      ->fields('og')
      ->condition('n.type', $bundle)
      ->orderBy('n.nid');

    $this->query = $query;

    $source_key = MigrateDestinationNode::getKeySchema();
    // Set the alias, so the query in MigrateSourceSQL::performRewind()
    // will not fail.
    $source_key['nid']['alias'] = 'n';

    $this->map = new MigrateSQLMap($this->machineName, $source_key, MigrateDestinationNode::getKeySchema());
    $this->source = new MigrateSourceSQL($this->query);
    $this->destination = new MigrateDestinationNode($bundle);

    $this->addFieldMapping('nid', 'nid');
    $this->addFieldMapping(OG_DEFAULT_ACCESS_FIELD, NULL)->defaultValue(TRUE);
  }

  /**
   * Construct the machine name from the bundle.
   */
  protected function generateMachineName($class_name = NULL) {
    return drupal_strtolower('OgUiPopulateField' . ucfirst($this->arguments['bundle']));
  }
}
