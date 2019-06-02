<?php

namespace Dkan\Datastore\Storage;

use Dkan\Datastore\Storage\Database\Query\Insert;

interface IDatabase
{
  public function tableExist($table_name);

  public function tableCreate($table_name, $scema);

  public function tableDrop($table_name);

  public function count($table_name);

  public function insert(Insert $query);
}