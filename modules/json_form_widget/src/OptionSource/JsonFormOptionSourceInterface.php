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
   * Returns the target entity type for the option source for use by autocreate.
   *
   * @param array $config
   *   Arbitrary configuration for the option source.
   *
   * @return null|string
   *   The target type, such as 'node', 'taxonomy_term', etc.
   */
  public function getTargetType(array $config): string;

  /**
   * Validates the configuration for the option source.
   *
   * @todo This should probably provide a framework for validating with JSON
   * schema, rather than just arbirary PHP logic.
   *
   * @param array $config
   *   Arbitrary configuration for the option source.
   *
   * @return bool
   *   If the configuration is valid, returns true. Invalid configuration will
   *   throw an exception. Should never return false; bool to support PHP 8.1.
   */
  public function validateConfig(array $config): bool;

}
