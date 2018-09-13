<?php

namespace Dkan\Datastore\Parser;

use Dkan\Datastore\StateMachine;

/**
 * Class Csv.
 *
 * A https://tools.ietf.org/html/rfc4180 compliant parser, that can use
 * arbitrary characters as its delimiter, quote, escape, or end of record
 * characters and chan parse a streamed CSV file as it only requires chunks
 * of a file/string to parse correctly.
 *
 * The parser can also handle trailing delimiter on records.
 *
 * Technical Details
 * =================
 * The CSV parser delegates most of its complexity to specialized parsers:
 * Field and QuotedField.
 *
 * The state machine driving this parser is very simple.
 * It contains a single state (Neutral) and handles 3 types of input:
 * blank characters, delimiters and end-of-record characters.
 *
 * The state machine does nothing with blank characters (they are ignored).
 * When a delimiter appears, a new field starts being tracked. When an end
 * of record character appears, all for the currently tracked fields are turned
 * into a record.
 *
 * If an input that is not one of the 3 handled here appears, the appropriate
 * field parser is engaged until it finds something it can not handle
 * (like a delimiter for example).
 *
 * If neither the state machine not a field parser can handle an input, an
 * error/exception is thrown with details on the failure.
 */
class Csv extends CsvBase {

  private $records;
  private $fields;
  private $field;

  private $fieldParser;

  private $trailingDelimiter = FALSE;

  /**
   * Activate the handling of trailing delimiters in a record.
   */
  public function activateTrailingDelimiter() {
    $this->trailingDelimiter = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function feed($chunk) {
    $this->chunck = $chunk;
    $chars = str_split($chunk);

    for ($i = 0; $i < count($chars); $i++) {
      $char = $chars[$i];

      // Ignore consecutive endline chars.
      if (!(in_array($this->currentChar, $this->recordEnd) && in_array($char, $this->recordEnd))) {
        $this->stateMachineInput($char);
      }
    }
  }

  /**
   * Get a record.
   */
  public function getRecord() {
    return array_shift($this->records);
  }

  /**
   * Informs the parser that we are done.
   */
  public function finish() {
    if (!in_array($this->currentChar, $this->recordEnd)) {
      $this->feed($this->recordEnd[0]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    parent::reset();
    $this->field = "";
    $this->fields = [];
    $this->records = [];
    $this->fieldParser = NULL;
  }

  /**
   * Create a new record from the current state.
   */
  public function createNewRecord() {
    $this->createNewField();

    // A CSV with trailing delimiter characters in each record will create an
    // empty field at the end of each record. Here we check to see if that last
    // field is empty when we are in "trailingDelimiter" mode and remove that
    // last field from the record. The reason we need to check for emptiness is
    // that it is possible for a record to not have a trailing delimiter and we
    // do not want to remove a valid field.
    if ($this->trailingDelimiter && empty($this->fields[count($this->fields) - 1])) {
      array_pop($this->fields);
    }

    $this->records[] = $this->fields;
    $this->fields = [];
  }

  /**
   * Create a new field from the current state.
   */
  public function createNewField() {
    $this->fields[] = $this->field;
    $this->field = "";
  }

  /**
   * {@inheritdoc}
   */
  protected function setupStateMachine() {
    $states = [
      "NEUTRAL",
    ];

    $inputs = [
      "BLANK",
      "DELIMITER",
      "END",
    ];

    $this->stateMachine = new StateMachine($states, $inputs);
    $this->stateMachine->addInitialState("NEUTRAL");

    $this->stateMachine->addTransition(
      "NEUTRAL",
      "BLANK",
      "NEUTRAL",
      TRUE);

    $this->stateMachine->addTransition(
      "NEUTRAL",
      "DELIMITER",
      "NEUTRAL",
      [$this, "createNewField"]);

    $this->stateMachine->addTransition(
      "NEUTRAL",
      "END",
      "NEUTRAL",
      [$this, "createNewRecord"]);
  }

  /**
   * {@inheritdoc}
   */
  protected function feedStateMachine($input) {

    // No field parser is active, we will handle things.
    if (!$this->fieldParser) {
      try {
        $this->stateMachine->processInput($input);
      }
      catch (\Exception $e) {
        // We couldn't handle it, lets try an appropriate field parser.
        if ($input == "QUOTE") {
          $this->fieldParser = new QuotedField($this->delimiter, $this->quote, $this->escape, $this->recordEnd);
        }
        else {
          $this->fieldParser = new Field($this->delimiter, $this->quote, $this->escape, $this->recordEnd);
        }
      }
    }

    // A field parser is active, let them do their thing.
    if ($this->fieldParser) {
      try {
        $this->fieldParser->feed($this->currentChar);
      }
      // The field parser could not handle it.
      catch (\Exception $e) {
        // Lets get their work,.
        $this->field = $this->fieldParser->getField();
        $this->fieldParser = NULL;

        // And lets try ourselves again.
        try {
          $this->stateMachine->processInput($input);
        }
        // If we can not handle it, we have a parsing issue.
        catch (\Exception $e) {
          $debug_info = [];
          $debug_info['Message'] = $e->getMessage();
          $debug_info['Chunk'] = $this->chunck;
          $debug_info['Records'] = $this->records;
          $debug_info['Fields'] = $this->fields;
          $debug_info['Field'] = $this->field;

          $json = json_encode($debug_info, JSON_PRETTY_PRINT);

          throw new \Exception("Error parsing CSV: {$json}");
        }
      }
    }
  }

}
