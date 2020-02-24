<?php

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\dkan_datastore\DataNodeLifeCycle;
use Drupal\dkan_datastore\Service;
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
   * Not having a download URL should not stop anything.
   */
  public function testNoDownloadURL() {
    $options = (new Options())
      ->add("dkan_datastore.service", Service::class)
      ->add('logger.factory', LoggerChannelFactory::class);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', new \Exception("Invalid metadata information or missing file information."))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'error', NULL, 'log');

    $container = $containerChain->getMock();

    $metadata = (object) [
      'identifier' => "12345",
      'data' => (object) [
        'accessURL' => "http://google.com",
        'mediaType' => "text/csv",
      ],
    ];

    $sequence = (new Sequence())
      ->add('distribution')
      ->add(json_encode($metadata));

    $entity = (new Chain($this))
      ->add(Node::class, 'bundle', 'data')
      ->add(Node::class, 'get', FieldItemList::class)
      ->add(Node::class, 'uuid', '12345')
      ->add(FieldItemList::class, 'first', FieldItemInterface::class)
      ->add(FieldItemInterface::class, '__get', $sequence)
      ->getMock();

    \Drupal::setContainer($container);

    $cycle = new DataNodeLifeCycle($entity);
    $cycle->insert();

    $this->assertEquals('Invalid metadata information or missing file information.',
      $containerChain->getStoredInput('log')[0]);
  }

}
