<?php

/**
 * @file
 * The file contains a class that assists to test Double field widget types.
 */

/**
 * Helper class to test Double field widget types.
 */
class DoubleFieldWidget {

  const MAXCARDINALITY = 10;

  protected $settings;
  protected $subwidgets;
  protected $value;
  protected $fieldSettings;

  /**
   * Constructor for DoubleFieldWidget.
   */
  public function __construct($widget_type, $field_settings) {

    $this->fieldSettings = $field_settings;

    // The order of the elements compared in
    // which order they were written in the list().
    list($this->subwidgets['second'], $this->subwidgets['first']) = array_reverse(explode('_&_', $widget_type));

    // Generate widget settings.
    $this->settings['inline'] = (bool) mt_rand(0, 1);
    foreach ($this->subwidgets as $index => $subwidget_type) {
      $this->settings[$index]['general'] = array(
        'required' => (bool) mt_rand(0, 1),
        'prefix' => '(' . DrupalTestCase::randomName(mt_rand(0, 126)) . ')',
        'suffix' => '(' . DrupalTestCase::randomName(mt_rand(0, 126)) . ')',
      );

      // Subwidget related settings.
      switch ($subwidget_type) {

        case 'textfield':
          $this->settings[$index][$subwidget_type]['size'] = mt_rand(1, 50);
          $this->settings[$index][$subwidget_type]['placeholder'] = DrupalTestCase::randomName(mt_rand(0, 50));
          break;

        case 'checkbox':
          // Get the value based on field settings.
          $this->settings[$index][$subwidget_type]['on_value'] = DoubleFieldField::generateValue($this->fieldSettings[$index]);
          $this->settings[$index][$subwidget_type]['off_value'] = '';
          break;

        case 'select':
          $this->settings[$index][$subwidget_type]  = array();
          for ($i = 0, $cnt = mt_rand(5, 10); $i < $cnt; $i++) {
            // These values should match global field settings.
            $value = DoubleFieldField::generateValue($this->fieldSettings[$index]);
            $this->settings[$index][$subwidget_type]['allowed_values'][$value] = ucfirst($value);
          }
          break;

        case 'textarea':
          $this->settings[$index][$subwidget_type]['cols'] = mt_rand(1, 25);
          $this->settings[$index][$subwidget_type]['rows'] = mt_rand(1, 25);
          $this->settings[$index][$subwidget_type]['resizable'] = (bool) mt_rand(0, 1);
          $this->settings[$index][$subwidget_type]['placeholder'] = DrupalTestCase::randomName(mt_rand(0, 50));
          break;

        default:
          throw new Exception('Undefined subwidget type: ' . $subwidget_type);

      }

      // Generate field value based on current field and widget settings.
      for ($delta = 0; $delta < self::MAXCARDINALITY; $delta++) {
        if ($subwidget_type == 'select') {
          // Take value for select list subwidget_type from allowed values.
          $this->value[$delta][$index] = array_rand($this->settings[$index][$subwidget_type]['allowed_values']);
        }
        elseif ($subwidget_type == 'checkbox') {
          $this->value[$delta][$index] = mt_rand(0, 1) ? '' : $this->settings[$index][$subwidget_type]['on_value'];
        }
        else {
          $this->value[$delta][$index] = DoubleFieldField::generateValue($this->fieldSettings[$index]);
        }
      }

    }

  }

  /**
   * Settings getter.
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Settings setter.
   */
  public function setSettings($settings) {
    $this->settings = $settings;
  }


  /**
   * Value getter.
   */
  public function getValue($delta = 0) {
    return $this->value[$delta];
  }

  /**
   * Get widget type.
   */
  public function getType() {
    return implode('_&_', $this->subwidgets);
  }

