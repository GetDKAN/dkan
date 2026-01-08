<?php

namespace Drupal\dkan_datastore\Service;

/**
 * Collector responsible for gathering processors to run after resource import.
 */
class ResourceProcessorCollector {

  /**
   * Post import resource processors.
   *
   * @var \Drupal\dkan_datastore\Service\ResourceProcessorInterface[]
   */
  protected array $processors;

  /**
   * Service collector add method.
   *
   * @param \Drupal\dkan_datastore\Service\ResourceProcessorInterface $processor
   *   Post import resource processor.
   * @param int $priority
   *   Priority to associate with processor.
   */
  public function addResourceProcessor(ResourceProcessorInterface $processor, int $priority): void {
    $this->processors[$priority] = $processor;
  }

  /**
   * Retrieve collected resource processors.
   *
   * @return \Drupal\dkan_datastore\Service\ResourceProcessorInterface[]
   *   Collected resource processors sorted in ascending order of priority.
   */
  public function getResourceProcessors(): array {
    // Sort processors by priority.
    ksort($this->processors);

    return $this->processors;
  }

}
