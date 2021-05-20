<?php

namespace Drupal\metastore_entity\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Metastore item revision.
 *
 * @ingroup metastore_entity
 */
class MetastoreItemRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Metastore item revision.
   *
   * @var \Drupal\metastore_entity\Entity\MetastoreItemEntityInterface
   */
  protected $revision;

  /**
   * The Metastore item storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $metastoreItemStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->metastoreItemStorage = $container->get('entity_type.manager')->getStorage('metastore_item');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metastore_item_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.metastore_item.version_history', ['metastore_item' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $metastore_item_revision = NULL) {
    $this->revision = $this->MetastoreItemStorage->loadRevision($metastore_item_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->MetastoreItemStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Metastore item: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Metastore item %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.metastore_item.canonical',
       ['metastore_item' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {metastore_item_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.metastore_item.version_history',
         ['metastore_item' => $this->revision->id()]
      );
    }
  }

}
