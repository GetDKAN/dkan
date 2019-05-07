<?php

use Drupal\dkan_data\Plugin\Validation\Constraint\ProperJsonValidator;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Context\ExecutionContext;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ProperJsonValidatorTest extends TestCase {

  /**
   *
   */
  public function testValidationSuccess() {
    $validator = $this->getMockBuilder(ProperJsonValidator::class)
      ->setMethods(["isProper"])
      ->getMock();

    $validator->expects($this->once())->method("isProper")->willReturn(['valid' => TRUE]);

    $context = $this->getMockBuilder(ExecutionContext::class)
      ->setMethods(["addViolation"])
      ->disableOriginalConstructor()
      ->getMock();

    $context->expects($this->never())->method("addViolation");

    $validator->initialize($context);

    $validator->validate([(object) ['value' => "{}"]], new Count(['min' => 1, 'max' => 2]));
  }

  /**
   *
   */
  public function testValidationFailure() {
    $validator = $this->getMockBuilder(ProperJsonValidator::class)
      ->setMethods(["isProper"])
      ->getMock();

    $validator->expects($this->once())
      ->method("isProper")
      ->willReturn([
        'valid' => FALSE,
        'errors' => [
          ['message' => "yep"],
        ],
      ]);

    $context = $this->getMockBuilder(ExecutionContext::class)
      ->setMethods(["addViolation"])
      ->disableOriginalConstructor()
      ->getMock();

    $context->expects($this->once())->method("addViolation");

    $validator->initialize($context);

    $validator->validate([(object) ['value' => "{}"]], new Count(['min' => 1, 'max' => 2]));
  }

}
