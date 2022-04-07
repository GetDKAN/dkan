<?php

namespace Drupal\metastore_admin\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Psr\Container\ContainerInterface;

/**
 * An example action covering most of the possible options.
 *
 * If type is left empty, action will be selectable for all
 * entity types.
 *
 * @Action(
 *   id = "hide_current_revision_action",
 *   label = "Hide Current Revision",
 *   type = "node",
 *   confirm = TRUE,
 * )
 *
 * @codeCoverageIgnore
 */
class HideCurrentRevisionAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Private.
   */
  private $entity = NULL;

  /**
   * Constructor.
   *
   * @param array $config
   *   Details for reference definition. Possible keys:
   *   - schemaId: For some reference definitions, a schemaId must be specified.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param AccountInterface $currentUser
   *   Current user.
   */
  public function __construct(
    array $config,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    MessengerInterface $messenger,
    AccountInterface $currentUser
  ) {
    parent::__construct($config, $pluginId, $pluginDefinition);
    $this->loggerFactory = $loggerFactory->get('metastore_admin');
    $this->messenger = $messenger;
    $this->currentUser = $currentUser;
  }

  /**
   * Container injection.
   *
   * @param \Drupal\common\Plugin\ContainerInterface $container
   *   The service container.
   * @param array $config
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $config,
    $pluginId,
    $pluginDefinition
  ) {
    $loggerFactory = $container->get('logger.factory');
    $messenger = $container->get('messenger');
    $currentUser = $container->get('current_user');
    return new static($config, $pluginId, $pluginDefinition, $loggerFactory, $messenger, $currentUser);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    if ($entity && $this->currentUser->hasPermission('use dkan_publishing transition hidden')) {
      $this->loggerFactory->notice("Executing hide current revision of " . $entity->label());

      $this->hide($entity);

      // Check if published.
      if (!$entity->isPublished()) {
        $msg = "Something went wrong, the entity must be published by this point.  Review your content moderation configuration make sure you have the hidden state available and try again.";
        $this->messenger->addError(utf8_encode($msg));
        $this->loggerFactory->warning($msg);
        return $msg;
      }
      return sprintf('Example action (configuration: %s)', print_r($this->configuration, TRUE));
    }
    else {
      $this->messenger->addWarning($this->t("You don't have access to execute this operation!"));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    return TRUE;
  }

  /**
   * Hide (exclude from search) current revision.
   */
  public function hide($entity) {

    $entity->set('moderation_state', 'hidden');
    if ($entity instanceof RevisionLogInterface) {
      //$entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $msg = 'Bulk operation create hidden revision';
      $entity->setRevisionLogMessage($msg);
      $current_uid = $this->currentUser->id();
      $entity->setRevisionUserId($current_uid);
    }

    if ($this->currentUser->hasPermission('use dkan_publishing transition hidden')) {
      $entity->save();
    }
    else {
      $this->logger->notice(
        utf8_encode("Bulk hide not permitted, check permissions")
      );
    }

    return $entity;
  }

}
