<?php

namespace Dkan\Datastore\Parser;

use Dkan\Datastore\StateMachine;

/**
 * Parses a single unquoted CSV field.
 *
 * Technical Details
 * =================
 * The state machine driving this parser has 2 states: CAPTURE and ESCAPE.
 * The CAPTURE state can handle non-special characters (anything that is
 * not a delimiter, end-of-record, etc), blank characters, and the escape
 * character. Anything else causes the state machine to throw an exception.
 *
 * Effectively, the CAPTURE state lets us know which characters we should be
 * adding to our field.
 *
 * If an escape character is found, the state machine transitions to the
 * ESCAPE state. This state can handle ANY input, and will add any character
 * to the field as it transitions to the CAPTURE state.
 */
class Field extends CsvBase {
  private $field;

  /**
   * Field getter.
   */
  public function getField() {
    return trim($this->field, " ");
  }

  /**
   * Add the current char to the field.
   */
  public function addCharToField() {
    $this->field .= $this->currentChar;
  }

  /**
   * {@inheritdoc}
   */
  protected function setupStateMachine() {
    $states = [
      "CAPTURE",
      "ESCAPE",
    ];

    $inputs = [
      "ESCAPE",
      "BLANK",
      "OTHER",
      "END",
      "DELIMITER",
    ];

    $this->stateMachine = new StateMachine($states, $inputs);
    $this->stateMachine->addInitialState("CAPTURE");

    $this->stateMachine->addTransition(
      "CAPTURE",
      "BLANK",
      "CAPTURE",
      [$this, "addCharToField"]);

    $this->stateMachine->addTransition(
      "CAPTURE",
      "OTHER",
      "CAPTURE",
      [$this, "addCharToField"]);

    $this->stateMachine->addTransition(
      "CAPTURE",
      "ESCAPE",
      "ESCAPE",
      TRUE);

    $this->stateMachine->addTransition(
      "ESCAPE",
      ["END", "DELIMITER", "ESCAPE", "BLANK", "OTHER"],
      "CAPTURE",
      [$this, "addCharToField"]);
  }

  /**
   * {@inheritdoc}
   */
  protected function feedStateMachine($input) {
    $this->stateMachine->processInput($input);
  }

}
