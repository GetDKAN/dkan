<?php
/**
 * @file
 * Contains class definition for Data module test cases.
 */

/**
 * Base class for Data module test cases.
 */
class DataTestCase extends DrupalWebTestCase {

  /**
   * Return a test schema.
   */
  protected function testSchema() {
    return array(
      'fields' => array(
        'id' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ),
        'char0' => array(
          'type' => 'varchar',
          'length' => '255',
          'not null' => FALSE,
          'default' => '',
        ),
        'char1' => array(
          'type' => 'varchar',
          'length' => '255',
          'not null' => FALSE,
          'default' => '',
        ),
      ),
      'indexes' => array(
        'id' => array('id'),
      ),
    );
  }

  /**
   * Test data.
   */
  protected function testData() {
    return array(
      0 => array(
        'id' => 0,
        'char0' => 'test00',
        'char1' => 'test01',
      ),
      1 => array(
        'id' => 1,
        'char0' => 'test10',
        'char1' => 'test11',
      ),
    );
  }
}
