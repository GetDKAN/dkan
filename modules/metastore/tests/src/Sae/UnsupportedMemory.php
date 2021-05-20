<?php

//namespace SaeTest;
namespace Drupal\Tests\metastore\Unit\Sae\SaeTest;

//use Contracts\StorerInterface;
//use Contracts\RemoverInterface;
use Drupal\metastore\Storage\MetastoreStorageInterface;

//class UnsupportedMemory implements StorerInterface, RemoverInterface
class UnsupportedMemory implements MetastoreStorageInterface
{
    private $storage = [];

    public function retrieve(string $id)
    {
        if (isset($this->storage[$id])) {
            return $this->storage[$id];
        }
        return null;
    }

    public function store($data, string $id = null): string
    {
        if (!isset($this->storage[$id])) {
            $this->storage[$id] = $data;
            return $id;
        }
        $this->storage[$id] = $data;
        return $id;
    }

    public function remove(string $id)
    {
        if (isset($this->storage[$id])) {
            unset($this->storage[$id]);
            return true;
        }
        return false;
    }

  public function retrieveAll() : array {}

  public function retrievePublished(string $uuid) : ?string {}

  public function publish(string $uuid) : string {}

}
