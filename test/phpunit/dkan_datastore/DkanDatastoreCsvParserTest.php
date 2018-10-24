<?php

class DkanDatastoreCsvParserTest extends \PHPUnit_Framework_TestCase {

  public function testEmptyString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
  }

  public function testJustDelimiters() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(",,");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
    $this->assertEquals("", $record[1]);
    $this->assertEquals("", $record[2]);
  }

  public function testEmptyNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("\n");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
  }

  public function testJustDelimitersNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(",,\n,,");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
    $this->assertEquals("", $record[1]);
    $this->assertEquals("", $record[2]);

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
    $this->assertEquals("", $record[1]);
    $this->assertEquals("", $record[2]);
  }

  public function testBlankString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("   ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
  }

  public function testBlankJustDelimiters() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("  ,   ,    ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
    $this->assertEquals("", $record[1]);
    $this->assertEquals("", $record[2]);
  }

  public function testBlankNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("  \n   ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
  }

  public function testBlankJustDelimitersNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("  ,   ,    \n  ,   ,    ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
    $this->assertEquals("", $record[1]);
    $this->assertEquals("", $record[2]);

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
    $this->assertEquals("", $record[1]);
    $this->assertEquals("", $record[2]);
  }

  public function testOther() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("A");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A", $record[0]);
  }

  public function testOtherJustDelimiters() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("A,B,C");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A", $record[0]);
    $this->assertEquals("B", $record[1]);
    $this->assertEquals("C", $record[2]);
  }

  public function testOtherNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("A\nB");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A", $record[0]);

    $record = $parser->getRecord();
    $this->assertEquals("B", $record[0]);
  }

  public function testOtherJustDelimitersNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("A,B,C\nD,E,F");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A", $record[0]);
    $this->assertEquals("B", $record[1]);
    $this->assertEquals("C", $record[2]);

    $record = $parser->getRecord();
    $this->assertEquals("D", $record[0]);
    $this->assertEquals("E", $record[1]);
    $this->assertEquals("F", $record[2]);
  }

  public function testOtherBlank() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("  A B  ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A B", $record[0]);
  }

  public function testOtherBlankJustDelimiters() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(" A B ,B  C , CD");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A B", $record[0]);
    $this->assertEquals("B  C", $record[1]);
    $this->assertEquals("CD", $record[2]);
  }

  public function testOtherBlankNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("A B   \n   B  C");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A B", $record[0]);

    $record = $parser->getRecord();
    $this->assertEquals("B  C", $record[0]);
  }

  public function testOtherBlankJustDelimitersNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(" AB,B C \n D  E,E   F ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("AB", $record[0]);
    $this->assertEquals("B C", $record[1]);

    $record = $parser->getRecord();
    $this->assertEquals("D  E", $record[0]);
    $this->assertEquals("E   F", $record[1]);
  }

  public function testOtherBlankEscape() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("  A \\\nB\\,  ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A \nB,", $record[0]);
  }

  public function testOtherBlankEscapeJustDelimiters() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(" A \\\\B ,B \\\n C , CD");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A \\B", $record[0]);
    $this->assertEquals("B \n C", $record[1]);
    $this->assertEquals("CD", $record[2]);
  }

  public function testOtherBlankEscapeNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("A B \\\n  \n \\,  B  C ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A B \n", $record[0]);

    $record = $parser->getRecord();
    $this->assertEquals(",  B  C", $record[0]);
  }

  public function testOtherBlankEscapeJustDelimitersNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(" A \\\n B,B C \n \\\\D  E\\,,E   F ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A \n B", $record[0]);
    $this->assertEquals("B C", $record[1]);

    $record = $parser->getRecord();
    $this->assertEquals("\\D  E,", $record[0]);
    $this->assertEquals("E   F", $record[1]);
  }

  public function testQuotes() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("\"\"");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("", $record[0]);
  }

  public function testQuoteDelimiters() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(" \" A B \" , \" B \n C\"");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals(" A B ", $record[0]);
    $this->assertEquals(" B \n C", $record[1]);
  }

  public function testQuoteNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed("  \" A B \n\"  \n \", B \\\" C \"");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals(" A B \n", $record[0]);

    $record = $parser->getRecord();
    $this->assertEquals(", B \" C ", $record[0]);
  }

  public function testQuoteJustDelimitersNewLineString() {
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed(" \"A \n B\"  , \"B C\" \n \"\\\\D  E,\" , \"E   F\" ");
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("A \n B", $record[0]);
    $this->assertEquals("B C", $record[1]);

    $record = $parser->getRecord();
    $this->assertEquals("\\D  E,", $record[0]);
    $this->assertEquals("E   F", $record[1]);
  }

  public function testDoubleQuoteEscaping() {
    $string = "\"S \"\"H\"\"\"";
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed($string);
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("S \"H\"", $record[0]);

    $string = "\"S \"\"H\"\" S\"";
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed($string);
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("S \"H\" S", $record[0]);

    $string = "\"\"\"H\"\" S\"";
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed($string);
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("\"H\" S", $record[0]);
  }

  public function testBrokenLookAhead() {
    $string1 = "\"S \"";
    $string2 = "\"H\"\"\"";
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed($string1);
    $parser->feed($string2);
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("S \"H\"", $record[0]);
  }

  public function testTrailingDelimiter() {
    $string = "H,F,\nG,B,";
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed($string);
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("H", $record[0]);
    $this->assertEquals("F", $record[1]);
    $this->assertEquals("", $record[2]);

    $record = $parser->getRecord();
    $this->assertEquals("G", $record[0]);
    $this->assertEquals("B", $record[1]);
    $this->assertEquals("", $record[2]);

    $string = "H,F \nG,B,";
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->activateTrailingDelimiter();
    $parser->feed($string);
    $parser->finish();

    $record = $parser->getRecord();
    $this->assertEquals("H", $record[0]);
    $this->assertEquals("F", $record[1]);
    $this->assertArrayNotHasKey(2, $record);

    $record = $parser->getRecord();
    $this->assertEquals("G", $record[0]);
    $this->assertEquals("B", $record[1]);
    $this->assertArrayNotHasKey(2, $record);
  }
}