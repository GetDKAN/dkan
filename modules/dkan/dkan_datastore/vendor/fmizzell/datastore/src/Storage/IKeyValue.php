<?php

namespace Dkan\Datastore\Storage;

interface IKeyValue
{
  public function set($key, $value);

  public function get($key, $default = NULL);
}