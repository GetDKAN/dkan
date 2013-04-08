<?php

/**
 * @file
 * The file contains a class that assists to test Double Field field settings.
 */

/**
 * Helper class to test Double Field field settings.
 */
class DoubleFieldField {

  protected $settings;
  protected $sufields;

  /**
   * Constructor for DoubleFieldInctance.
   */
  public function __construct($field_type, $settings = FALSE) {
    // The order of the elements compared in
    // which order they were written in the list().
    list($this->subfields['second'], $this->subfields['first']) = array_reverse(explode('_&_', $field_type));

    $sizes = array('tiny', 'small', 'medium', 'normal', 'big');
    foreach ($this->subfields as $index => $subfield_type) {
      shuffle($sizes);
      $this->settings[$index] = array(
        'type' => $subfield_type,
        'maxlength' => mt_rand(1, 10000),
        'size' => $sizes[0],
        'precision' => mt_rand(10, 32),
        'scale' => mt_rand(0, 10),
      );
    }

  }

  /**
   * Generate field value.
   */
  public static function generateValue($subfield_settings) {

    switch ($subfield_settings['type']) {
      case 'varchar':
        $value = DrupalTestCase::randomName($subfield_settings['maxlength']);
        break;

      case 'text':
        // 128 is default maxlength in form API.
        $value = DrupalTestCase::randomName(128);
        break;

      case 'int':
        // These size values based on mysql limitation because pgsql
        // and sqlite have higher level of restrictions.
        // See http://dev.mysql.com/doc/refman/5.0/en/integer-types.html.
        $sizes = array(
          'tiny' => array(-128, 127),
          'small' => array(-32768, 32767),
          'medium' => array(-8388608, 8388607),
          'normal' => array(-mt_getrandmax(), -mt_getrandmax()),
          'big' => array(-mt_getrandmax(),  mt_getrandmax()),
        );
        $value = mt_rand($sizes[$subfield_settings['size']][0], $sizes[$subfield_settings['size']][1]);
        break;

      case 'float':
        // 2 Bytes?
        $value = mt_rand(-32768, 32767) / pow(10, mt_rand(0, 5));
        break;

      case 'decimal':
        $integer = DoubleFieldTestCase::randomNumber($subfield_settings['precision'] - $subfield_settings['scale']);
        $fracional = DoubleFieldTestCase::randomNumber($subfield_settings['scale']);
        $value = trim($integer . '.' . $fracional, '0');
        break;

    }

    return $value;
  }

  /**
   * Value getter.
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Create field settings form validators.
   */
  public function getSettingsFormValidators() {

    $form_xpath = '//form[@id="field-ui-field-edit-form"]';

    $validators[] = $form_xpath;

    foreach ($this->subfields as $index => $subfield) {
      $prefix = "$form_xpath//fieldset[@id='edit-field-settings-$index']";
      $text = $index == 'first' ? t('First subfield') : t('Second subfield');
      $validators[] = "$prefix//legend//span[text()='$text']";

      // Ensure that sufield type is selected.
      $validators[] = "$prefix//select[@name='field[settings][$index][type]']//option[@value='$subfield' and @selected='selected']";

      // Check subwidget related elements.
      switch ($subfield) {

        case 'varchar':
          $value = $this->settings[$index]['maxlength'];
          $validators[] = "$prefix//input[@name='field[settings][$index][maxlength]' and @value='$value']";
          break;

        case 'int':
        case 'float':
          $value = $this->settings[$index]['size'];
          $validators[] = "$prefix//select[@name='field[settings][$index][size]']//option[@value='$value' and @selected='selected']";
          break;

        case 'decimal':
          $value = $this->settings[$index]['precision'];
          $validators[] = "$prefix//select[@name='field[settings][$index][precision]']//option[@value='$value' and @selected='selected']";
          $value = $this->settings[$index]['scale'];
          $validators[] = "$prefix//select[@name='field[settings][$index][scale]']//option[@value='$value' and @selected='selected']";
          break;

      }

    }

    return $validators;
  }

  /**
   * Return input array for post submission.
   */
  public function getSettingsFormInput() {
    $input = array();
    foreach (array('first', 'second') as $subfield) {
      $input["field[settings][$subfield][type]"] = $this->settings[$subfield]['type'];

      switch ($this->settings[$subfield]['type']) {

        case 'varchar':
          $input["field[settings][$subfield][maxlength]"] = $this->settings[$subfield]['maxlength'];
          break;

        case 'int':
        case 'float':
          $input["field[settings][$subfield][size]"] = $this->settings[$subfield]['size'];
          break;

        case 'decimal':
          $input["field[settings][$subfield][precision]"] = $this->settings[$subfield]['precision'];
          $input["field[settings][$subfield][scale]"] = $this->settings[$subfield]['scale'];
          break;
      }
    }

    return $input;
  }

  /**
   * Get field settings.
   */
  public static function getFieldSettings($subfield_type) {
    list($subfields['first'], $subfields['second']) = explode('_&_', $subfield_type);
    foreach ($subfields as $subfield => $subfield_type) {
      $sizes = array('tiny', 'small', 'medium', 'normal', 'big');
      shuffle($sizes);
      $settings[$subfield] = array(
        'type' => $subfield_type,
        'maxlength' => mt_rand(1, 10000),
        'size' => $sizes[0],
        'precision' => mt_rand(10, 32),
        'scale' => mt_rand(0, 10),
      );
    }
    return $settings;
  }

  /**
   * Get all suported widget types.
   */
  public static function getAllFieldTypes() {
    $subfields = array(
      'varchar' => t('Varchar'),
      'text' => t('Text'),
      'int' => t('Integer'),
      'float'  => t('Float'),
      // Decimal values get rounded values and may differ
      // from original values.
      // See DoubleFieldField::getValue().
      // 'decimal' => t('Decimal'),
    );
    foreach ($subfields as $first_subfield => $first_subfield_label) {
      foreach ($subfields as $second_subfield => $second_subwfield_label) {
        $fields[$first_subfield . '_&_' . $second_subfield] = array(
          'label' => $first_subfield_label . ' & ' . $second_subwfield_label,
        );
      }
    }
    return $fields;
  }

}
