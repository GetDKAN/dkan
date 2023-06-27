<?php

namespace Drupal\Tests\metastore\Unit\Plugin\Validation\Constraint;

use Drupal\metastore\Plugin\Validation\Constraint\ProperJsonValidator;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Context\ExecutionContext;
use PHPUnit\Framework\TestCase;

/**
 * Class.
 */
class ProperJsonValidatorTest extends TestCase {

  /**
   * The schema retriever used for testing.
   *
   * @var \Drupal\metastore\SchemaRetriever|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $schemaRetriever;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  /**
   * The container used for testing.
   */
  protected $container;

  /**
   * The context used for testing.
   *
   * @var Symfony\Component\Validator\Context\ExecutionContext|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->schemaRetriever = $this->getMockBuilder(SchemaRetriever::class)
      ->disableOriginalConstructor()
      ->onlyMethods(["retrieve"])
      ->getMock();

    $this->validMetadataFactory = $this->getMockBuilder(ValidMetadataFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(["getSchemaRetriever"])
      ->getMock();
    $this->validMetadataFactory->method('getSchemaRetriever')->willReturn($this->schemaRetriever);

    $this->container = $this->getMockBuilder(ContainerInterface::class)
      ->onlyMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->container->method('get')
      ->with('dkan.metastore.valid_metadata')
      ->willReturn($this->validMetadataFactory);

    $this->context = $this->getMockBuilder(ExecutionContext::class)
      ->onlyMethods(["addViolation"])
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Public.
   */
  public function testValidationSuccess() {
    $this->schemaRetriever->method('retrieve')->willReturn(
      json_encode(['foo' => 'bar'])
    );

    $validator = ProperJsonValidator::create($this->container);

    $this->context->expects($this->never())->method("addViolation");

    $validator->initialize($this->context);

    $validator->validate([(object) ['value' => "{}"]], new Count(['min' => 1, 'max' => 2]));
  }

  /**
   * Public.
   */
  public function testValidationFailure() {
    $this->schemaRetriever->method('retrieve')->willReturn(
      '{"type":"object","properties": {"number":{ "type":"number"}}}'
    );

    $validator = ProperJsonValidator::create($this->container);

    $this->context->expects($this->once())->method("addViolation");

    $validator->initialize($this->context);

    $validator->validate([(object) ['value' => '{"number":"foo"}']], new Count(['min' => 1, 'max' => 2]));
  }

}
