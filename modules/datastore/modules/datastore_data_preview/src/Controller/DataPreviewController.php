<?php

namespace Drupal\datastore_data_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\datastore_data_preview\Service\ResourceIdResolver;
use Drupal\metastore\MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for standalone data preview pages.
 */
class DataPreviewController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    protected ResourceIdResolver $resourceResolver,
    protected MetastoreService $metastore,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('dkan.data_preview.resource_resolver'),
      $container->get('dkan.metastore.service'),
    );
  }

  /**
   * Renders the data preview page.
   *
   * @param string $resource_id
   *   Either "{identifier}__{version}" or a distribution UUID.
   *
   * @return array
   *   Render array.
   */
  public function preview(string $resource_id): array {
    $resolved = $this->resolveResourceId($resource_id);
    if (!$resolved) {
      throw new NotFoundHttpException();
    }

    return [
      '#type' => 'data_preview',
      '#resource_id' => $resolved,
    ];
  }

  /**
   * Title callback for the preview page.
   *
   * @param string $resource_id
   *   Either "{identifier}__{version}" or a distribution UUID.
   *
   * @return string
   *   Page title.
   */
  public function title(string $resource_id): string {
    $resolved = $this->resolveResourceId($resource_id);
    if (!$resolved) {
      return $this->t('Data Preview');
    }

    // Try to get a filename from the distribution metadata.
    if (!str_contains($resource_id, '__')) {
      try {
        $json = $this->metastore->get('distribution', $resource_id);
        $metadata = json_decode($json);
        $title = $metadata->data->title ?? NULL;
        if ($title) {
          return $this->t('Data Preview: @title', ['@title' => $title]);
        }
      }
      catch (\Exception $e) {
        // Fall through to default.
      }
    }

    return $this->t('Data Preview');
  }

  /**
   * Resolves a resource ID, accepting UUID or identifier__version format.
   *
   * @param string $resource_id
   *   The resource identifier.
   *
   * @return string|null
   *   The resolved "{identifier}__{version}" string, or NULL.
   */
  protected function resolveResourceId(string $resource_id): ?string {
    return $this->resourceResolver->resolve($resource_id);
  }

}
