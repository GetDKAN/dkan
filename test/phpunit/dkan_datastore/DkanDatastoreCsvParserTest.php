<?php

class DkanDatastoreCsvParserTest extends \PHPUnit_Framework_TestCase {

  public function testCommaDelimiter() {
    $parser = new \Dkan\Datastore\CsvParser(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("date,pr\\\nice,state_id\r\n\"12\n34\",0.59,OK\n\r");
    $parser->finish();

    $this->assertEquals(TRUE, TRUE);
  }
}