<?php

namespace Dkan\Datastore;

class CsvParser {

  const STATE_NEW_FIELD = 0;
  const STATE_CAPTURE = 1;
  const STATE_NO_CAPTURE = 2;
  const STATE_ESCAPE = 3;
  const STATE_QUOTE_INITIAL = 4;
  const STATE_QUOTE_FINAL = 5;
  const STATE_QUOTE_CAPTURE = 6;
  const STATE_QUOTE_NO_CAPTURE = 7;
  const STATE_QUOTE_ESCAPE = 8;

  const CHAR_TYPE_DELIMITER = 9;
  const CHAR_TYPE_QUOTE = 10;
  const CHAR_TYPE_ESCAPE = 11;
  const CHAR_TYPE_RECORD_END = 12;
  const CHAR_TYPE_BLANK = 13;
  const CHAR_TYPE_OTHER = 14;

  private $delimiter;
  private $quote;
  private $escape;
  private $recordEnd;

  private $records;
  private $fields;
  private $field;

  private $currentState;

  private $stateMachine = [];

  public function __construct($delimiter, $quote, $escape, array $record_end) {
    $this->recordEnd = $record_end;
    $this->delimiter = $delimiter;
    $this->quote = $quote;
    $this->escape = $escape;

    $this->stateMachine[self::STATE_NEW_FIELD][self::CHAR_TYPE_DELIMITER][self::STATE_NEW_FIELD] = TRUE;
    $this->stateMachine[self::STATE_NEW_FIELD][self::CHAR_TYPE_QUOTE][self::STATE_QUOTE_INITIAL] = TRUE;
    $this->stateMachine[self::STATE_NEW_FIELD][self::CHAR_TYPE_ESCAPE][self::STATE_ESCAPE] = TRUE;
    $this->stateMachine[self::STATE_NEW_FIELD][self::CHAR_TYPE_BLANK][self::STATE_NO_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_NEW_FIELD][self::CHAR_TYPE_OTHER][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_NEW_FIELD][self::CHAR_TYPE_RECORD_END][self::STATE_NO_CAPTURE] = TRUE;

    $this->stateMachine[self::STATE_CAPTURE][self::CHAR_TYPE_DELIMITER][self::STATE_NEW_FIELD] = TRUE;
    $this->stateMachine[self::STATE_CAPTURE][self::CHAR_TYPE_ESCAPE][self::STATE_ESCAPE] = TRUE;
    $this->stateMachine[self::STATE_CAPTURE][self::CHAR_TYPE_BLANK][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_CAPTURE][self::CHAR_TYPE_OTHER][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_CAPTURE][self::CHAR_TYPE_RECORD_END][self::STATE_NEW_FIELD] = TRUE;


    $this->stateMachine[self::STATE_NO_CAPTURE][self::CHAR_TYPE_DELIMITER][self::STATE_NEW_FIELD] = TRUE;
    $this->stateMachine[self::STATE_NO_CAPTURE][self::CHAR_TYPE_QUOTE][self::STATE_QUOTE_INITIAL] = TRUE;
    $this->stateMachine[self::STATE_NO_CAPTURE][self::CHAR_TYPE_ESCAPE][self::STATE_ESCAPE] = TRUE;
    $this->stateMachine[self::STATE_NO_CAPTURE][self::CHAR_TYPE_BLANK][self::STATE_NO_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_NO_CAPTURE][self::CHAR_TYPE_OTHER][self::STATE_CAPTURE] = TRUE;

    $this->stateMachine[self::STATE_ESCAPE][self::CHAR_TYPE_DELIMITER][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_ESCAPE][self::CHAR_TYPE_QUOTE][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_ESCAPE][self::CHAR_TYPE_ESCAPE][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_ESCAPE][self::CHAR_TYPE_BLANK][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_ESCAPE][self::CHAR_TYPE_OTHER][self::STATE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_ESCAPE][self::CHAR_TYPE_RECORD_END][self::STATE_CAPTURE] = TRUE;

    $this->stateMachine[self::STATE_QUOTE_INITIAL][self::CHAR_TYPE_DELIMITER][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_INITIAL][self::CHAR_TYPE_QUOTE][self::STATE_QUOTE_FINAL] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_INITIAL][self::CHAR_TYPE_ESCAPE][self::STATE_QUOTE_ESCAPE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_INITIAL][self::CHAR_TYPE_BLANK][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_INITIAL][self::CHAR_TYPE_OTHER][self::STATE_QUOTE_CAPTURE] = TRUE;

