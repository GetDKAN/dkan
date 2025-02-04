<?php

namespace Drupal\Tests\json_form_widget\Unit;

use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\field\Plugin\migrate\source\d6\Field;
use Drupal\json_form_widget\FieldTypeRouter;
use Drupal\json_form_widget\StringHelper;
use MockChain\Options;

/**
 * Test class for StringHelper.
 *
 * @group dkan
 * @group json_form_widget
 * @group unit
 */
class StringHelperTest extends TestCase {

  /**
   * Test.
   */
  public function testEmailValidate() {
    $options = (new Options())
      ->add('string_translation', TranslationManager::class)
      ->add('email.validator', EmailValidator::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(EmailValidator::class, 'isValid', TRUE);
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $string_helper = StringHelper::create($container);
    $element["#parents"] = [];
    $form = [
      "hasEmail" => [
        "#type" => "email",
        "#title" => "Email",
        "#description" => "Email address for the contact name.",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#element_validate" => [StringHelper::class, 'validateEmail'],
      ],
    ];

    // Should return nothing.
    $form_state = new FormState();
    $element["#value"] = "";
    $return = $string_helper->validateEmail($element, $form_state, $form);
    $this->assertEmpty($return);

    // Should raise errors.
    $form_state = new FormState();
    $element["#value"] = "test";
    $string_helper->validateEmail($element, $form_state, $form);
    $this->assertNotEmpty($form_state->getErrors());

    // Should raise errors.
    $form_state = new FormState();
    $element["#value"] = "test@test";
    $string_helper->validateEmail($element, $form_state, $form);
    $this->assertNotEmpty($form_state->getErrors());

    // Should not raise errors.
    $form_state = new FormState();
    $element["#value"] = "test@test.com";
    $string_helper->validateEmail($element, $form_state, $form);
    $this->assertEmpty($form_state->getErrors());
  }

  /**
   * Test that StringHelper::handleStringElement correctly reemoves mailto:.
   */
  public function testHandleStringElement() {
    $definition = [
      'schema' => (object) [
        'title' => 'Email',
        'description' => 'Email address for the contact name.',
        'type' => 'string',
        "pattern" => "^mailto:[\\w\\_\\~\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=\\:.-]+@[\\w.-]+\\.[\\w.-]+?$|[\\w\\_\\~\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=\\:.-]+@[\\w.-]+\\.[\\w.-]+?$",
      ],
      'name' => 'hasEmail',
    ];

    $string_helper = new StringHelper(new EmailValidator());
    $field_type_router = $this->createMock(FieldTypeRouter::class);
    $field_type_router->method('getSchema')->willReturn((object) [
      'type' => 'object',
      'properties' => [
        'hasEmail' => $definition['schema'],
      ],
    ]);
    $string_helper->setBuilder($field_type_router);

    $data = 'mailto:john@doe.com';

    $result = $string_helper->handleStringElement($definition, $data);
    $this->assertEquals('john@doe.com', $result['#default_value']);
  }

}
