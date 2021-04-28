<?php

namespace Drupal\json_form_widget\Element;

use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a flexible_datetime element.
 *
 * @FormElement("flexible_datetime")
 * @codeCoverageIgnore
 */
class FlexibleDateTime extends Datetime {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $date_format = '';
    $time_format = '';
    // Date formats cannot be loaded during install or update.
    if (!defined('MAINTENANCE_MODE')) {
      if ($date_format_entity = DateFormat::load('html_date')) {
        /** @var $date_format_entity \Drupal\Core\Datetime\DateFormatInterface */
        $date_format = $date_format_entity->getPattern();
      }
      if ($time_format_entity = DateFormat::load('html_time')) {
        /** @var $time_format_entity \Drupal\Core\Datetime\DateFormatInterface */
        $time_format = $time_format_entity->getPattern();
      }
    }

    $class = get_class($this);

    // Note that since this information is cached, the #date_timezone property
    // is not set here, as this needs to vary potentially by-user.
    return [
      '#input' => TRUE,
      '#element_validate' => [
        [$class, 'validateDatetime'],
      ],
      '#process' => [
        [$class, 'processDatetime'],
        [$class, 'processAjaxForm'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
      '#theme' => 'datetime_form',
      '#theme_wrappers' => ['datetime_wrapper'],
      '#date_date_format' => $date_format,
      '#date_date_element' => 'date',
      '#date_date_callbacks' => [],
      '#date_time_format' => $time_format,
      '#date_time_element' => 'time',
      '#date_time_callbacks' => [],
      '#date_year_range' => '1900:2050',
      '#date_increment' => 1,
      '#date_time_required' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (empty($element['#default_value']) && !empty($input['date'])) {
      $input['time'] = !empty($input['time']) ? $input['time'] : '00:00:00';
    }
    return parent::valueCallback($element, $input, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public static function processDatetime(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processDatetime($element, $form_state, $complete_form);
    $element['time']['#required'] = $element['#date_time_required'];
    return $element;
  }

}
