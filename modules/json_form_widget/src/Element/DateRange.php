<?php

namespace Drupal\json_form_widget\Element;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides a date_range element.
 *
 * @FormElement("date_range")
 * @codeCoverageIgnore
 */
class DateRange extends Datetime {

  /**
   * Get info.
   */
  public function getInfo() {
    $class = get_class($this);
    $date_format = '';
    $time_format = '';
    if (!defined('MAINTENANCE_MODE')) {
      if ($date_format_entity = DateFormat::load('html_date')) {
        $date_format = $date_format_entity->getPattern();
      }
      if ($time_format_entity = DateFormat::load('html_time')) {
        $time_format = $time_format_entity->getPattern();
      }
    }
    return $this->getElementInfo($class, $date_format, $time_format);
  }

  /**
   * Get element info array.
   */
  private function getElementInfo($class, $date_format, $time_format) {
    return [
      '#input' => TRUE,
      '#element_validate' => [
        [$class, 'validateRange'],
      ],
      '#process' => [
        [$class, 'processDateRange'],
      ],
      '#theme' => 'datetime_form',
      '#theme_wrappers' => ['datetime_wrapper'],
      '#date_year_range' => '1900:2050',
      '#date_increment' => 1,
      '#date_date_callbacks' => [],
      '#date_timezone' => date_default_timezone_get(),
      '#date_date_element' => 'date',
      '#date_date_format' => $date_format,
      '#date_time_element' => 'time',
      '#date_time_format' => $time_format,
    ];
  }

  /**
   * Callback for getting the value of the date range element.
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $element += ['#date_timezone' => date_default_timezone_get()];
    if ($input !== FALSE) {
      $start = static::getDateValue('start_date', $element, $input);
      $end = static::getDateValue('end_date', $element, $input);
      $range = (empty($start) && empty($end)) ? '' : $start . '/' . $end;
      $input = [
        'start' => $start,
        'end' => $end,
        'date_range' => $range,
      ];
    }
    return $input;
  }

  /**
   * Get date value for date sub element.
   *
   * @param string $date_field
   *   The name of the date field (for example: start_date or end_date).
   * @param array $element
   *   The element.
   * @param array $input
   *   Input to take the value from.
   *
   * @return string
   *   Date element formatted as string.
   */
  public static function getDateValue($date_field, array $element, array $input) {
    $input['date_range'] = '';
    $date_format = $element['#date_date_element'] != 'none' ? static::getHtml5DateFormat($element) : '';
    $time_format = $element['#date_time_element'] != 'none' ? static::getHtml5TimeFormat($element) : '';
    $date_time_format = trim($date_format . ' ' . $time_format);

    if (!empty($input[$date_field]['date'])) {
      $time = static::getFormattedTime($input[$date_field]['time']);
      return static::getDateTimeElement($input[$date_field]['date'], $time, $date_time_format, $element['#date_timezone']);
    }
    return '';
  }

  /**
   * Get the date time element formatted.
   *
   * @param string $date
   *   The date.
   * @param string $time
   *   The time formatted.
   * @param string $format
   *   The format in which the date should be formatted.
   * @param mixed $timezone
   *   The timezone of the date.
   *
   * @return string
   *   The date formatted.
   */
  public static function getDateTimeElement($date, $time, $format, $timezone) {
    $input = $date . ' ' . $time;
    $date_time = DrupalDateTime::createFromFormat($format, $input, $timezone);
    return $date_time->format('c', ['timezone' => 'UTC']);
  }

  /**
   * Get time formatted as H:i:S.
   *
   * @param mixed $time
   *   Time to format.
   *
   * @return mixed
   *   Formatted time.
   */
  public static function getFormattedTime($time) {
    $formatted = !empty($time) ? $time : '00:00:00';
    if (strlen($formatted) == 5) {
      $formatted = $formatted . ':00';
    }
    return $formatted;
  }

  /**
   * Callback for processing the date_range element.
   */
  public static function processDateRange(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['start_date'] = [
      '#type' => 'flexible_datetime',
      '#title' => 'Start date',
    ];
    $element['end_date'] = [
      '#type' => 'flexible_datetime',
      '#title' => 'End date',
    ];

    // Add default value for start and end dates.
    $default = !empty($element['#default_value']) ? $element['#default_value'] : '';
    $matches = [];
    if (preg_match('/(.*)\/(.*)/', $default, $matches)) {
      $start_date = new DrupalDateTime($matches[1], date_default_timezone_get());
      $element['start_date']['#default_value'] = $start_date;
      $end_date = new DrupalDateTime($matches[2], date_default_timezone_get());
      $element['end_date']['#default_value'] = $end_date;
    }
    return $element;
  }

  /**
   * Validation callback for a date_range element.
   *
   * @param array $element
   *   The form element whose value is being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public static function validateRange(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $input_exists = FALSE;
    $input = NestedArray::getValue($form_state->getValues(), $element['#parents'], $input_exists);
    if ($input_exists) {
      if ($input['start_date'] instanceof DrupalDateTime && $input['end_date'] instanceof DrupalDateTime) {
        static::validateInterval($input['start_date'], $input['end_date'], $element, $form_state);
        return;
      }
      static::missingEndDate($input, $element, $form_state);
      static::missingStartDate($input, $element, $form_state);
    }
  }

  /**
   * Set errors when end date is missing.
   *
   * @param array $input
   *   Array with keys for start_date and end_date.
   * @param array $element
   *   The element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function missingEndDate(array $input, array $element, FormStateInterface $form_state) {
    if (!empty($input['start_date']) && empty($input['end_date'])) {
      $form_state->setError($element['end_date'], t('Please enter an end date.'));
    }
  }

  /**
   * Set errors when start date is missing.
   *
   * @param array $input
   *   Array with keys for start_date and end_date.
   * @param array $element
   *   The element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function missingStartDate(array $input, array $element, FormStateInterface $form_state) {
    if (empty($input['start_date']) && !empty($input['end_date'])) {
      $form_state->setError($element['start_date'], t('Please enter a start date.'));
    }
  }

  /**
   * Set error when start date is greater than end date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start
   *   Start date object.
   * @param \Drupal\Core\Datetime\DrupalDateTime $end
   *   End date object.
   * @param array $element
   *   The element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateInterval(DrupalDateTime $start, DrupalDateTime $end, array $element, FormStateInterface $form_state) {
    if ($start->diff($end)->invert === 1) {
      $form_state->setError($element, t('The end date should be greater than the start date.'));
    }
  }

}
