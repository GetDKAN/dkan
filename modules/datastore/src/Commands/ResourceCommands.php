<?php

namespace Drupal\datastore\Commands;

use Drupal\common\Resource;
use Drupal\common\Util\DrupalFiles;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drush\Commands\DrushCommands;
use Procrastinator\Result;

/**
 * Datastore-related Drush commands.
 *
 * @codeCoverageIgnore
 */
class ResourceCommands extends DrushCommands {

  /**
   * Drupal files utility service.
   *
   * @var \Drupal\common\Util\DrupalFiles
   */
  private $drupalFiles;

  /**
   * PurgeCommands constructor.
   *
   * @param \Drupal\datastore\Service\ResourcePurger $resourcePurger
   *   The dkan.datastore.service.resource_localizer service.
   */
  public function __construct(
    ResourceMapper $resourceMapper,
    DrupalFiles $drupalFiles,
    ResourceLocalizer $resourceLocalizer
  ) {
    parent::__construct();
    $this->resourceMapper = $resourceMapper;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->drupalFiles = $drupalFiles;
  }

  /**
   * Create a resource for a file URI.
   *
   * @param array $options
   *   Options array.
   *
   * @option source Source
   * @option local-path Local path
   * @option mimeType MimeType, defaults to text/csv.
   *
   * @command dkan:resource:register
   */
  public function register(array $options = [
    'source' => '',
    'local-path' => '',
    'mimeType' => '',
  ]) {
    $mimeType = $options['mimeType'] ?: 'text/csv';

    $sourceUrl = $options['source'];
    $source = new Resource($sourceUrl, $mimeType, Resource::DEFAULT_SOURCE_PERSPECTIVE);

    try {
      $this->resourceMapper->register($source);
    }
    catch (AlreadyRegistered $e) {
      $this->logger()->notice("Already registered source perspective for $sourceUrl");
      $info = json_decode($e->getMessage());
      if (isset($info[0]->identifier)) {
        $source = $this->resourceMapper->get($info[0]->identifier, Resource::DEFAULT_SOURCE_PERSPECTIVE);
      }
    }

    if ($options['local-path']) {
      $localFilePath = $options['local-path'];
      $localFilePerspective = $source->createNewPerspective(ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $localFilePath);
      try {
        $this->resourceMapper->registerNewPerspective($localFilePerspective);
      }
      catch (AlreadyRegistered $e) {
        $this->logger()->notice("Already registered local perspective for $sourceUrl");
      }

      $dir = "file://" . $this->drupalFiles->getPublicFilesDirectory();
      $localFileDrupalUri = str_replace($dir, "public://", $localFilePath);
      $localUrl = $this->drupalFiles->fileCreateUrl($localFileDrupalUri);
      $localUrl = Referencer::hostify($localUrl);
      $localUrlPerspective = $source->createNewPerspective(ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $localUrl);
      try {
        $this->resourceMapper->registerNewPerspective($localUrlPerspective);
      }
      catch (AlreadyRegistered $e) {
      }
      $this->logger()->notice($source->getIdentifier() . '_' . $source->getVersion());

      $ff = $this->resourceLocalizer->getFileFetcher($source);
      $ff->getResult()->setStatus(Result::DONE);
      $result = $ff->run();
      return $result;
    }
  }

}
