<?php

namespace Drupal\metastore_admin\Plugin\Action;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a hide action to exclude an entity from search results.
 *
 * If type is left empty, action will be selectable for all
 * entity types.
 *
 * @Action(
 *   id = "hide_current_revision_action",
 *   label =  @Translation("Hide Current Revision"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 */
class HideCurrentRevisionAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Private.
   *
   * @var entity
   */
  private $entity = NULL;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Details for reference definition. Possible keys:
   *   - schemaId: For some reference definitions, a schemaId must be specified.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current user.
   * @param \Drupal\Component\Datetime\TimeInterface $timeInterface
   *   Time.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    MessengerInterface $messenger,
    AccountInterface $currentUser,
    TimeInterface $timeInterface
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->logger = $loggerFactory->get('metastore_admin');
    $this->messenger = $messenger;
    $this->currentUser = $currentUser;
    $this->timeInterface = $timeInterface;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('logger.factory'),
      $container->get('messenger'),
      $container->get('current_user'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    if ($entity && $this->access($entity)) {
      $this->logger->notice("Executing hide current revision of " . $entity->label());
      $this->hide($entity);
    }
    else {
      $this->messenger->addWarning($this->t("You don't have access to execute this operation!"));
      return;
    }
  }

  /**
   * {@inheritdoc}
   *
   * Drupal\Core\Action\ActionInterface::access.
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($account === NULL) {
      $account = $this->currentUser;
    }
    if (!$account->hasPermission('use dkan_publishing transition hidden')) {
      return FALSE;
    }
    $access = $object->access('update', $account, TRUE);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * Hide (exclude from search) current revision.
   */
  public function hide($entity) {

    $entity->set('moderation_state', 'hidden');
    if ($entity instanceof RevisionLogInterface) {
      $entity->setRevisionCreationTime($this->timeInterface->getRequestTime());
      $msg = 'Bulk operation create hidden revision';
      $entity->setRevisionLogMessage($msg);
      $current_uid = $this->currentUser->id();
      $entity->setRevisionUserId($current_uid);
    }
    $entity->save();

    return $entity;
  }

}
