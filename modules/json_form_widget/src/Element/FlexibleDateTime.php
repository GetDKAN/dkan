<?php

namespace Drupal\json_form_widget\Element;

use Drupal\Core\Datetime\Element\Datetime;
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
    $info = parent::getInfo();
    $info['#date_time_required'] = FALSE;
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (!empty($input['date']) && empty($input['time'])) {
      $input['time'] = '00:00:00';
    }
    if (
      ($input['time'] ?? NULL === '00:00:00')
      && $element['#date_date_element'] !== 'none'
      && empty($input['date'])
    ) {
      unset($input['time']);
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
