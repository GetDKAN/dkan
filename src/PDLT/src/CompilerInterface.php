<?php

namespace PDLT;

/**
 * AST compiler.
 */
interface CompilerInterface {

  /**
   * Compile the given AST.
   *
   * @param array $syntax
   *   Abstract Syntax Tree.
   *
   * @return string
   *   Compiled string.
   */
  public function compile(array $syntax): string;

}
