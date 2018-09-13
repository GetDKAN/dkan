<?php

namespace Dkan\Datastore\Parser;

use Dkan\Datastore\StateMachine;

/**
 * Class QuotedField.
 *
 * Parse CSV quoted strings.
 *
 * Technical Details
 * =================
 * Quoted strings in fields are straight forward: The field is anything that is
 * in between 2 string-quoting-characters (SQC). Any complexity in this parser
 * is caused by the handling of using 2 SQCs as a means to
 * escape a SQC. So the CSV string """" will yield a single
 * double quote in a our field (") assuming that a double quote is our
 * SQC.
 *
 * This behavior creates some ambiguity for our state machine. We can not know
 * what a SQC should be interpreted as (the end of the string or
 * an escape character) until we see the next character in the string.
 *
 * Our state machine implementation solves this by allowing certain inputs to
 * be declared as ambiguous, and allowing the class using the state machine (us)
 * to have a disambiguation character handler.
 *
 * The details of the state machine are simple beyond the previously mentioned
 * complexity.
 *
 * We have 2 special states: INITIAL and END. The INITIAL state only handles
 * SQCs. Anything else will produce an exception.
 *
 * From the initial state we transition to the CAPTURE state. Once we see
 * another SQC we transition to the END state (except when escaping).
 * After entering the END state we do not handle any more inputs. This is a
 * final state in our state machine.
 *
 * While in the CAPTURE state we can handle any characters including escape
 * characters. Escape characters are handled in the same way here as in the
 * Field parser. This is why we have an ESCAPE state.
 */
class QuotedField extends CsvBase {

  private $field;

  /**
   * Field getter.
   */
  public function getField() {
    return $this->field;
  }

  /**
   * Add the current char to the field.
   */
  public function addCharToField($input) {
    // If we get a mismatch between input and the current char,
    // it is likely that our current char is out of sync due
    // to double quote escape handling. In that case, lets try
    // to translate our input into the proper character.
    if ($this->getStateMachineInput($this->currentChar) != $input) {
      $this->field .= $this->inputToChar($input);
    }
    else {
      $this->field .= $this->currentChar;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setupStateMachine() {
    $states = [
      "INITIAL",
      "CAPTURE",
      "ESCAPE",
      "END",
    ];

    $inputs = [
      "QUOTE",
      "ESCAPE",
      "BLANK",
      "OTHER",
      "END",
      "DELIMITER",
    ];

    $this->stateMachine = new StateMachine($states, $inputs);

    $this->stateMachine->setAmbiguousInputHandler([$this, "disambiguateInput"]);
    $this->stateMachine->addAmbiguousInput("QUOTE");
    $this->stateMachine->addInitialState("INITIAL");
    $this->stateMachine->addEndState("END");

    $this->stateMachine->addTransition(
      "INITIAL",
      "QUOTE",
      "CAPTURE",
      TRUE);

    $this->stateMachine->addTransition(
      "CAPTURE",
      ["BLANK", "OTHER", "END", "DELIMITER"],
      "CAPTURE",
      [$this, "addCharToField"]);

    $this->stateMachine->addTransition(
      "CAPTURE",
      "ESCAPE",
      "ESCAPE",
      TRUE);

    $this->stateMachine->addTransition(
      "ESCAPE",
      ["QUOTE", "END", "DELIMITER", "ESCAPE", "BLANK", "OTHER"],
      "CAPTURE",
      [$this, "addCharToField"]);

    $this->stateMachine->addTransition(
      "CAPTURE",
      "QUOTE",
      "END",
      TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function feedStateMachine($input) {
    $this->stateMachine->processInput($input);
  }

  /**
   * Diambiguation handler for the state machine.
   *
   * Once we are capturing characters for the field,
   * when we get a string-quoting-character, we do not know
   * whether it should be the end of the quoted string or if
   * it is being used as a means to escape a string-quoting-character
   * inside the string.
   *
   * We disambiguate by letting the state machine know that any
   * string-quoting-character follow by another should be treated
   * as an escape character.
   */
  public function disambiguateInput($inputs, $current_state) {
    if ($current_state == "CAPTURE") {
      if ($inputs[0] == "QUOTE" && $inputs[1] == "QUOTE") {
        return "ESCAPE";
      }
      else {
        return "QUOTE";
      }
    }
    else {
      return "QUOTE";
    }
  }

  /**
   * Translate a state machine input into a character.
   */
  private function inputToChar($input) {
    if ($input == "QUOTE") {
      return $this->quote;
    }
    else {
      return "?";
    }
  }

}
