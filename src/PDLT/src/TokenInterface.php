<?php

namespace PDLT;

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
