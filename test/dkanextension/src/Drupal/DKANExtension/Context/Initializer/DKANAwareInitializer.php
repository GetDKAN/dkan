<?php

namespace Drupal\DKANExtension\Context\Initializer;

use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Behat\Context\Context;
use Drupal\DKANExtension\Context\DKANAwareInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;

class DKANAwareInitializer extends RawDrupalContext implements ContextInitializer {
  private $entityStore, $pageStore, $parameters;

  public function __construct($entityStore, $pageStore, array $parameters) {
    $this->entityStore = $entityStore;
    $this->pageStore = $pageStore;
    $this->parameters = $parameters;
  }

  /**
   * {@inheritdocs}
   */
  public function initializeContext(Context $context) {

    // All contexts are passed here, only RawDKANEntityContext is allowed.
    if (!$context instanceof DKANAwareInterface) {
      return;
    }
    $context->setEntityStore($this->entityStore);
    $context->setPageStore($this->pageStore);

    // Add all parameters to the context.
    //$context->setParameters($this->parameters);
  }

}
