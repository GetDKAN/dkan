<?php

namespace Drupal\Tests\datastore\Unit\SqlParser;

use PHPUnit\Framework\TestCase;
use Drupal\datastore\SqlParser\SqlParser;

/**
 * @group dkan
 * @group datastore
 * @group sqlparser
 * @group unit
 *
 * @covers \Drupal\datastore\SqlParser\SqlParser
 * @coversDefaultClass \Drupal\datastore\SqlParser\SqlParser
 */
class SqlParserTest extends TestCase {

  /**
   * Data provider.
   */
  public static function dataTestSqlParser(): array {
    return [
          ['foo', FALSE],
          ['[SELECT * FROM abc];', TRUE],
          ['[SELECT *a FROM abc];', FALSE],
          ['[SELECT abc FROM abc];', TRUE],
          ['[SELECT abc,def FROM abc];', TRUE],
          ['[SELECT abc, def FROM abc];', FALSE],
          ['[SELECT * FROM abc][WHERE def = "hij"];', TRUE],
          ['[SELECT * FROM abc][ORDER BY qrs ASC];', TRUE],
          ['[SELECT * FROM abc][ORDER BY qrs,tuv DESC][LIMIT 1 OFFSET 2];', TRUE],
          ['[SELECT * FROM abc][LIMIT 1 OFFSET 2];', TRUE],
          ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"];', TRUE],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs ASC];',
            TRUE,
          ],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv];',
            FALSE,
          ],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv DESC];',
            TRUE,
          ],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv][LIMIT 1];',
            FALSE,
          ],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv ASC][LIMIT 1 OFFSET 2];',
            TRUE,
          ],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv ASC][LIMIT 1 OFFSET 2];',
            TRUE,
          ],
          [
            '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv DESC][LIMIT 1 OFFSET 2];',
            TRUE,
          ],
    ];
  }

  /**
   * Tests validate and everything else.
   *
   * @param string $sqlString
   *   SQL string.
   * @param bool $expected
   *   Whether the SQL string is valid or not.
   *
   * @dataProvider dataTestSqlParser
   */
  public function testSqlParser(string $sqlString, bool $expected): void {
    $parser = new SqlParser();

    $actual = $parser->validate($sqlString);

    $this->assertEquals($actual, $expected);
  }

}
