<?php

namespace PDLT;

/**
 * Date format compiler.
 */
class Compiler implements CompilerInterface {

  /**
   * Simple single dimensional map of token literals to output token strings.
   *
   * @var \PDLT\CompilationMapInterface
   */
  protected CompilationMapInterface $compilationMap;

  /**
   * Builds a date format compiler.
   *
   * @param \PDLT\CompilationMapInterface $compilation_map
   *   Date format compilation map.
   */
  public function __construct(CompilationMapInterface $compilation_map) {
    $this->compilationMap = $compilation_map;
  }

  /**
   * Convert the supplied token to it's string equivalent using compilation map.
   *
   * @param \PDLT\TokenInterface $token
   *   Lexical token.
   *
   * @return string
   *   Compiled token string.
   *
   * @throws \PDLT\UnsupportedTokenException
   *   When a token of a type not supported by this compiler is encountered.
   */
  protected function compileToken(TokenInterface $token): string {
    if ($token instanceof DirectiveToken) {
      return $this->compileDirective($token);
    }
    elseif ($token instanceof LiteralToken) {
      return $this->compileLiteral($token);
    }
    else {
      throw new UnsupportedTokenException(sprintf('Unable to compile unsupported token type "%s" with literal value "%s".', get_class($token), $token->getLiteral()));
    }
  }

  /**
   * Compile a directive token to it's string equivalent.
   *
   * @param \PDLT\DirectiveToken $token
   *   Directive token.
   *
   * @return string
   *   Compiled token string.
   *
   * @throws \PDLT\UnsupportedTokenException
   *   When the supplied token is not supported by the this compiler's
   *   compilation map.
   */
  protected function compileDirective(DirectiveToken $token): string {
    if ($result = $this->compilationMap[$token->getLiteral()] ?? NULL) {
      return $result;
    }
    else {
      throw new UnsupportedTokenException(sprintf('Unable to compile unsupported directive "%s"; not found in compilation map "%s".', $token->getLiteral(), get_class($this->compilationMap)));
    }
  }

  /**
   * Compile a literal token to a string.
   *
   * @param \PDLT\LiteralToken $token
   *   Literal token.
   *
   * @return string
   *   Compiled token string.
   */
  protected function compileLiteral(LiteralToken $token): string {
    return $token->getLiteral();
  }

  /**
   * {@inheritdoc}
   */
  public function compile(array $syntax): string {
    $output_format = '';

    foreach ($syntax as $token) {
      $output_format .= $this->compileToken($token);
    }

    return $output_format;
  }

}
