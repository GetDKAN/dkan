<?php

namespace Drupal\dkan_datastore;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class SqlParserTest extends TestCase {

  /**
   *
   */
  public function dataTestSQLParser() {
    return [
            ['foo', FALSE],
            ['[SELECT * FROM abc];', TRUE],
            ['[SELECT abc FROM abc];', TRUE],
            ['[SELECT abc,def FROM abc];', TRUE],
            ['[SELECT abc, def FROM abc];', FALSE],
            ['[SELECT * FROM abc][WHERE def = "hij"];', TRUE],
            ['[SELECT * FROM abc][WHERE def LIKE "hij"];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv];', FALSE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv][LIMIT 1];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv][LIMIT 1 OFFSET 2];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv ASC][LIMIT 1 OFFSET 2];', TRUE],
            ['[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv DESC][LIMIT 1 OFFSET 2];', TRUE],
    ];
  }

  /**
   * Tests validate and everything else.
   *
   * @param string $sqlString
   * @param bool $expected
   *
   * @dataProvider dataTestSQLParser
   */
  public function testSQLParser($sqlString, $expected) {

    $parser = new SqlParser();

    $actual = $parser->validate($sqlString);

    $this->assertEquals($actual, $expected);
  }

}
