<?php

namespace Drupal\json_form_widget\Tests\Kernel\Plugin\JsonFormOptionSource;

use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Tests the TaxonomySource plugin.
 *
 * @group json_form_widget
 * @coversDefaultClass \Drupal\json_form_widget\Plugin\JsonFormOptionSource\TaxonomySource
 */
class TaxonomySourceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'json_form_widget',
    'taxonomy',
    'user',
    'system',
    'field',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('taxonomy_vocabulary');
    $this->installEntitySchema('user');
    $this->installConfig(['taxonomy', 'json_form_widget']);

    // Create a vocabulary for testing.
    $vocabulary = Vocabulary::create([
      'vid' => 'test',
      'description' => 'Test vocabulary',
      'name' => 'Test',
    ]);
    $vocabulary->save();
    // Add some terms to the vocabulary.
    $terms = ['Term 1', 'Term 2', 'Term 3'];
    foreach ($terms as $term_name) {
      $term = Term::create([
        'name' => $term_name,
        'vid' => 'test',
      ]);
      $term->save();
    }
  }

  public function testGetOptions() {
    $plugin_manager = \Drupal::service('plugin.manager.json_form_option_source');
    $plugin = $plugin_manager->createInstance('taxonomy', ['vocabulary' => 'test']);
    $options = $plugin->getOptions(['vocabulary' => 'test']);

    // Verify that the options match the terms we created.
    $expected_options = [
      'Term 1' => 'Term 1',
      'Term 2' => 'Term 2',
      'Term 3' => 'Term 3',
    ];
    $this->assertEquals($expected_options, $options);
  }
}