<?php

namespace Dkan\Datastore\Parser;

/**
 * Class CsvBase.
 *
 * Things common to all CSV-related parsers.
 */
abstract class CsvBase extends Base {

  protected $delimiter;
  protected $quote;
  protected $escape;
  protected $recordEnd;

  /**
   * Constructor.
   *
   * @param string $delimiter
   *   Delimiter.
   * @param string $quote
   *   Quote.
   * @param string $escape
   *   Escape.
   * @param array $record_end
   *   Record end.
   */
  public function __construct($delimiter, $quote, $escape, array $record_end) {
    $this->recordEnd = $record_end;
    $this->delimiter = $delimiter;
    $this->quote = $quote;
    $this->escape = $escape;

    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function getStateMachineInput($char) {
    if (in_array($char, $this->recordEnd)) {
      return "END";
    }
    if ($char == $this->delimiter) {
      return "DELIMITER";
    }
    if ($char == $this->quote) {
      return "QUOTE";
    }
    if ($char == $this->escape) {
      return "ESCAPE";
    }
    if (ctype_space($char)) {
      return "BLANK";
    }
    return "OTHER";
  }

}
