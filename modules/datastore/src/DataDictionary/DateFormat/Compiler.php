<?php

namespace Drupal\datastore\DataDictionary\DateFormat;

use Drupal\datastore\DataDictionary\CompilationMapInterface;
use Drupal\datastore\DataDictionary\CompilerInterface;
use Drupal\datastore\DataDictionary\TokenInterface;
use Drupal\datastore\DataDictionary\UnsupportedTokenException;

/**
 * Date format compiler.
 */
class Compiler implements CompilerInterface {

  /**
   * Simple single dimensional map of token literals to output token strings.
   *
   * @var \Drupal\datastore\DataDictionary\CompilationMapInterface
   */
  protected CompilationMapInterface $compilationMap;

  /**
   * Builds a date format compiler.
   *
   * @param \Drupal\datastore\DataDictionary\CompilationMapInterface $compilation_map
   *   Date format compilation map.
   */
  public function __construct(CompilationMapInterface $compilation_map) {
    $this->compilationMap = $compilation_map;
  }

  /**
   * Convert the supplied token to it's string equivalent using compilation map.
   *
   * @param TokenInterface $token
   *   Lexical token.
   *
   * @return string
   *   Compiled token string.
   */
  protected function compileToken(TokenInterface $token): string {
    if ($token instanceof DirectiveToken) {
      if ($result = $this->compilationMap[$token->getLiteral()] ?? NULL) {
        return $result;
      }
      else {
        throw new UnsupportedTokenException(sprintf('Unable to compile unsupported directive "%s"; not found in compilation map "%s".', $token->getLiteral(), get_class($this->compilationMap)));
      }
    }
    elseif ($token instanceof LiteralToken) {
      return $token->getLiteral();
    }
    else {
      throw new UnsupportedTokenException(sprintf('Unable to compile unsupported token type "%s" with literal value "%s".', get_class($token), $token->getLiteral()));
    }
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
