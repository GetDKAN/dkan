<?php

class DkanDatastoreCsvParserTest extends \PHPUnit_Framework_TestCase {

  public function testCommaDelimiter() {
    $parser = new \Dkan\Datastore\CsvParser("\t", "\"", "\\", ["\r", "\n"]);
    $parser->feed("Hello\t\"my\tname\"\tis\\\tCarlos\r");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("Hello", $record[0]);
    $this->assertEquals("my\tname", $record[1]);
    $this->assertEquals("is\tCarlos", $record[2]);
  }
}