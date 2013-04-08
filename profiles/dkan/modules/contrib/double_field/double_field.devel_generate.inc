<?php
/**
 * @file
 * Functions needed for devel_generate integration.
 */

/**
 * Implements hook_devel_generate().
 */
function double_field_devel_generate($object, $field, $instance, $bundle) {

  list($widget['first'], $widget['second']) = explode('_&_', $instance['widget']['type']);

  foreach (array('first', 'second') as $subfield) {

    switch ($widget[$subfield]) {
      case 'checkbox':
        $object_field[$subfield] = $instance['widget']['settings'][$subfield]['checkbox'][mt_rand(0, 1) ? 'on_value' : 'off_value'];
        break;

      case 'select':
        if ($allowed_values = array_keys($instance['widget']['settings'][$subfield]['select']['allowed_values'])) {
          $object_field[$subfield] = $allowed_values[mt_rand(0, count($allowed_values) - 1)];
        }
        break;

      default:
        // Other field types are not supported yet.
        if ($field['settings'][$subfield]['type'] == 'varchar') {
          $maxlength = $field['settings'][$subfield]['maxlength'];
          if ($maxlength > 64) {
            // Average length of a word in devel_create_greeking()
            // is about 7 charaters.
            $word_count = (int) ($maxlength / 7);
            $object_field[$subfield] = rtrim(substr(devel_create_greeking($word_count), 0, $maxlength - 1), ' .') . '.';
          }
          else {
            $object_field[$subfield] = user_password($field['settings'][$subfield]['maxlength']);
          }

        }
        else {
          $object_field[$subfield] = '123';
        }
    }

  }

  return $object_field;
}
