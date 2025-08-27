<?php

namespace Drupal\Tests\metastore\Functional\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metastore\Plugin\Field\FieldWidget\DkanJsonFormWidget;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;
use MockChain\Chain;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test the JsonFormWidget.
 *
 * @group dkan
 * @group json_form_widget
 * @group functional
 */
class DkanJsonFormWidgetTest extends BrowserTestBase {

  protected RequestStack $requestStack;

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

  public function setUp(): void {
    parent::setUp();
    $this->requestStack = \Drupal::service('request_stack');
  }

  public function testNewDataset() {
    // Mock the SchemaRetriever service.
    $widget = $this->initializeWidget();

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
    $this->setSchemaQuery('distribution');
    $widget = DkanJsonFormWidget::create(
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
    $this->setSchemaQuery('foo');
    $widget = $this->initializeWidget();
    $dataset = Node::create(['type' => 'data']);
    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', ContentEntityFormInterface::class)
      ->add(ContentEntityFormInterface::class, 'getEntity', $dataset)
      ->getMock();
    try {
      $result = $widget->formElement($items, 0, $element, $form, $form_state);
      $this->fail('Expected exception not thrown.');
    }
    catch (BadRequestException $e) {
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

    // Copy the dataset schema file to the docroot schema directory.
    $this->schemaCopy("dataset");

    // Re-initialize the widget with the new schema file.
    $widget = $this->initializeWidget();

    // Set the schema query to dataset.
    $this->setSchemaQuery('dataset');

    // Simulate a new node form, this time it has no ui schema.
    $dataset = Node::create(['type' => 'data']);
    $form_state = (new Chain($this))
      ->add(FormStateInterface::class, 'getFormObject', ContentEntityFormInterface::class)
      ->add(ContentEntityFormInterface::class, 'getEntity', $dataset)
      ->getMock();
    $result = $widget->formElement($items, 0, $element, $form, $form_state);
    // Assert form is still a dataset...
    $this->assertEquals('dcat:Dataset', $result['value']['@type']['#default_value']);
    // ... but now the description field is a textfield, not a textarea.
    $this->assertEquals('textfield', $result['value']['description']['#type']);
    $this->schemaCleanup();
  }

  /**
   * Set the schema query parameter in the request stack.
   *
   * @param string $schema
   *   The schema name to set.
   */
  protected function setSchemaQuery(string $schema) {
    $request = new Request([
      'schema' => $schema,
    ]);
    $request->setSession($this->requestStack->getCurrentRequest()->getSession());
    $this->requestStack->push($request);
  }

  /**
   * Initialize the JsonFormWidget.
   *
   * @return \Drupal\json_form_widget\Plugin\Field\FieldWidget\JsonFormWidget
   *   The initialized widget.
   */
  protected function initializeWidget() {
    return DkanJsonFormWidget::create(
      \Drupal::getContainer(),
      [
        'field_definition' => $this->createMock(FieldDefinitionInterface::class),
        'settings' => [],
        'third_party_settings' => [],
      ],
      'json_form_widget',
      [],
    );
  }

  /**
   * Copy the schema file to the docroot schema directory.
   *
   * @param string $schema
   *   The schema name to copy.
   */
  protected function schemaCopy(string $schema) {
    $source = \Drupal::service('extension.list.module')->getPath('dkan') . "/schema/collections/{$schema}.json";
    $destDir = \Drupal::root() . '/schema/collections';
    $dest = $destDir . "/{$schema}.json";
    if (!file_exists($destDir)) {
      mkdir($destDir, 0777, TRUE);
    }
    if (file_exists($source)) {
      copy($source, $dest);
      $this->assertTrue(file_exists($dest), "{$schema} schema file copied successfully");
    }
    else {
      $this->fail('Source schema file not found at ' . $source);
    }
  }

  /**
   * Clean up the schema directory after the test.
   */
  protected function schemaCleanup(): void {
    // Clean up the schema directory after the test.
    $destDir = \Drupal::root() . '/schema/collections';
    $dest = $destDir . '/dataset.json';

    if (file_exists($dest)) {
      unlink($dest);
    }
    // Remove the directory if it's empty.
    if (is_dir($destDir) && count(scandir($destDir)) === 2) {
      rmdir($destDir);
      rmdir(dirname($destDir));
    }
    parent::tearDown();
  }

}
