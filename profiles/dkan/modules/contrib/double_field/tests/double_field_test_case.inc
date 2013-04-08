<?php

/**
 * @file
 * Tests for Double Field module.
 *
 * Some field settings are initializated by random values.
 * So it is recommended to run these tests several times.
 * @code
 * for i in {1..10};
 * do
 *   echo "Test #$i"
 *   php scripts/run-tests.sh --url http://example.com/ --color 'Double field';
 * done
 * @endcode
 */

/**
 * Tests for double field types.
 */
class DoubleFieldTestCase extends DrupalWebTestCase {

  /**
   * Double field test group.
   */
  const GROUP = 'Double field';

  /**
   * Prepare environment.
   */
  public function setUp() {

    $modules = func_get_args();
    if (isset($modules[0]) && is_array($modules[0])) {
      $modules = $modules[0];
    }
    parent::setUp($modules ? $modules : 'double_field');

    // Create content type.
    $this->type_name = strtolower($this->randomName(8));
    $this->drupalCreateContentType(array('name' => $this->type_name, 'type' => $this->type_name));

    // Create test field.
    $this->field_name = strtolower($this->randomName(8));

    $field = array(
      'field_name' => $this->field_name,
      'type' => 'double_field',
      'cardinality' => 1,
    );

    $field_type = array_rand(DoubleFieldField::getAllFieldTypes());
    $field['settings'] = DoubleFieldField::getFieldSettings($field_type);

    $this->field = field_create_field($field);

    debug($field['settings'], 'Field settings', TRUE);

    $instance = array(
      'field_name' => $this->field_name,
      'entity_type' => 'node',
      'label' => 'Test double field',
      'bundle' => $this->type_name,
      'widget' => array(
        'module' => 'double_field',
        'type' => 'textfield_&_textfield',
      ),
    );
    $widget = new DoubleFieldWidget($instance['widget']['type'], $this->field['settings']);
    $instance['widget']['settings'] = $widget->getSettings();
    field_create_instance($instance);

    // Field create instance doesn't return all field properties.
    $this->instance = field_read_instance($instance['entity_type'], $instance['field_name'], $instance['bundle']);
    debug($this->instance['widget']['settings'], 'Widget settings', TRUE);

    $this->field_settings_page = "admin/structure/types/manage/{$this->type_name}/fields/{$this->field_name}";

    $this->user = $this->drupalCreateUser(
      array(
        'access administration pages',
        'administer content types',
        'administer nodes',
        'access content overview',
        'administer site configuration',
        'create ' . $this->type_name . ' content',
        'edit any ' . $this->type_name . ' content',
      )
    );
    $this->drupalLogin($this->user);

    // Create a node with actual data for the field.
    $edit = array(
      'type' => $this->type_name,
      'uid' => $this->user->uid,
    );
    for ($delta = 0; $delta < $this->field['cardinality']; $delta++) {
      $edit[$this->field_name][LANGUAGE_NONE][] = $widget->getValue($delta);
    }
    $this->node = $this->drupalCreateNode($edit);

    $this->value = &$this->node->{$this->field_name}[LANGUAGE_NONE][0];

    $this->widgets = DoubleFieldWidget::getAllWidgetTypes();
  }

  /**
   * Create subfield name.
   */
  protected function getSubfieldName($index, $delta = 0) {
    return $this->field_name . '[' . LANGUAGE_NONE . '][' . $delta . '][' . $index . ']';
  }

  /**
   * Run validators.
   */
  protected function validate($validators, $success_message) {
    foreach ($validators as $xpath) {
      if (!$this->xpath($xpath)) {
        debug($xpath, 'xpath', TRUE);
        $this->fail($success_message);
        return;
      }
    }
    $this->pass($success_message);
  }

  /**
   * Generate random number.
   */
  public static function randomNumber($length = 8) {
    $str = '';
    for ($i = 0; $i < $length; $i++) {
      $str .= chr(mt_rand(48, 57));
    }
    return $str;
  }

}
