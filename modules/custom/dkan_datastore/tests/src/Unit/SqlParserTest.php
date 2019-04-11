<?php

namespace Drupal\dkan_datastore;

use Maquina\Feeder;

use \PHPUnit\Framework\TestCase;

class SqlParserTest extends TestCase
{

  public function testSQLParser() {

    $valid_sql_strings = [];
    $valid_sql_strings[] = '[SELECT * FROM abc];';
    $valid_sql_strings[] = '[SELECT abc FROM abc];';
    $valid_sql_strings[] = '[SELECT abc,def FROM abc];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij"];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def LIKE "hij"];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs,tuv];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv][LIMIT 1];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv][LIMIT 1 OFFSET 2];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv ASC][LIMIT 1 OFFSET 2];';
    $valid_sql_strings[] = '[SELECT * FROM abc][WHERE def = "hij" AND klm = "nop"][ORDER BY qrs, tuv DESC][LIMIT 1 OFFSET 2];';
    
    foreach ($valid_sql_strings as $string) {
      $parser = new SqlParser();
      $machine = $parser->getMachine();
      Feeder::feed($string, $machine);
      $this->assertTrue($machine->isCurrentlyAtAnEndState());
    }

  }

}
