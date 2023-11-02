<?php

namespace Drupal\dkan\CsvParser\Parser;

use Contracts\ParserInterface;
use Maquina\StateMachine\MachineOfMachines;

class Csv implements ParserInterface, \JsonSerializable {
  private $delimiter;
  private $quote;
  private $escape;
  private array $recordEnd;

  private $records;
  private $fields;
  private string $field;

  /**
   * @var \Drupal\dkan\CsvParser\Parser\StateMachine
   */
  public $machine;

  private $lastCharType;
  private bool $quoted = FALSE;

  private bool $trailingDelimiter = FALSE;

  public function activateTrailingDelimiter(): void {
    $this->trailingDelimiter = TRUE;
  }

  public static function getParser($delimiter = ',', $quote = '"', $escape = '\\', $record_end = ["\n", "\r"]): self {
    return new self($delimiter, $quote, $escape, $record_end);
  }

  public function __construct($delimiter, $quote, $escape, array $record_end) {
    $this->recordEnd = $record_end;
    $this->delimiter = $delimiter;
    $this->quote = $quote;
    $this->escape = $escape;
    $this->reset();
    $this->machine = new StateMachine();
    $this->machine->stopRecording();
  }

  public function feed(string $chunk) {
    if (strlen($chunk) > 0) {
      $chars = str_split($chunk);
      foreach ($chars as $char) {
        $this->machine->getCurrentStates();
        $char_type = $this->getCharType($char);

        $this->machine->processInput($char_type);

        $this->lastCharType = $char_type;
        $end_states = $this->machine->getCurrentStates();

        $this->processTransition($end_states, $char);
      }
    }
    else {
      throw new \Exception('The CSV parser can not parse empty chunks.');
    }
  }

  public function getRecord() {
    $record = array_shift($this->records);

    if (isset($record)) {
      $last_field = array_pop($record);
      if ($this->trailingDelimiter && empty($last_field)) {
        return $record;
      }
      else {
        array_push($record, $last_field);
      }
    }

    return $record;
  }

  public function getRecords() : array {
    $records = [];
    while ($record = $this->getRecord()) {
      $records[] = $record;
    }
    return $records;
  }

  public function reset(): void {
    $this->field = '';
    $this->fields = [];
    $this->records = [];

    $this->machine = new StateMachine();
    $this->lastCharType = NULL;
    $this->quoted = FALSE;
  }

  public function finish() {
    // There will be csv strings that do not end in a "end of record" char.
    // This will flush them.
    if ($this->lastCharType != StateMachine::CHAR_TYPE_RECORD_END) {
      $this->feed($this->recordEnd[0]);
    }

    // We just flushed the machine. This should never happen.
    if (!$this->machine->isCurrentlyAtAnEndState()) {
      throw new \Exception('Machine did not halt');
    }
  }

  private function getCharType(string $char) {
    $type = StateMachine::CHAR_TYPE_OTHER;
    if (in_array($char, $this->recordEnd)) {
      $type = StateMachine::CHAR_TYPE_RECORD_END;
    }
    elseif ($char == $this->delimiter) {
      $type = StateMachine::CHAR_TYPE_DELIMITER;
    }
    elseif ($char == $this->quote) {
      $type = StateMachine::CHAR_TYPE_QUOTE;
    }
    elseif ($char == $this->escape) {
      $type = StateMachine::CHAR_TYPE_ESCAPE;
    }
    elseif (ctype_space($char)) {
      $type = StateMachine::CHAR_TYPE_BLANK;
    }
    return $type;
  }

  private function addCharToField(string $char): void {
    $this->field .= $char;
  }

  private function createNewRecord(): void {
    $this->createNewField();
    if (!empty($this->fields)) {
      $this->records[] = $this->fields;
      $this->fields = [];
    }
  }

  private function createNewField(): void {
    if ($this->quoted) {
      $this->fields[] = $this->field;
      $this->quoted = FALSE;
    }
    else {
      $this->fields[] = trim($this->field, ' ');
    }

    $this->field = '';
  }

  private function processTransition($endStates, string $input): void {
    foreach ($endStates as $endState) {
      $this->processTransitionHelper($endState, $input);
    }
  }

  private function processTransitionHelper($endState, string $input): void {
    if ($endState == StateMachine::STATE_RECORD_END) {
      $this->createNewRecord();
    }
    elseif ($endState == StateMachine::STATE_NEW_FIELD) {
      $this->createNewField();
    }
    elseif ($endState == StateMachine::STATE_CAPTURE || $endState == StateMachine::STATE_QUOTE_CAPTURE) {
      $this->addCharToField($input);
    }
    elseif ($endState == StateMachine::STATE_QUOTE_INITIAL) {
      $this->quoted = TRUE;
    }
  }

  #[\ReturnTypeWillChange]
    public function jsonSerialize() {
    return (object) [
      'delimiter' => $this->delimiter,
      'quote' => $this->quote,
      'escape' => $this->escape,
      'recordEnd' => $this->recordEnd,
      'records' => $this->records,
      'fields' => $this->fields,
      'field' => $this->field,
      'machine' => $this->machine,
    ];
    }

    /**
     * @todo Replace this with a new hydrateable trait from upstream (contracts?)
     */
    public static function hydrate($json) {
      $data = json_decode($json);

      $reflector = new \ReflectionClass(self::class);
      $object = $reflector->newInstanceWithoutConstructor();

      $reflector = new \ReflectionClass($object);

      foreach ($data as $property => $value) {
        $p = $reflector->getProperty($property);
        $p->setAccessible(TRUE);
        $p->setValue($object, $value);
      }
      // The machine property needs to be hydrated, so make a second pass.
      $p = $reflector->getProperty('machine');
      $p->setAccessible(TRUE);
      $machine = new StateMachine();
      $machine->stopRecording();
      $machine = MachineOfMachines::hydrate(json_encode($data->machine), $machine);

      $p->setValue($object, $machine);

      return $object;
    }

}
