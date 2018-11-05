<?php

class DkanDatastoreCsvParserTest extends \PHPUnit_Framework_TestCase {

  private function parse($string) {
    $parser = new \Dkan\Datastore\Parser\Csv(',', '"', "\\", ["\r", "\n"]);
    $parser->feed($string);
    $parser->finish();
    return $parser;
  }

  public function testEmptyString() {
    $parser = $this->parse('');

    $record = $parser->getRecord();
    $values = [''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testJustDelimiters() {
    $parser = $this->parse(',,');

    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testEmptyNewLineString() {
    $parser = $this->parse("\n");

    $record = $parser->getRecord();
    $values = [''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testJustDelimitersNewLineString() {
    $parser = $this->parse(",,\n,,");

    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testBlankString() {
    $parser = $this->parse('   ');

    $record = $parser->getRecord();
    $values = [''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testBlankJustDelimiters() {
    $parser = $this->parse('  ,   ,    ');

    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testBlankNewLineString() {
    $parser = $this->parse("  \n   ");

    $record = $parser->getRecord();
    $values = [''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = [''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testBlankJustDelimitersNewLineString() {
    $parser = $this->parse("  ,   ,    \n  ,   ,    ");

    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOther() {
    $parser = $this->parse('A');

    $record = $parser->getRecord();
    $values = ['A'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherJustDelimiters() {
    $parser = $this->parse('A,B,C');

    $record = $parser->getRecord();
    $values = ['A', 'B', 'C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherNewLineString() {
    $parser = $this->parse("A\nB");

    $record = $parser->getRecord();
    $values = ['A'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['B'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherJustDelimitersNewLineString() {
    $parser = $this->parse("A,B,C\nD,E,F");

    $record = $parser->getRecord();
    $values = ['A', 'B', 'C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['D', 'E', 'F'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlank() {
    $parser = $this->parse('  A B  ');

    $record = $parser->getRecord();
    $values = ['A B'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankJustDelimiters() {
    $parser = $this->parse(' A B ,B  C , CD');

    $record = $parser->getRecord();
    $values = ['A B', 'B  C', 'CD'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankNewLineString() {
    $parser = $this->parse("A B   \n   B  C");

    $record = $parser->getRecord();
    $values = ['A B'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['B  C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankJustDelimitersNewLineString() {
    $parser = $this->parse(" AB,B C \n D  E,E   F ");

    $record = $parser->getRecord();
    $values = ['AB', 'B C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['D  E', 'E   F'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscape() {
    $parser = $this->parse('  A \\' . "\n" . 'B\,  ');

    $record = $parser->getRecord();
    $values = ["A \nB,"];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscapeJustDelimiters() {
    $parser = $this->parse(' A \\\B ,B \\' . "\n" . ' C , CD');

    $record = $parser->getRecord();
    $values = ['A \\B', "B \n C", 'CD'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscapeNewLineString() {
    $parser = $this->parse('A B \\' . "\n  \n" . ' \\,  B  C ');

    $record = $parser->getRecord();
    $values = ["A B \n"];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = [',  B  C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscapeJustDelimitersNewLineString() {
    $parser = $this->parse(' A \\' . "\n" . ' B,B C ' . "\n" . ' \\\D  E\,,E   F ');

    $record = $parser->getRecord();
    $values = ["A \n B", 'B C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['\D  E,', 'E   F'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testQuotes() {
    $parser = $this->parse('""');

    $record = $parser->getRecord();
    $values = [''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testQuoteDelimiters() {
    $parser = $this->parse('" A B " , " B ' . "\n" . ' C"');

    $record = $parser->getRecord();
    $values = [' A B ', " B \n C"];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testQuoteNewLineString() {
    $parser = $this->parse('  " A B ' . "\n" . '"  ' . "\n" . ' ", B \" C "');

    $record = $parser->getRecord();
    $values = [" A B \n"];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = [', B " C '];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testQuoteJustDelimitersNewLineString() {
    $parser = $this->parse(' "A '. "\n" . ' B"  , "B C" ' . "\n" . ' "\\\D  E," , "E   F" ');

    $record = $parser->getRecord();
    $values = ["A \n B", 'B C'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['\D  E,', 'E   F'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testDoubleQuoteEscaping() {
    $parser = $this->parse('"S ""H"""');

    $record = $parser->getRecord();
    $values = ['S "H"'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());

    $parser = $this->parse('"S ""H"" S"');

    $record = $parser->getRecord();
    $values = ['S "H" S'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());

    $parser = $this->parse('"""H"" S"');

    $record = $parser->getRecord();
    $values = ['"H" S'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  /**
   * Double quote parsing requires looking ahead or back as we process a string.
   * We went to make sure that when a string is cut in the middle of double
   * quote scaping, that the parser can still handle it without problems.
   */
  public function testBrokenLookAhead() {
    $string1 = '"S "';
    $string2 = '"H"""';
    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->feed($string1);
    $parser->feed($string2);
    $parser->finish();

    $record = $parser->getRecord();
    $values = ['S "H"'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  public function testTrailingDelimiter() {
    $parser = $this->parse('H,F,' . "\n" . 'G,B,');

    $record = $parser->getRecord();
    $values = ['H', 'F', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['G', 'B', ''];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());

    $parser = new \Dkan\Datastore\Parser\Csv(",", "\"", "\\", ["\r", "\n"]);
    $parser->activateTrailingDelimiter();
    $parser->feed('H,F ' . "\n" . 'G,B,');
    $parser->finish();

    $record = $parser->getRecord();
    $values = ['H', 'F'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $record = $parser->getRecord();
    $values = ['G', 'B'];
    $this->assertNumberOfFieldsAndValues($record, $values);

    $this->assertNull($parser->getRecord());
  }

  private function assertNumberOfFieldsAndValues($record, $values) {
    $this->assertEquals(count($record), count($values));

    foreach ($record as $key => $value) {
      $this->assertEquals($values[$key], $value);
    }
  }
}