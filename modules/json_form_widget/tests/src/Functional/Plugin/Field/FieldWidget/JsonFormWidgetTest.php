<?php

namespace Drupal\Tests\json_form_widget\Functional\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_form_widget\FormBuilder;
use Drupal\json_form_widget\Plugin\Field\FieldWidget\JsonFormWidget;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use MockChain\Chain;
use Symfony\Component\HttpFoundation\RequestStack;

class JsonFormWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'json_form_widget',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';


  public function testNewDataset() {
    // Mock the SchemaRetriever service.
    $widget = new JsonFormWidget(
      'json_form_widget',
      [],
      $this->createMock(FieldDefinitionInterface::class),
      [],
      [],
      \Drupal::service('json_form.builder'),
      \Drupal::service('json_form.value_handler'),
      (new RequestStack()),
      \Drupal::service('dkan.metastore.schema_retriever'),
    );

    $element = [
      '#title' => 'JSON Metadata',
      '#title_display' => 'before',
      '#description' => 'JSON Metadata',
      '#field_parents' => [],
      '#required' => FALSE,
      '#delta' => 0,
      '#weigtht' => 0,
    ];

    $dataset = Node::create([
      'type' => 'data',
      'title' => 'Test Dataset',
      'field_data_type' => 'dataset',
      'field_json_metadata' => [
        'value' => json_encode([
          'title' => 'Test Dataset',
          'description' => 'This is a test dataset.',
          'license' => 'CC0-1.0',
          'keywords' => ['test', 'dataset'],
          'publisher' => [
            '@type' => 'Organization',
            'name' => 'Test Publisher',
          ],
        ]),
      ],
    ]);
    $dataset->save();

    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', ContentEntityFormInterface::class)
      ->add(ContentEntityFormInterface::class, 'getEntity', $dataset)
      ->getMock();

    $items = $this->createMock(FieldItemList::class);

    $form = [];

    $result = $widget->formElement($items, 0, $element, $form, $form_state);

    // Assert some basic properties we'd expect from the processed form element.
    $this->assertEquals(FALSE, $result['value']['@type']['#access']);
    $this->assertEquals('textarea', $result['value']['description']['#type']);
    $this->assertEquals('fieldset', $result['value']['keyword']['#type']);
    $this->assertEquals('select2', $result['value']['keyword']['keyword'][0]['#type']);
    $this->assertNotEmpty($result['value']['references']['actions']['actions']);
  }

}
