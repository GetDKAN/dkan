<?php

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\datastore\DataNodeLifeCycle;
use Drupal\datastore\Service;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DataNodeLifeCycle2Test extends TestCase {

  /**
   *
   */
  public function initialSetUp(string $type, stdClass $data) {
    $options = (new Options())
      ->add("datastore.service", Service::class);
    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options);
    $container = $containerChain->getMock();

    $sequence = (new Sequence())
      ->add($type)
      ->add(json_encode($data));

    $entity = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->add(Node::class, 'uuid', '12345')
      ->add(Node::class, 'get', FieldItemList::class)
      ->add(FieldItemList::class, 'first', FieldItemInterface::class)
      ->add(FieldItemInterface::class, '__get', $sequence)
      ->getMock();

    return [$container, $entity];
  }

  /**
   *
   */
  public function testNonDistributionInsert() {
    $metadata = (object) ['identifier' => "12345"];
    [$container, $entity] = $this->initialSetUp('foobar', $metadata);

    \Drupal::setContainer($container);

    $cycle = new DataNodeLifeCycle($entity);
    $this->assertNull($cycle->insert());
  }

  /**
   *
   */
  public function testNonDistributionPreDelete() {
    $metadata = (object) ['identifier' => "12345"];
    [$container, $entity] = $this->initialSetUp('foobar', $metadata);

    \Drupal::setContainer($container);

    $cycle = new DataNodeLifeCycle($entity);
    $this->assertNull($cycle->predelete());
  }

  /**
   *
   */
  public function testDistributionWithoutDownloadURL() {
    $metadata = (object) [
      'identifier' => "12345",
      'data' => (object) [
        'accessURL' => "http://google.com",
      ],
    ];
    [$container, $entity] = $this->initialSetUp('distribution', $metadata);

    \Drupal::setContainer($container);

    $cycle = new DataNodeLifeCycle($entity);
    $this->assertNull($cycle->insert());
  }

  /**
   *
   */
  public function testDistributionWithDownloadURL() {
    $options = (new Options())
      ->add("datastore.service", Service::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', new \Exception("Invalid metadata information or missing file information."))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'log', NULL, 'log');

    $container = $containerChain->getMock();

    $metadata = (object) [
      'identifier' => "12345",
      'data' => (object) [
        'downloadURL' => "http://google.com",
        'mediaType' => "text/csv",
      ],
    ];

    $sequence = (new Sequence())
      ->add('distribution')
      ->add(json_encode($metadata));

    $entity = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->add(Node::class, 'uuid', '12345')
      ->add(Node::class, 'get', FieldItemList::class)
      ->add(FieldItemList::class, 'first', FieldItemInterface::class)
      ->add(FieldItemInterface::class, '__get', $sequence)
      ->getMock();

    \Drupal::setContainer($container);

    $cycle = new DataNodeLifeCycle($entity);
    $cycle->insert();

    $this->assertEquals('Invalid metadata information or missing file information.',
      $containerChain->getStoredInput('log')[1]);
  }

  /**
   *
   */
  public function testLifeCycle() {
    $options = (new Options())
      ->add('datastore.service', Service::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('file_system', FileSystemInterface::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', [], 'import')
      ->add(Service::class, 'drop', [], 'drop');
    $container = $containerChain->getMock();

    \Drupal::setContainer($container);

    $metadata = (object) [
      'identifier' => "12345",
      'data' => (object) [
        'downloadURL' => "http://google.com",
        'mediaType' => "text/csv",
      ],
    ];

    $options = (new Options())
      ->add('field_data_type', 'distribution')
      ->add('field_json_metadata', json_encode($metadata))
      ->use('field_get')
      ->index(1);

    $entity = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->add(Node::class, 'uuid', '12345')
      ->add(Node::class, 'get', FieldItemList::class, 'field_get')
      ->add(FieldItemList::class, 'first', FieldItemInterface::class)
      ->add(FieldItemInterface::class, '__get', $options)
      ->getMock();

    $cycle = new DataNodeLifeCycle($entity);
    $cycle->insert();
    // The right info was given to the datastore service to queue for import.
    $this->assertEquals(['12345', TRUE], $containerChain->getStoredInput('import'));

    $cycle->predelete();
    // The right info was given to the datastore service to drop the datastore.
    $this->assertEquals(['12345'], $containerChain->getStoredInput('drop'));
  }

}
