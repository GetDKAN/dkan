<?php

namespace Drupal\Tests\metastore_admin\Functional\Plugin\Action;

use Drupal\Core\Session\AccountProxy;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\metastore_admin\Plugin\Action\HideCurrentRevisionAction;
use Drupal\Tests\common\Traits\CleanUp;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
use Drupal\user\Entity\User;
use RootedData\RootedJsonData;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * NodeData Functional Tests.
 *
 * @package Drupal\Tests\dkan\Functional
 * @group dkan
 */
class HideCurrentRevisionActionTest extends ExistingSiteBase {
  use CleanUp;
  use UserCreationTrait;

  protected ValidMetadataFactory $validMetadataFactory;
  protected User $testUser;
  protected User $testApiUser;

  private const S3_PREFIX = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit';

  public function setUp(): void {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
    $this->setDefaultModerationState("published");

    $this->testUser = $this->createUser([], "testadmin", TRUE, ['mail' => 'testadmin@test.com']);
    $this->testApiUser = $this->createUser([], "testapiuser", FALSE, ['roles' => ['api_user'], 'mail' => 'testapiuser@test.com']);

    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);
  }

  /**
   * Test resource removal on distribution deleting.
   */
  public function testHide() {
    $datasetRootedJsonData = $this->getData("456", 'Test Published', []);
    $this->getMetastore()->post('dataset', $datasetRootedJsonData);
    $result = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => '456']);
    $node = current($result);
    $container = \Drupal::getContainer();

    $accountProxy = new AccountProxy($container->get('event_dispatcher'));
    $accountProxy->setAccount($this->testUser);
    $container->set('current_user', $accountProxy);

    $hideCurrentRevisionAction = HideCurrentRevisionAction::create(
      $container,
      [],
      "hide_current_revision_action",
      [
        'type' => 'node',
        'class' => HideCurrentRevisionAction::class,
        'confirm' => TRUE,
        'provider' => 'metastore_admin',
      ]
    );

    $this->assertEquals('published', $node->get('moderation_state')->getString());

    $hideCurrentRevisionAction->execute($node);

    $this->assertEquals('hidden', $node->get('moderation_state')->getString());

    $this->setDefaultModerationState("draft");
    $datasetRootedJsonData = $this->getData("457", 'Test Unpublished', []);
    $this->getMetastore()->post('dataset', $datasetRootedJsonData);
    $result = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => '457']);
    $node = current($result);
    $this->assertEquals('draft', $node->get('moderation_state')->getString());
    $hideCurrentRevisionAction->execute($node);
    $this->assertEquals('hidden', $node->get('moderation_state')->getString());

    $this->setDefaultModerationState("published");
    $datasetRootedJsonData = $this->getData("458", 'Test Published no access', []);
    $this->getMetastore()->post('dataset', $datasetRootedJsonData);
    $result = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => '458']);
    $node = current($result);

    $accountProxy = new AccountProxy($container->get('event_dispatcher'));
    $accountProxy->setAccount($this->testApiUser);
    $container->set('current_user', $accountProxy);
    $hideCurrentRevisionAction = HideCurrentRevisionAction::create(
      $container,
      [],
      "hide_current_revision_action",
      [
        'type' => 'node',
        'class' => HideCurrentRevisionAction::class,
        'confirm' => TRUE,
        'provider' => 'metastore_admin',
      ]
    );

    $this->assertEquals('published', $node->get('moderation_state')->getString());
    $hideCurrentRevisionAction->execute($node);
    $this->assertEquals('published', $node->get('moderation_state')->getString());

  }

  /**
   * Generate dataset metadata, possibly with multiple distributions.
   *
   * @param string $identifier
   *   Dataset identifier.
   * @param string $title
   *   Dataset title.
   * @param array $downloadUrls
   *   Array of resource files URLs for this dataset.
   *
   * @return \RootedData\RootedJsonData
   *   Json encoded string of this dataset's metadata, or FALSE if error.
   */
  private function getData(string $identifier, string $title, array $downloadUrls): RootedJsonData {

    $data = new \stdClass();
    $data->title = $title;
    $data->description = "Some description.";
    $data->identifier = $identifier;
    $data->accessLevel = "public";
    $data->modified = "06-04-2020";
    $data->keyword = ["some keyword"];
    $data->distribution = [];

    foreach ($downloadUrls as $key => $downloadUrl) {
      $distribution = new \stdClass();
      $distribution->title = "Distribution #{$key} for {$identifier}";
      $distribution->downloadURL = $this->getDownloadUrl($downloadUrl);
      $distribution->mediaType = "text/csv";

      $data->distribution[] = $distribution;
    }

    return $this->validMetadataFactory->get(json_encode($data), 'dataset');
  }

  private function getMetastore(): MetastoreService {
    return \Drupal::service('dkan.metastore.service');
  }

  private function setDefaultModerationState($state = 'published') {
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config = \Drupal::service('config.factory');
    $defaultModerationState = $config->getEditable('workflows.workflow.dkan_publishing');
    $defaultModerationState->set('type_settings.default_moderation_state', $state);
    $defaultModerationState->save();
  }

  private function getDownloadUrl(string $filename) {
    return self::S3_PREFIX . '/' . $filename;
  }

}
