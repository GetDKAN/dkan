<?php

namespace Drupal\dkan\Schema;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

interface MetastoreSchemaInterface extends ContainerFactoryPluginInterface {
  
  public function getSchemaId(): string;

  public function getSchema(): object;

  public function getUiSchema(): ?object;

  public function getIdentifierProperty(): ?string;

  // public function getTriggers(): array;
}