<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use Drupal\Core\Form\FormState;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\metastore\SchemaRetriever;
use Drupal\data_dictionary_widget\Controller\Widget\FieldAddCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldButtons;
use Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks;
use Drupal\data_dictionary_widget\Controller\Widget\FieldCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldEditCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldOperations;
use Drupal\data_dictionary_widget\Controller\Widget\FieldValues;
use MockChain\Options;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\data_dictionary_widget\Plugin\Field\FieldWidget\DataDictionaryWidget;


/**
 * Test class for DataDictionaryWidget.
 *
 * @group dkan
 * @group data_dictionary_widget
 * @group unit
 */
class DataDictionaryWidgetBuildTest extends TestCase {

  /**
   * Test a new/empty Data Dictionary Widget.
   */
  public function testEmptyDataDictionaryWidget() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $formObject = $this->createMock(EntityFormInterface::class);
    $entity = $this->createMock(FieldableEntityInterface::class);
    $fieldItemList = $this->createMock(FieldItemListInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];
    $form = [];
    $plugin_id = '';
    $plugin_definition = [];

    // Expectations for getFormObject().
    $formState->expects($this->once())
      ->method('getFormObject')
      ->willReturn($formObject);

    // Expectations for getEntity().
    $formObject->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);

    // Expectations for set() method if form entity is FieldableEntityInterface.
    $entity->expects($this->once())
      ->method('set')
      ->with('field_data_type', 'data-dictionary');

    $dataDictionaryWidget = new DataDictionaryWidget (
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );

    // Call the method under test.
    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    $this->assertNotNull($element);
    $this->assertArrayHasKey('identifier', $element, 'Identifier Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('title', $element, 'Identifier Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('dictionary_fields', $element, 'Identifier Field Does Not Exist On The Data Dictionary Form');
  }
}
