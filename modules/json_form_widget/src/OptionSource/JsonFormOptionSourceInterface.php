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
   * @param object $source
   *   The source object to get options for.
   * @param string|null $titleProperty
   *   (optional) The property to use as the option title, if not the default.
   *
   * @return array
   *   An associative array of options, where the keys are the option values and
   *   the values are the option titles.
   */
  public function getOptions(object $source, ?string $titleProperty): array;

}
