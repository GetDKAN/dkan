<?php

namespace Drupal\Tests\dkan\Unit\CsvParser\Parser;

use Drupal\dkan\CsvParser\Parser\Csv;
use Drupal\dkan\CsvParser\Parser\StateMachine;
use PHPUnit\Framework\TestCase;

/**
 * @group dkan
 * @group csvparser
 *
 * @covers \Drupal\dkan\CsvParser\Parser\Csv
 * @coversDefaultClass \Drupal\dkan\CsvParser\Parser\Csv
 */
class CsvParserTest extends TestCase {

  private function parse(string $string) {
    $parser = Csv::getParser(',', '"', '\\', ["\r", "\n"]);
    $parser->feed($string);
    $parser->finish();
    return $parser;
  }

  public function testEmptyString(): void {
    $this->expectExceptionMessage('The CSV parser can not parse empty chunks.');
    $this->parse('');
  }

  public function testJustDelimiters(): void {
    $parser = $this->parse(',,');
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testEmptyNewLineString(): void {
    $parser = $this->parse("\n");
    $record = $parser->getRecord();
    $values = [''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testJustDelimitersNewLineString(): void {
    $parser = $this->parse(",,\n,,");
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testBlankString(): void {
    $parser = $this->parse('   ');
    $record = $parser->getRecord();
    $values = [''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testBlankJustDelimiters(): void {
    $parser = $this->parse('  ,   ,    ');
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testBlankNewLineString(): void {
    $parser = $this->parse("  \n   ");
    $record = $parser->getRecord();
    $values = [''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = [''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testBlankJustDelimitersNewLineString(): void {
    $parser = $this->parse("  ,   ,    \n  ,   ,    ");
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOther(): void {
    $parser = $this->parse('A');
    $record = $parser->getRecord();
    $values = ['A'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherJustDelimiters(): void {
    $parser = $this->parse('A,B,C');
    $record = $parser->getRecord();
    $values = ['A', 'B', 'C'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherNewLineString(): void {
    $parser = $this->parse("A\nB");
    $record = $parser->getRecord();
    $values = ['A'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['B'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherJustDelimitersNewLineString(): void {
    $parser = $this->parse("A,B,C\nD,E,F");
    $record = $parser->getRecord();
    $values = ['A', 'B', 'C'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['D', 'E', 'F'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlank(): void {
    $parser = $this->parse('  A B  ');
    $record = $parser->getRecord();
    $values = ['A B'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankJustDelimiters(): void {
    $parser = $this->parse(' A B ,B  C , CD');
    $record = $parser->getRecord();
    $values = ['A B', 'B  C', 'CD'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankNewLineString(): void {
    $parser = $this->parse("A B   \n   B  C");
    $record = $parser->getRecord();
    $values = ['A B'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['B  C'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankJustDelimitersNewLineString(): void {
    $parser = $this->parse(" AB,B C \n D  E,E   F ");
    $record = $parser->getRecord();
    $values = ['AB', 'B C'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['D  E', 'E   F'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscape(): void {
    $parser = $this->parse('  A \\' . "\n" . 'B\,  ');
    $record = $parser->getRecord();
    $values = ["A \nB,"];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscapeJustDelimiters(): void {
    $parser = $this->parse(' A \\\B ,B \\' . "\n" . ' C , CD');
    $record = $parser->getRecord();
    $values = ['A \\B', "B \n C", 'CD'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscapeNewLineString(): void {
    $parser = $this->parse('A B \\' . "\n  \n" . ' \\,  B  C ');
    $record = $parser->getRecord();
    $values = ["A B \n"];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = [',  B  C'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testOtherBlankEscapeJustDelimitersNewLineString(): void {
    $parser = $this->parse(' A \\' . "\n" . ' B,B C ' . "\n" . ' \\\D  E\,,E   F ');
    $record = $parser->getRecord();
    $values = ["A \n B", 'B C'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['\D  E,', 'E   F'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testQuotes(): void {
    $parser = $this->parse('""');
    $record = $parser->getRecord();
    $values = [''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testQuoteDelimiters(): void {
    $parser = $this->parse('" A B " , " B ' . "\n" . ' C"');
    $record = $parser->getRecord();
    $values = [' A B ', " B \n C"];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testQuoteNewLineString(): void {
    $parser = $this->parse('  " A B ' . "\n" . '"  ' . "\n" . ' ", B \" C "');
    $record = $parser->getRecord();
    $values = [" A B \n"];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = [', B " C '];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testQuoteJustDelimitersNewLineString(): void {
    $parser = $this->parse(' "A ' . "\n" . ' B"  , "B C" ' . "\n" . ' "\\\D  E," , "E   F" ');
    $record = $parser->getRecord();
    $values = ["A \n B", 'B C'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['\D  E,', 'E   F'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testDoubleQuoteEscaping(): void {
    $parser = $this->parse('"S ""H"""');
    $record = $parser->getRecord();
    $values = ['S "H"'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
    $parser = $this->parse('"S ""H"" S"');
    $record = $parser->getRecord();
    $values = ['S "H" S'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
    $parser = $this->parse('"""H"" S"');
    $record = $parser->getRecord();
    $values = ['"H" S'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  /**
   * Double quote parsing requires looking ahead or back as we process a string.
   * We went to make sure that when a string is cut in the middle of double
   * quote scaping, that the parser can still handle it without problems.
   */
  public function testBrokenLookAhead(): void {
    $string1 = '"S "';
    $string2 = '"H"""';
    $parser = Csv::getParser(',', '"', '\\', ["\r", "\n"]);
    $parser->feed($string1);
    $parser->feed($string2);
    $parser->finish();
    $record = $parser->getRecord();
    $values = ['S "H"'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testTrailingDelimiter(): void {
    $parser = $this->parse('H,F,' . "\n" . 'G,B,');
    $record = $parser->getRecord();
    $values = ['H', 'F', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['G', 'B', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());

    $parser = Csv::getParser(',', '"', '\\', ["\r", "\n"]);
    $parser->activateTrailingDelimiter();

    $parser->feed('H,F ' . "\n" . 'G,B,');
    $parser->finish();
    $record = $parser->getRecord();
    $values = ['H', 'F'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['G', 'B'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testMultiEnd(): void {
    $parser = $this->parse('H,F' . "\n\r" . 'G,B');
    $record = $parser->getRecord();
    $values = ['H', 'F'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['G', 'B'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());

    $parser = $this->parse('H,F' . "\n\r\n" . 'G,B');
    $record = $parser->getRecord();
    $values = ['H', 'F'];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['G', 'B'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());

    $parser = $this->parse("a,b\r\n");
    $record = $parser->getRecord();
    $values = ['a', 'b'];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testSerialization(): void {
    $parser = Csv::getParser(',', '"', '\\', ["\r", "\n"]);
    $parser->feed("a,b,c,d\n");

    $json = json_encode($parser->jsonSerialize());

    $parser2 = Csv::hydrate($json);
    $this->assertTrue($parser2 instanceof Csv);
    $parser2->feed('e,f,g,h');

    $parser2->finish();

    $record1 = $parser2->getRecord();
    $this->assertEquals($record1[0], 'a');

    $record2 = $parser2->getRecord();
    $this->assertEquals($record2[0], 'e');
  }

  public function testEndingWithNewLineString(): void {
    $parser = $this->parse(",,\n,,\n");
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testEndingWithWindowsNewLineString(): void {
    $parser = $this->parse(",,\r\n,,\r\n");
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testEndingWithMultipleNewLinesString(): void {
    $parser = $this->parse(",,\n,,\n\n\n");
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testEndingWithMultipleWindowsNewLinesString(): void {
    $parser = $this->parse(",,\r\n,,\r\n\r\n\r\n");
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $record = $parser->getRecord();
    $values = ['', '', ''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  public function testEmptyWindowsNewLineString(): void {
    $parser = $this->parse("\r\n");
    $record = $parser->getRecord();
    $values = [''];
    $this->assertEquals($values, $record);
    $this->assertNull($parser->getRecord());
  }

  /**
   * @covers ::finish
   */
  public function testFinishException(): void {
    $this->expectExceptionMessage('Machine did not halt');

    $csv = Csv::getParser();
    // Set lastCharType to StateMachine::CHAR_TYPE_RECORD_END. Use
    // reflection since it's not public and doesn't have a setter.
    $last_char_type = new \ReflectionProperty(Csv::class, 'lastCharType');
    $last_char_type->setAccessible(TRUE);
    $last_char_type->setValue($csv, StateMachine::CHAR_TYPE_RECORD_END);
    // Mock a machine.
    $machine = $this->getMockBuilder(StateMachine::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['isCurrentlyAtAnEndState'])
      ->getMock();
    $machine->method('isCurrentlyAtAnEndState')
      ->willReturn(FALSE);
    // Since ::$machine is public, we can set it easily.
    $csv->machine = $machine;
    // Finally call it.
    $csv->finish();
  }

}
