<?php

namespace Drupal\Tests\dkan_metastore\Unit\Plugin\Validation\Constraint;

use Drupal\dkan_metastore\Plugin\Validation\Constraint\ProperJsonValidator;
use Drupal\dkan_metastore\SchemaRetriever;
use Drupal\dkan_metastore\ValidMetadataFactory;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class.
 */
class ProperJsonValidatorTest extends UnitTestCase {

  /**
   * The config factory used for testing.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The schema retriever used for testing.
   *
   * @var \Drupal\dkan_metastore\SchemaRetriever|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $schemaRetriever;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\dkan_metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
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

    $this->configFactory = $this->getConfigFactoryStub([
      'metastore.settings' => [
        'disable_json_validation' => FALSE,
      ],
    ]);

    $this->container = $this->getMockBuilder(ContainerInterface::class)
      ->onlyMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->container->method('get')
      ->willReturnMap([
        ['config.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configFactory],
        ['dkan.metastore.valid_metadata', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->validMetadataFactory],
      ]);

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

    // Assert addViolation is invoked with a non-empty string referencing the
    // failing field. This catches regressions where ErrorFormatter output
    // becomes empty or malformed.
    $this->context->expects($this->once())
      ->method('addViolation')
      ->with($this->logicalAnd(
        $this->isType('string'),
        $this->stringContains('number')
      ));

    $validator->initialize($this->context);

    $validator->validate([(object) ['value' => '{"number":"foo"}']], new Count(['min' => 1, 'max' => 2]));
  }

}