    $this->stateMachine[self::STATE_QUOTE_CAPTURE][self::CHAR_TYPE_DELIMITER][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_CAPTURE][self::CHAR_TYPE_QUOTE][self::STATE_QUOTE_FINAL] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_CAPTURE][self::CHAR_TYPE_ESCAPE][self::STATE_QUOTE_ESCAPE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_CAPTURE][self::CHAR_TYPE_BLANK][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_CAPTURE][self::CHAR_TYPE_OTHER][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_CAPTURE][self::CHAR_TYPE_RECORD_END][self::STATE_QUOTE_CAPTURE] = TRUE;


    $this->stateMachine[self::STATE_QUOTE_ESCAPE][self::CHAR_TYPE_DELIMITER][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_ESCAPE][self::CHAR_TYPE_QUOTE][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_ESCAPE][self::CHAR_TYPE_ESCAPE][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_ESCAPE][self::CHAR_TYPE_BLANK][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_ESCAPE][self::CHAR_TYPE_OTHER][self::STATE_QUOTE_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_ESCAPE][self::CHAR_TYPE_RECORD_END][self::STATE_QUOTE_CAPTURE] = TRUE;


    $this->stateMachine[self::STATE_QUOTE_FINAL][self::CHAR_TYPE_DELIMITER][self::STATE_NEW_FIELD] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_FINAL][self::CHAR_TYPE_BLANK][self::STATE_QUOTE_NO_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_FINAL][self::CHAR_TYPE_RECORD_END][self::STATE_NEW_FIELD] = TRUE;


    $this->stateMachine[self::STATE_QUOTE_NO_CAPTURE][self::CHAR_TYPE_DELIMITER][self::STATE_NEW_FIELD] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_NO_CAPTURE][self::CHAR_TYPE_BLANK][self::STATE_QUOTE_NO_CAPTURE] = TRUE;
    $this->stateMachine[self::STATE_QUOTE_NO_CAPTURE][self::CHAR_TYPE_RECORD_END][self::STATE_NEW_FIELD] = TRUE;


    $this->reset();
  }

  private function goToState($state, $char) {
    switch ($state) {
      case self::STATE_NEW_FIELD:
        $type = $this->getCharType($char);

        if ($this->currentState == self::STATE_NO_CAPTURE ||
        $this->currentState == self::STATE_CAPTURE) {
          $this->removeFieldBlankCharsAtEndOfLine();
        }

        if($type == self::CHAR_TYPE_RECORD_END && (
            $this->currentState == self::STATE_QUOTE_NO_CAPTURE ||
            $this->currentState == self::STATE_QUOTE_FINAL ||
            $this->currentState == self::STATE_CAPTURE
          )) {

          $this->createNewRecord();
        }else {
          $this->createNewField();
        }
        break;
      case self::STATE_CAPTURE:
        $this->addCharToField($char);
        break;
      case self::STATE_NO_CAPTURE:
        break;
      case self::STATE_ESCAPE:
        break;
      case self::STATE_QUOTE_INITIAL:
        break;
      case self::STATE_QUOTE_FINAL:
        break;
      case self::STATE_QUOTE_CAPTURE:
        $this->addCharToField($char);
        break;
      case self::STATE_QUOTE_ESCAPE:
        break;
      case self::STATE_QUOTE_NO_CAPTURE:
        break;
    }
    $this->currentState = $state;
  }

  private function removeFieldBlankCharsAtEndOfLine() {
    $this->field = rtrim($this->field);
  }

  private function addCharToField($char) {
    $this->field .= $char;
  }

  private function createNewRecord() {
    $this->createNewField();
    $this->records[] = $this->fields;
    $this->fields = [];
  }

  private function createNewField() {
    $this->fields[] = $this->field;
    $this->field = "";
  }

  private function stateMachineInput($char) {
    $char_type = $this->getCharType($char);
    $next_state = $this->getNextState($char_type);
    $this->goToState($next_state, $char);
  }

  private function getCharType($char) {
    if(in_array($char, $this->recordEnd)) {
      return self::CHAR_TYPE_RECORD_END;
    }
    if ($char == $this->delimiter) {
      return self::CHAR_TYPE_DELIMITER;
    }
    if ($char == $this->quote) {
      return self::CHAR_TYPE_QUOTE;
    }
    if ($char == $this->escape) {
      return self::CHAR_TYPE_ESCAPE;
    }
    if (ctype_space($char)) {
      return self::CHAR_TYPE_BLANK;
    }
    return self::CHAR_TYPE_OTHER;
  }

  private function getNextState($char_type) {
    if (isset($this->stateMachine[$this->currentState][$char_type])) {
      $keys = array_keys($this->stateMachine[$this->currentState][$char_type]);
      return $keys[0];
    }
    throw new \Exception("Error parsing the current chunk. [{$this->currentState}->{$char_type}]");
  }

  public function feed($chunk) {
    $chars = str_split($chunk);
    foreach ($chars as $char) {
      $this->stateMachineInput($char);
    }
  }

  public function getRecord() {
    return array_shift($this->records);
  }

  public function reset() {
    $this->field = "";
    $this->fields = [];
    $this->records = [];
    $this->currentState = self::STATE_NEW_FIELD;
  }

  public function finish() {
    if (!empty($this->field)) {
      $this->createNewRecord();
    }
  }

}