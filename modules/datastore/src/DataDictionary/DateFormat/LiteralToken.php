<?php

namespace Drupal\datastore\DataDictionary\DateFormat;

use Drupal\datastore\DataDictionary\TokenInterface;

/**
 * Lexical token used to represent a string literal.
 */
class LiteralToken implements TokenInterface {

  /**
   * String literal.
   *
   * @var string
   */
  protected string $literal;

  /**
   * Build a lexical token.
   *
   * @param string $literal
   *   String literal.
   */
  public function __construct(string $literal) {
    $this->literal = $literal;
  }

  /**
   * {@inheritdoc}
   */
  public function getLiteral(): string {
    return $this->literal;
  }

}
