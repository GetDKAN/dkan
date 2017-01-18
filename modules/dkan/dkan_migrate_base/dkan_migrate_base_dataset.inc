<?php

/**
 * @file
 * Migration Class for Datasets.
 */

/**
 * Migrate CKAN dataset.
 */
class MigrateCkanDatasetBase extends MigrateCkanBase {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {
    parent::__construct($arguments);
    $fields = $this->getCkanDatasetFields();

    $list_url = isset($arguments['list_url']) ? $arguments['list_url'] : 'package_list';
    $list_url = $this->endpoint . $list_url;
    $item_url = isset($arguments['item_url']) ? $arguments['item_url'] : 'package_show?id=:id';
    $item_url = $this->endpoint . $item_url;
    $this->page = isset($arguments['page']) ? $arguments['page'] : '';
    $this->offset = isset($arguments['offset']) ? $arguments['offset'] : '';
    $this->ids = isset($arguments['ids']) ? $arguments['ids'] : '';

    $this->highwaterField = array(
      'name' => 'revision_timestamp',
    );

    $this->source = new MigrateSourceList(new CKANListJSON(
      $list_url,
      array(
        'page' => $this->page,
        'offset' => $this->offset,
        'ids' => $this->ids,
      )
    ),
    new CKANItemJSON($item_url, $fields), $fields);

    $this->map = new MigrateSQLMap(
            $this->machineName,
            array(
              'uuid' => array(
                'type' => 'varchar',
                'length' => 255,
                'not null' => TRUE,
                'description' => 'id',
              ),
            ),
            MigrateDestinationNode::getKeySchema()
        );

    $this->destination = new MigrateDestinationNode('dataset', array('text_format' => 'html'));

    $this->addDefaultDatasetMappings();
  }

  /**
   * Implements prepareRow.
   */
  public function prepareRow($row) {
    $this->datasetPrepareRow($row);
  }

  /**
   * Implements prepare.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    $this->datasetPrepare($node, $row);
  }

  /**
   * Implements complete.
   */
  public function complete($node, $row) {
    if (isset($row->group_ids)) {
      $node->og_group_ref = array(
        LANGUAGE_NONE => array(),
      );
      foreach ($row->group_ids as $group) {
        $node->og_group_ref[LANGUAGE_NONE][] = array('target_id' => $group);
      }
    }
    node_save($node);
  }

}
