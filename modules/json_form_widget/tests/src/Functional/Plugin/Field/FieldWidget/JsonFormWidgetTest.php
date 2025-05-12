<?php

namespace Drupal\Tests\json_form_widget\Functional\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_form_widget\Plugin\Field\FieldWidget\JsonFormWidget;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use MockChain\Chain;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the JsonFormWidget.
 *
 * @group dkan
 * @group json_form_widget
 * @group functional
 */
class JsonFormWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'json_form_widget',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';


  public function testNewDataset() {
    // Mock the SchemaRetriever service.
    $widget = JsonFormWidget::create(
      \Drupal::getContainer(),
      [
        'field_definition' => $this->createMock(FieldDefinitionInterface::class),
        'settings' => [],
        'third_party_settings' => [],
      ],
      'json_form_widget',
      [],
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
    $this->assertEquals('dcat:Dataset', $result['value']['@type']['#default_value']);
    $this->assertEquals(FALSE, $result['value']['@type']['#access']);
    $this->assertEquals('textarea', $result['value']['description']['#type']);
    $this->assertEquals('fieldset', $result['value']['keyword']['#type']);
    $this->assertEquals('select2', $result['value']['keyword']['keyword'][0]['#type']);
    $this->assertNotEmpty($result['value']['references']['array_actions']['actions']);

    // Now try it again simulating a new dataset.
    $dataset = Node::create(['type' => 'data']);
    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', ContentEntityFormInterface::class)
      ->add(ContentEntityFormInterface::class, 'getEntity', $dataset)
      ->getMock();
    $result = $widget->formElement($items, 0, $element, $form, $form_state);
    $this->assertEquals('dcat:Dataset', $result['value']['@type']['#default_value']);
    $this->assertEquals(FALSE, $result['value']['@type']['#access']);
    $this->assertEquals('textarea', $result['value']['description']['#type']);
    $this->assertEquals('fieldset', $result['value']['keyword']['#type']);
    $this->assertEquals('select2', $result['value']['keyword']['keyword'][0]['#type']);
    $this->assertNotEmpty($result['value']['references']['array_actions']['actions']);

    // Simulate a new node form, but change the request stack to have query ?schema=distribution
    $session = \Drupal::service('session');
    $distro_request = new Request([
      'schema' => 'distribution',
    ]);
    $distro_request->setSession($session);
    \Drupal::service('request_stack')->push($distro_request);
    $widget = JsonFormWidget::create(
      \Drupal::getContainer(),
      [
        'field_definition' => $this->createMock(FieldDefinitionInterface::class),
        'settings' => [],
        'third_party_settings' => [],
      ],
      'json_form_widget',
      [],
    );
    $dataset = Node::create(['type' => 'data']);
    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', ContentEntityFormInterface::class)
      ->add(ContentEntityFormInterface::class, 'getEntity', $dataset)
      ->getMock();
    $result = $widget->formElement($items, 0, $element, $form, $form_state);
    $this->assertEquals("dcat:Distribution", $result['value']['data']['data']['@type']['#default_value']);

    // Simulate a new node form, this time give it an invalid schema name.
    $distro_request = new Request([
      'schema' => 'foo',
    ]);
    $distro_request->setSession($session);
    \Drupal::service('request_stack')->push($distro_request);
    $widget = JsonFormWidget::create(
      \Drupal::getContainer(),
      [
        'field_definition' => $this->createMock(FieldDefinitionInterface::class),
        'settings' => [],
        'third_party_settings' => [],
      ],
      'json_form_widget',
      [],
    );
    $dataset = Node::create(['type' => 'data']);
    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', ContentEntityFormInterface::class)
      ->add(ContentEntityFormInterface::class, 'getEntity', $dataset)
      ->getMock();
    try {
      $result = $widget->formElement($items, 0, $element, $form, $form_state);
      $this->fail('Expected exception not thrown.');
    }
    catch (\Exception $e) {
      $this->assertStringContainsString('Schema foo not found', $e->getMessage());
    }

    // Simulate non-entity form, should throw exception.
    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', FormInterface::class)
      ->getMock();
    try {
      $result = $widget->formElement($items, 0, $element, $form, $form_state);
      $this->fail('Expected exception not thrown.');
    }
    catch (\Exception $e) {
      $this->assertStringContainsString('No valid form entity found', $e->getMessage());
    }
    
  }

}
