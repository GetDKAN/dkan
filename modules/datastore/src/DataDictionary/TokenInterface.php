<?php

namespace Drupal\datastore\DataDictionary;

/**
 * Lexical token.
 */
interface TokenInterface {

  /**
   * Retrieve the literal value stored for this token.
   *
   * @return string
   *   Literal value.
   */
  public function getLiteral(): string;

}