  /**
   * Create widget settings form validators.
   */
  public function getSettingsFormValidators() {

    $form_xpath = '//form[@id="field-ui-field-edit-form"]';

    $checked = $this->settings['inline'] ? '@checked' : 'not(@checked)';
    $validators[] = "$form_xpath//input[@name='instance[widget][settings][inline]' and $checked]";

    foreach ($this->subwidgets as $index => $subwidget) {
      $prefix = "$form_xpath//fieldset[@id='edit-instance-widget-settings-$index']";
      $text = ($index == 'first' ? t('First subfield') : t('Second subfield')) . ' (' . double_field_get_subwidgets($subwidget) . ')';
      $validators[] = "$prefix//legend//span[text()='$text']";

      // Check subwidget related elements.
      switch ($subwidget) {

        case 'textfield':
          $value = $this->settings[$index][$subwidget]['size'];
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][size]' and @value='$value']";
          $value = $this->settings[$index][$subwidget]['placeholder'];
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][placeholder]' and @value='$value']";
          break;

        case 'textarea':
          $value = $this->settings[$index][$subwidget]['cols'];
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][cols]' and @value='$value']";
          $value = $this->settings[$index][$subwidget]['rows'];
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][rows]' and @value='$value']";
          $checked = $this->settings[$index][$subwidget]['resizable'] ? '@checked' : 'not(@checked)';
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][resizable]' and $checked]";
          $value = $this->settings[$index][$subwidget]['placeholder'];
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][placeholder]' and @value='$value']";
          break;

        case 'select':
          $value = list_allowed_values_string($this->settings[$index][$subwidget]['allowed_values']);
          $validators[] = "$prefix//textarea[@name='instance[widget][settings][$index][$subwidget][allowed_values]' and text()='$value']";
          break;

        case 'checkbox':
          $value = $this->settings[$index][$subwidget]['on_value'];
          $validators[] = "$prefix//input[@name='instance[widget][settings][$index][$subwidget][on_value]' and @value='$value']";
          break;

      }

      // Walk down to general fieldset (should be collapsed).
      $prefix .= "//fieldset[@id='edit-instance-widget-settings-$index-general' and @class='collapsible collapsed form-wrapper']";

      $checked = $this->settings[$index]['general']['required'] ? '@checked' : 'not(@checked)';
      $validators[] = "$prefix//input[@name='instance[widget][settings][$index][general][required]' and $checked]";

      $value = $this->settings[$index]['general']['prefix'];
      $validators[] = "$prefix//input[@name='instance[widget][settings][$index][general][prefix]' and @value='$value']";

      $value = $this->settings[$index]['general']['suffix'];
      $validators[] = "$prefix//input[@name='instance[widget][settings][$index][general][suffix]' and @value='$value']";
    }

    return $validators;
  }

  /**
   * Create widget fornm validators.
   */
  public function getFormValidators($field_name) {
    $prefix = "//div[@id='edit-$field_name']";

    $class = $this->settings['inline'] ? 'double-field-elements container-inline form-wrapper' : 'double-field-elements form-wrapper';

    // Verify prefix and suffix.
    $validators[] = "$prefix//div[@class='$class' and starts-with(text(), {$this->settings['first']['general']['prefix']})]";
    $validators[] = "$prefix//div[@class='$class' and contains(text(), {$this->settings['first']['general']['suffix']})]";
    $validators[] = "$prefix//div[@class='$class' and contains(text(), {$this->settings['second']['general']['prefix']})]";
    // It seems that ends-with() is an XPath 2.0 function.
    $validators[] = "$prefix//div[@class='$class' and contains(text(), {$this->settings['second']['general']['suffix']})]";

    $prefix .= "//div[@class='$class']";

    foreach ($this->subwidgets as $index => $subwidget) {
      $name = $field_name . '[' . LANGUAGE_NONE . '][0][' .  $index . ']';

      $value = $this->value[0][$index];

      // Check subwidget related elements.
      switch ($subwidget) {
        case 'textfield':
          $size = $this->settings[$index][$subwidget]['size'];
          $placeholder_value = $this->settings[$index][$subwidget]['placeholder'];
          $placeholder = $placeholder_value ? "@placeholder='$placeholder_value'" : 'not(@checked)';
          $validators[] = "$prefix//input[@name='$name' and @type='text' and @size='$size' and @value='$value' and $placeholder]";
          break;

        case 'textarea':
          $cols = $this->settings[$index][$subwidget]['cols'];
          $rows = $this->settings[$index][$subwidget]['rows'];
          $wrapper_class = $this->settings[$index][$subwidget]['resizable'] ? 'form-textarea-wrapper resizable' : 'form-textarea-wrapper';
          $placeholder_value = $this->settings[$index][$subwidget]['placeholder'];
          $placeholder = $placeholder_value ? "@placeholder='$placeholder_value'" : 'not(@checked)';
          $validators[] = "$prefix//div[@class='$wrapper_class']//textarea[@name='$name' and @cols='$cols' and @rows='$rows' and text()='$value' and $placeholder]";
          break;

        case 'select':
          $validators[] = "$prefix//select[@name='$name']//option[@value='$value' and @selected='selected']";
          break;

        case 'checkbox':
          $checked = $value ? '@checked' : 'not(@checked)';
          $validators[] = "$prefix//input[@name='$name' and @type='checkbox' and '$checked']";
          break;
      }
    }

    return $validators;
  }

  /**
   * Return input array for post submission.
   */
  public function getSettingsFormInput() {
    return $this->settingsToInput($this->settings, 'instance[widget][settings]', TRUE);
  }

  /**
   * Convert settings to input array.
   */
  protected function settingsToInput($settings, $parents = '', $reset = FALSE) {
    static $result;
    if ($reset) {
      $result = array();
    }
    foreach ($settings as $key => $value) {
      if (is_array($value) && $key != 'allowed_values') {
        $this->settingsToInput($value, $parents . "[$key]");
      }
      elseif ($parents) {
        $result[$parents . "[$key]"] = $key == 'allowed_values' ? list_allowed_values_string($value) : $value;
      }
    }
    return $result;
  }

  /**
   * Create widget form input values.
   */
  public function getFormInput($field_name, $delta = 0) {
    return array(
      $field_name . '[' . LANGUAGE_NONE . '][' . $delta . '][first]' => $this->value[$delta]['first'],
      $field_name . '[' . LANGUAGE_NONE . '][' . $delta . '][second]' => $this->value[$delta]['second'],
    );
  }

  /**
   * Set 'required' widget settings.
   */
  public function setRequiredOptions($first, $second) {
    $this->settings['first']['general']['required'] = $first;
    $this->settings['second']['general']['required'] = $second;
  }

  /**
   * Get all suported widget types.
   */
  public static function getAllWidgetTypes() {
    $subwidgets = array(
      'textfield' => t('Text field'),
      'checkbox'  => t('Checkbox'),
      'select'    => t('Select list'),
      'textarea'  => t('Textarea'),
    );
    // Why we need labels here?
    foreach ($subwidgets as $first_subwidget => $first_subwidget_label) {
      foreach ($subwidgets as $second_subwidget => $second_subwidget_label) {
        $widgets[$first_subwidget . '_&_' . $second_subwidget] = array(
          'label' => $first_subwidget_label . ' & ' . $second_subwidget_label,
        );
      }
    }
    return $widgets;
  }
}
