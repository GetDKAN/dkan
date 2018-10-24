<?php

namespace Dkan\Datastore\Parser;

/**
 * Class Base.
 *
 * The base for a parser. In this case a parser takes string chunks, and
 * runs them through a state machine that represent the parsing rules.
 */
abstract class Base {

  protected $chunck;
  protected $currentChar;

  /**
   * State machine.
   *
   * @var \Dkan\Datastore\StateMachine
   */
  protected $stateMachine;

  /**
   * Setup the state machine used by this parser.
   */
  abstract protected function setupStateMachine();

  /**
   * Translate characters into state machine inputs.
   */
  abstract protected function getStateMachineInput($char);

  /**
   * Process inputs with the state machine.
   */
  abstract protected function feedStateMachine($input);

  /**
   * Constructor.
   */
  public function __construct() {
    $this->reset();
  }

  /**
   * Feeds the parser a chunck of string to be parsed.
   *
   * @param string $chunk
   *   Part of what we are parsing.
   */
  public function feed($chunk) {
    $this->chunck = $chunk;
    $chars = str_split($chunk);

    for ($i = 0; $i < count($chars); $i++) {
      $char = $chars[$i];
      $this->stateMachineInput($char);
    }
  }

  /**
   * It sets the parser's state to its initial state.
   */
  public function reset() {
    $this->setupStateMachine();
  }

  /**
   * Get a proper state machine input and feed it to state machine.
   */
  protected function stateMachineInput($char) {
    $this->currentChar = $char;
    $input = $this->getStateMachineInput($char);
    $this->feedStateMachine($input);
  }

}
