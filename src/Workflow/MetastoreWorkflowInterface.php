<?php

namespace Drupal\dkan\Workflow;

use Drupal\dkan\MetastoreItemInterface;

interface MetastoreWorkflowInterface {

  public function publish(MetastoreItemInterface $item): void;

  public function archive(MetastoreItemInterface $item): void;

}