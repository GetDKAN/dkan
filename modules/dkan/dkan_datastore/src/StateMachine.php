<?php

namespace Dkan\Datastore;

/**
 * Class StateMachine.
 *
 * Implemenation of a
 * [state machine](https://en.wikipedia.org/wiki/Finite-state_machine)
 */
class StateMachine {
  private $states = [];
  private $inputs = [];
  public $transitions = [];

  public $ambiguousInputHandler;
  public $ambiguousInputs = [];
  public $disambiguated = FALSE;
  public $buffer = [];

  private $initialState = NULL;
  private $endState = NULL;
  private $currentState = NULL;

  /**
   * Constructor.
   */
  public function __construct($states, $inputs) {
    $this->states = $states;
    $this->inputs = $inputs;
  }

  /**
   * Set an ambiguous input handler.
   */
  public function setAmbiguousInputHandler($callable) {
    $this->ambiguousInputHandler = $callable;
  }

  /**
   * Mark inputs as ambiguous in the machine.
   */
  public function addAmbiguousInput($input) {
    if (isset($this->ambiguousInputHandler)) {
      if (in_array($input, $this->inputs)) {
        $this->ambiguousInputs[] = $input;
      }
      else {
        throw new \Exception("Invalid input: {$input}");
      }
    }
    else {
      throw new \Exception("Declare and ambiguous input handler before adding ambiguous inputs");
    }
  }

  /**
   * Set the initial state.
   */
  public function addInitialState($state) {
    if ($this->stateIsValid($state)) {
      $this->initialState = $state;
    }
    else {
      throw new \Exception("Invalid initial state {$state}");
    }
  }

  /**
   * Set an end state.
   */
  public function addEndState($state) {
    if ($this->stateIsValid($state)) {
      $this->endState = $state;
    }
    else {
      throw new \Exception("Invalid end state {$state}");
    }
  }

  /**
   * Note transitions, and an action if relevant.
   */
  public function addTransition($current_state, $inputs, $next_state, $callable = TRUE) {

    if (!is_array($inputs)) {
      $inputs = [$inputs];
    }

    foreach ($inputs as $input) {
      if ($this->stateIsValid($current_state) && in_array($input, $this->inputs) &&
        $this->stateIsValid($next_state)) {
        $this->transitions[$current_state][$input][$next_state] = $callable;
      }
      else {
        throw new \Exception("Invalid transition: {$current_state}->{$input}->{$next_state}");
      }
    }
  }

  /**
   * Give the state machine an input for it to work.
   */
  public function processInput($input) {

    $this->setCurrentStateIfNotSet();

    if ($this->processPreviouslyFoundAmbiguousInputs($input)) {
      return;
    }

    if ($this->checkAndCaptureAmbiguousInputs($input)) {
      return;
    }

    // Handle input after the end state.
    if ($this->currentState == $this->endState) {
      throw new \Exception("Already at end state {$this->endState}, no more processing can be done.");
    }

    $this->letTheStateMachineWork($input);
  }

  /**
   * Private.
   */
  private function setCurrentStateIfNotSet() {
    if (!isset($this->currentState)) {
      if (!isset($this->initialState)) {
        throw new \Exception("An initial state must be provided to start processing");
      }
      $this->currentState = $this->initialState;
    }
  }

  /**
   * Private.
   */
  private function processPreviouslyFoundAmbiguousInputs($input) {
    if (!empty($this->buffer)) {
      $this->buffer[] = $input;

      $buffer = $this->buffer;
      $this->buffer = [];
      $final_input = call_user_func($this->ambiguousInputHandler, $buffer, $this->currentState);
      $buffer[0] = $final_input;

      $this->disambiguated = TRUE;
      $counter = 0;
      foreach ($buffer as $input) {
        $this->processInput($input);
        if ($counter == 0) {
          $this->disambiguated = FALSE;
        }
        $counter++;
      }

      return TRUE;
    }
    return FALSE;
  }

  /**
   * Private.
   */
  private function checkAndCaptureAmbiguousInputs($input) {
    if (in_array($input, $this->ambiguousInputs) && !$this->disambiguated) {
      $this->buffer[] = $input;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Private.
   */
  private function letTheStateMachineWork($input) {
    if ($this->transitionIsValid($input)) {
      $next_state = $this->getNextState($input);
      $this->executeTransitionCallable($input, $next_state);
      $this->currentState = $next_state;
    }
    else {
      throw new \Exception("Invalid Input {$input}");
    }
  }

  /**
   * Private.
   */
  private function transitionIsValid($input) {
    return isset($this->transitions[$this->currentState][$input]);
  }

  /**
   * Private.
   */
  private function getNextState($input) {
    $keys = array_keys($this->transitions[$this->currentState][$input]);
    return $keys[0];
  }

  /**
   * Private.
   */
  private function executeTransitionCallable($input, $next_state) {
    $callable = $this->transitions[$this->currentState][$input][$next_state];
    if (!is_bool($callable)) {
      if (!call_user_func($callable, $input) == FALSE) {
        $json = json_encode($callable);
        throw new \Exception("Unable to call callable {$json}");
      }
    }
  }

  /**
   * Check state validity.
   */
  private function stateIsValid($state) {
    return in_array($state, $this->states);
  }

}
