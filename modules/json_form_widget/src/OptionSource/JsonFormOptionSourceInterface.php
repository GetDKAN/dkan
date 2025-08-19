<?php

declare(strict_types=1);

namespace Drupal\json_form_widget\OptionSource;

/**
 * Interface for json_form_option_source plugins.
 */
interface JsonFormOptionSourceInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

  /**
   * Returns options for a given argument.
   *
   * @param array $config
   *   Arbitrary configuration for the option source.
   *
   * @return array
   *   An associative array of options, where the keys are the option values and
   *   the values are the option titles.
   */
  public function getOptions(array $config): array;

  /**
   * Validates the configuration for the option source.
   *
   * @param array $config
   *   Arbitrary configuration for the option source.
   *
   * @return true
   *   If the configuration is valid, returns true. Invalid configuration will
   *   throw an exception.
   */
  public function validateConfig(array $config): true;

}
