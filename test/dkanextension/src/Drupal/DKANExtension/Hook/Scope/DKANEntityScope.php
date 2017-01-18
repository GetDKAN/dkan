<?php
/**
 * @file
 * Node scope.
 */
namespace Drupal\DKANExtension\Hook\Scope;

use Behat\Testwork\Hook\Scope\HookScope;
use Behat\Behat\Context\Context;
use Behat\Testwork\Environment\Environment;
use EntityDrupalWrapper;

/**
 * Represents an Entity hook scope.
 */
abstract class DKANEntityScope implements HookScope {

  const BEFORE = 'dkanentity.create.before';
  const AFTER = 'dkanentity.create.after';

  /**
   * @var Environment
   */
  private $environment;

  /**
   * Context object.
   *
   * @var \Behat\Behat\Context\Context
   */
  private $context;

  /**
   * Entity object.
   *
   * @var EntityDrupalWrapper
   */
  private $entity;

  /**
   * Initializes the scope.
   */
  public function __construct(Environment $environment, Context $context, $entity, &$fields) {
    $this->context = $context;
    $this->entity = $entity;
    $this->environment = $environment;
    $this->fields = &$fields;
  }

  /**
   * Returns the context.
   *
   * @return \Behat\Behat\Context\Context
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * Returns the entity object.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Returns the original fields from the step.
   */
  public function &getFields() {
    return $this->fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * {@inheritDoc}
   */
  public function getSuite() {
    return $this->environment->getSuite();
  }
}
