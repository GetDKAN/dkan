<?php

namespace Drupal\common;

/**
 * MIME type (IANA media type) data object.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types
 */
class MimeType {

  /**
   * Full mime-type RegEx.
   *
   * @var string
   */
  protected const MIME_TYPE_REGEX = '%^(?P<type>\w+)/(?P<subtype>[\w+]+)(?:;\s*(?P<parameters>.*))?$%';

  /**
   * Mime-type parameters RegEx.
   *
   * @var string
   */
  protected const MIME_PARAMS_REGEX = '%^\s*(?P<name>.+?)\s*=\s*(?P<value>.+?)\s*$%';

  /**
   * Base mime-type (category).
   *
   * @var string
   */
  protected $type;

  /**
   * Sub mime-type (kind).
   *
   * @var string
   */
  protected $subtype;

  /**
   * Mime-type parameters.
   *
   * @var string[]
   */
  protected $parameters;

  /**
   * Create a mime-type object.
   *
   * @param string $type
   *   Base mime-type.
   * @param string $subtype
   *   Sub mime-type.
   * @param array $parameters
   *   An optional list of mime-type parameters.
   */
  protected function __construct(string $type, string $subtype, array $parameters = []) {
    $this->type = $type;
    $this->subtype = $subtype;
    $this->parameters = $parameters;
  }

  /**
   * Build mime-type object from string.
   *
   * @param string
   *   Full mime-type string.
   *
   * @return \Drupal\common\MimeType
   *   Mime-type object.
   */
  public static function fromString(string $mime_type): self {
    $mime_parts = NULL;
    if (!preg_match(self::MIME_TYPE_REGEX, $mime_type, $mime_parts)) {
      throw new \UnexpectedValueException("Invalid data-dictionary mime-type '{$mime_type}'.");
    }
    $mime_params = array_merge([], ...array_map(function ($parameter) {
      if (!preg_match(self::MIME_PARAMS_REGEX, $parameter, $param_parts)) {
        throw new \UnexpectedValueException("Invalid data-dictionary mime-type parameter '{$parameter}'.");
      }
      return [$param_parts['name'] => $param_parts['value']];
    }, explode(';', $mime_parts['parameters'])));

    return new self($mime_parts['type'], $mime_parts['subtype'], $mime_params);
  }

  /**
   * Get mime-type string excluding parameters.
   *
   * @return string
   *   Mime-type string.
   */
  public function getType(): string {
    return $this->type . '/' . $this->subtype;
  }

  /**
   * Get mime-type string excluding parameters.
   *
   * @param string
   *   Mime-type parameter name.
   * @param string
   *   Mime-type parameter value.
   *
   * @return bool
   *   Whether the given mime-type parameter was found.
   */
  public function hasParameter(string $param, string $value): bool {
    return ($this->parameters[$param] ?? NULL) === $value;
  }

}
