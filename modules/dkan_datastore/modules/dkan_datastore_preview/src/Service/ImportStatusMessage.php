<?php

namespace Drupal\dkan_datastore_preview\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_datastore\Service\Info\ImportInfo;
use Drupal\dkan_metastore\ResourceMapper;
use Procrastinator\Result;

/**
 * Builds user-facing messages for previews whose data is not queryable yet.
 *
 * A dataset page can exist before its datastore import has run (imports are
 * processed by the localize_import and datastore_import queues), so instead
 * of rendering nothing the preview explains the state.
 */
class ImportStatusMessage {

  use StringTranslationTrait;

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_datastore\Service\Info\ImportInfo $importInfo
   *   The DKAN datastore import info service.
   * @param \Drupal\dkan_metastore\ResourceMapper $resourceMapper
   *   The DKAN resource mapper.
   */
  public function __construct(
    protected ImportInfo $importInfo,
    protected ResourceMapper $resourceMapper,
  ) {}

  /**
   * Build a status message render array for a resource without a table.
   *
   * @param string $resource_id
   *   Resource id in "identifier__version" format.
   *
   * @return array
   *   Render array with a message describing the import state.
   */
  public function build(string $resource_id): array {
    return [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->getMessage($resource_id),
      '#attributes' => ['class' => ['dkan-datastore-preview__message']],
    ];
  }

  /**
   * Resolve the message for the resource's current import state.
   *
   * Only a resource that is known to the mapper and whose fetch/import is
   * waiting or running is described as "processing"; anything else (unknown
   * resource, stopped or finished-but-missing import) gets the generic
   * message so the page never promises data that will not arrive.
   */
  protected function getMessage(string $resource_id): string {
    $generic = (string) $this->t('Data preview is not yet available.');
    try {
      if (!str_contains($resource_id, '__')) {
        return $generic;
      }
      [$identifier, $version] = explode('__', $resource_id, 2);
      if (!$this->resourceMapper->get($identifier, DataResource::DEFAULT_SOURCE_PERSPECTIVE, $version)) {
        return $generic;
      }

      $item = $this->importInfo->getItem($identifier, $version);
      $statuses = [
        $item->fileFetcherStatus ?? NULL,
        $item->importerStatus ?? NULL,
      ];
      if (in_array(Result::ERROR, $statuses, TRUE)) {
        return (string) $this->t('A data preview could not be generated for this distribution.');
      }
      $processing = [Result::WAITING, Result::IN_PROGRESS];
      if (in_array($statuses[0], $processing, TRUE) || in_array($statuses[1], $processing, TRUE)) {
        return (string) $this->t('Data preview is not yet available. The data for this distribution is still being processed.');
      }
      return $generic;
    }
    catch (\Throwable) {
      return $generic;
    }
  }

}
