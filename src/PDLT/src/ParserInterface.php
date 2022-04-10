<?php

namespace PDLT;

/**
 * Language string parser.
 */
interface ParserInterface {

  /**
   * Generate an Abstract Syntax Tree (AST) from the given input.
   *
   * @param string $input
   *   Input string.
   *
   * @return array
   *   Generated AST.
   */
  public function parse(string $input): array;

}
