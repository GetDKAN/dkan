<?php

namespace PDLT;

/**
 * Converts between date formats.
 *
 * The date format parser given is used to parse the input format supplied to
 * `::convert()`, and the date format compiler is then used to generate the
 * converted output format.
 *
 * The internal language used by ASTs which all parsers are supposed to convert
 * input format's to is the Frictionless date format.
 */
class Converter implements ConverterInterface {

  /**
   * Build date format converter using the given parser and compiler.
   */
  public function __construct(ParserInterface $parser, CompilerInterface $compiler) {
    $this->parser = $parser;
    $this->compiler = $compiler;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(string $input_format): string {
    // Generate a syntax tree for the supplied date format string.
    $syntax = $this->parser->parse($input_format);
    // Compile the generate syntax tree into an output date format string.
    $output_format = $this->compiler->compile($syntax);

    return $output_format;
  }

}
