<?php

namespace Drupal\Tests\json_form_widget\Unit;

use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Uuid\Php;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\json_form_widget\SchemaUiHandler;
use Drupal\json_form_widget\StringHelper;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\Service;
use MockChain\Options;

/**
 * Test class for SchemaUiHandlerTest.
 */
class SchemaUiHandlerTest extends TestCase {

  /**
   * Test.
   */
  public function testSchemaUi() {
    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('json_form.string_helper', StringHelper::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.service', Service::class)
      ->add('uuid', Php::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(SchemaRetriever::class, 'retrieve', '{"@test":{"ui:options":{"widget":"hidden"}},"textarea_text":{"ui:options":{"widget":"textarea","rows":4,"cols":45,"title":"Textarea field","description":"Test description"}},"date":{"ui:options":{"placeholder":"YYYY-MM-DD"}},"disabled":{"ui:options":{"disabled":true}}}')
      ->add(SchemaUiHandler::class, 'setSchemaUi');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      "@test" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#description" => "",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
      "textarea_text" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
      "date" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
      "disabled" => [
        "#type" => "textfield",
        "#title" => "Test disabled field",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
    ];
    $expected = [
      "@test" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#description" => "",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#access" => FALSE,
      ],
      "textarea_text" => [
        "#type" => "textarea",
        "#title" => "Textarea field",
        "#description" => "Test description",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#rows" => 4,
        "#cols" => 45,
      ],
      "date" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#attributes" => [
          "placeholder" => "YYYY-MM-DD",
        ],
      ],
      "disabled" => [
        "#type" => "textfield",
        "#title" => "Test disabled field",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#disabled" => TRUE,
      ],
    ];
    $this->assertEquals($ui_handler->applySchemaUi($form), $expected);

    // Test dkan_uuid field with already existing value.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"identifier":{"ui:options":{"widget":"dkan_uuid"}}}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      'identifier' => [
        '#type' => 'textfield',
        '#title' => 'Identifier',
        '#description' => 'Some description',
        '#required' => TRUE,
        '#default_value' => 'cedcd327-4e5d-43f9-8eb1-c11850fa7c55',
      ],
    ];

    $expected = [
      'identifier' => [
        '#type' => 'textfield',
        '#title' => 'Identifier',
        '#description' => 'Some description',
        '#required' => TRUE,
        '#default_value' => 'cedcd327-4e5d-43f9-8eb1-c11850fa7c55',
        '#access' => FALSE,
      ],
    ];
    $this->assertEquals($ui_handler->applySchemaUi($form), $expected);

    // Test dkan_uuid field, adding new value.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"identifier":{"ui:options":{"widget":"dkan_uuid"}}}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      'identifier' => [
        '#type' => 'textfield',
        '#title' => 'Identifier',
        '#description' => 'Some description',
        '#required' => TRUE,
        '#default_value' => '',
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertNotEmpty($form['identifier']['#default_value']);

    // Test array field.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"references":{"ui:options":{"title":"Related documents","description":"Improved description"},"items":{"ui:options":{"title":"References","placeholder":"http://"}}}}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      'references' => [
        '#type' => 'fieldset',
        '#title' => 'References',
        '#description' => 'Some description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'references' => [
          0 => [
            '#type' => 'textfield',
            '#title' => 'Ref',
            '#default_value' => 'Transportation',
          ],
          1 => [
            '#type' => 'textfield',
            '#title' => 'Ref',
            '#default_value' => NULL,
          ],
        ],
        'actions' => [],
      ],
    ];

    $expected = [
      'references' => [
        '#type' => 'fieldset',
        '#title' => 'Related documents',
        '#description' => 'Improved description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'references' => [
          0 => [
            '#type' => 'textfield',
            '#title' => 'References',
            '#default_value' => 'Transportation',
            '#attributes' => [
              'placeholder' => 'http://',
            ],
          ],
          1 => [
            '#type' => 'textfield',
            '#title' => 'References',
            '#default_value' => NULL,
            '#attributes' => [
              'placeholder' => 'http://',
            ],
          ],
        ],
        'actions' => [],
      ],
    ];
    $this->assertEquals($ui_handler->applySchemaUi($form), $expected);

    // Test object field.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"publisher":{"properties":{"@type":{"ui:options":{"widget":"hidden"}},"name":{"ui:options":{"description":"Better description"}}}}}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      'publisher' => [
        'publisher' => [
          '#type' => 'details',
          "#open" => TRUE,
          '#title' => 'Object title',
          '@type' => [
            '#type' => 'textfield',
            '#title' => 'Metadata context',
            '#description' => 'Some description',
            '#default_value' => 'org:Organization',
            '#required' => FALSE,
          ],
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Publisher name',
            '#description' => 'Some description',
            '#default_value' => 'org:Organization',
            '#required' => TRUE,
          ],
        ],
      ]
    ];

    $expected = [
      'publisher' => [
        'publisher' => [
          '#type' => 'details',
          "#open" => TRUE,
          '#title' => 'Object title',
          '@type' => [
            '#type' => 'textfield',
            '#title' => 'Metadata context',
            '#description' => 'Some description',
            '#default_value' => 'org:Organization',
            '#required' => FALSE,
            '#access' => FALSE,
          ],
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Publisher name',
            '#description' => 'Better description',
            '#default_value' => 'org:Organization',
            '#required' => TRUE,
          ],
        ],
      ],
    ];
    $this->assertEquals($ui_handler->applySchemaUi($form), $expected);

    // Test array field with object.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"distribution":{"items":{"@type":{"ui:options":{"widget":"hidden"}}}}}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      'distribution' => [
        '#type' => 'fieldset',
        '#title' => 'Distribution',
        '#description' => 'Some description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'distribution' => [
          0 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ],
            ],
          ],
          1 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ]
            ],
          ],
        ],
        'actions' => [],
      ],
    ];

    $expected = [
      'distribution' => [
        '#type' => 'fieldset',
        '#title' => 'Distribution',
        '#description' => 'Some description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'distribution' => [
          0 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
                '#access' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ]
            ],
          ],
          1 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
                '#access' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ]
            ],
          ],
        ],
        'actions' => [],
      ],
    ];

    $this->assertEquals($ui_handler->applySchemaUi($form), $expected);

    // Test upload_or_link widget.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"downloadURL":{"ui:options":{"widget":"upload_or_link", "extensions": "jpg pdf png csv"}}}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $ui_handler = SchemaUiHandler::create($container);
    $ui_handler->setSchemaUi('dataset');
    $form = [
      'downloadURL' => [
        '#type' => 'string',
        '#title' => 'Download URL',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#default_value' => 'https://url.to.api.or.file',
      ],
    ];
    $expected = [
      'downloadURL' => [
        '#type' => 'upload_or_link',
        '#title' => 'Download URL',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#uri' => 'https://url.to.api.or.file',
        '#upload_location' => 'public://uploaded_resources',
        '#upload_validators' => [
          'file_validate_extensions' => ['jpg pdf png csv'],
        ],
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($form, $expected);
  }

}
