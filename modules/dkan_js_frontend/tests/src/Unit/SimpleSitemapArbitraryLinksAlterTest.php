<?php

namespace Drupal\Tests\dkan_js_frontend\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;

use Drupal\dkan_js_frontend\Routing\RouteProvider;
use Drupal\metastore\MetastoreService;

use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

$module_path = substr(__DIR__, 0, strpos(__DIR__, '/dkan_js_frontend/')) . '/dkan_js_frontend';
require_once $module_path . '/dkan_js_frontend.module';

/**
 * Test dkan_js_frontend_simple_sitemap_arbitrary_links_alter() function.
 */
class SimpleSitemapArbitraryLinksAlterTest extends TestCase {

  /**
   * Base URL to use for testing sitemap URL generation.
   *
   * @var string
   */
  protected const BASE_URL = 'https://example.com';

  /**
   * Test sitemap generation of static links.
   */
  public function testSitemapStaticLinks(): void {
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['home,/home', 'about,/about'])
      ->getMock();
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan_js_frontend.route_provider', new RouteProvider($configFactory))
      ->add('entity_type.repository', EntityTypeRepository::class)
      ->add('entity_type.manager', EntityTypeManagerInterface::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('simple_sitemap.settings', SimpleSitemapSettingsInterface::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $containerOptions)
      ->add(RequestStack::class, 'getCurrentRequest', (Request::create(self::BASE_URL)))
      ->add(SimpleSitemapSettingsInterface::class, 'get', NULL)
      ->getMock();
    \Drupal::setContainer($container);

    $arbitrary_links = [];
    $simpleSitemap = (new Chain($this))
      ->add(ConfigEntityInterface::class, 'id', 'default')
      ->getMock();
    dkan_js_frontend_simple_sitemap_arbitrary_links_alter($arbitrary_links, $simpleSitemap);

    $this->assertEquals($arbitrary_links, [
      DKAN_JS_FRONTEND_DEFAULT_STATIC_LINK + ['url' => self::BASE_URL . '/home'],
      DKAN_JS_FRONTEND_DEFAULT_STATIC_LINK + ['url' => self::BASE_URL . '/about'],
    ]);
  }

  /**
   * Test sitemap generation of dataset links.
   */
  public function testSitemapDatasetLinks(): void {
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['dataset,/dataset/{id}'])
      ->getMock();
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan_js_frontend.route_provider', new RouteProvider($configFactory))
      ->add('entity_type.repository', EntityTypeRepository::class)
      ->add('entity_type.manager', EntityTypeManagerInterface::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('request_stack', RequestStack::class)
      ->add('simple_sitemap.settings', SimpleSitemapSettingsInterface::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $containerOptions)
      ->add(RequestStack::class, 'getCurrentRequest', (Request::create(self::BASE_URL)))
      ->add(MetastoreService::class, 'getIdentifiers', [1, 2])
      ->add(SimpleSitemapSettingsInterface::class, 'get', NULL)
      ->getMock();
    \Drupal::setContainer($container);

    $arbitrary_links = [];
    $simpleSitemap = (new Chain($this))
      ->add(ConfigEntityInterface::class, 'id', 'default')
      ->getMock();
    dkan_js_frontend_simple_sitemap_arbitrary_links_alter($arbitrary_links, $simpleSitemap);

    $this->assertEquals($arbitrary_links, [
      DKAN_JS_FRONTEND_DEFAULT_DATASET_LINK + ['url' => self::BASE_URL . '/dataset/1'],
      DKAN_JS_FRONTEND_DEFAULT_DATASET_LINK + ['url' => self::BASE_URL . '/dataset/2'],
    ]);
  }

  /**
   * Test sitemap error when a dataset route is not found.
   */
  public function testSitemapErrorNoDatasetRouteFound(): void {
    $configFactory = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', [])
      ->getMock();
    $containerOptions = (new Options())
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan_js_frontend.route_provider', new RouteProvider($configFactory))
      ->add('entity_type.repository', EntityTypeRepository::class)
      ->add('entity_type.manager', EntityTypeManagerInterface::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('request_stack', RequestStack::class)
      ->add('simple_sitemap.settings', SimpleSitemapSettingsInterface::class)
      ->index(0);
    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $containerOptions)
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'error')
      ->add(RequestStack::class, 'getCurrentRequest', (Request::create(self::BASE_URL)))
      ->add(SimpleSitemapSettingsInterface::class, 'get', NULL);
    \Drupal::setContainer($containerChain->getMock());

    $arbitrary_links = [];
    $simpleSitemap = (new Chain($this))
      ->add(ConfigEntityInterface::class, 'id', 'default')
      ->getMock();
    dkan_js_frontend_simple_sitemap_arbitrary_links_alter($arbitrary_links, $simpleSitemap);

    $this->assertEquals($containerChain->getStoredInput('error')[0], DKAN_JS_FRONTEND_MISSING_DATASET_ROUTE_ERROR);
  }

  /**
   * Ensure the $arbitrary_links array is not modified for non-default sitemaps.
   */
  public function testArbitraryLinksNotModifiedForNonDefaultSitemap(): void {
    $arbitrary_links = [];
    $simpleSitemap = (new Chain($this))
      ->add(ConfigEntityInterface::class, 'id', 'test')
      ->getMock();
    dkan_js_frontend_simple_sitemap_arbitrary_links_alter($arbitrary_links, $simpleSitemap);

    $this->assertEmpty($arbitrary_links);
  }

}
