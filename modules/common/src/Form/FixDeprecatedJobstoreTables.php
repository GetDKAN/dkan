<?php

namespace Drupal\common\Form;

use Drupal\common\Util\JobStoreUtil;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for renaming deprecated jobstore tables.
 *
 * @internal
 */
class FixDeprecatedJobstoreTables extends ConfirmFormBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Creation with container injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   *
   * @return \Drupal\common\Form\FixDeprecatedJobstoreTables
   *   The form.
   */
  public static function create(ContainerInterface $container): self {
    $form = parent::create($container);
    $form->connection = $container->get('database');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dkan_common_fix_deprecated_jobstore_tables_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to rename deprecated jobstore tables?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('system.status');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Rename jobstore tables');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $job_store_util = new JobStoreUtil($this->connection);
    $deprecated_tables = $job_store_util->getAllTableNameChanges(
      $job_store_util->getAllDeprecatedJobstoreTableNames()
    );
    $display_tables = [];
    foreach ($deprecated_tables as $dep => $table) {
      $display_tables[] = $dep . ' => ' . $table;
    }
    return $this->t(
      'This action will rename the following jobstore database tables: @table_display. This will not affect tables for which there are both deprecated and non-deprecated table names.',
      ['@table_display' => implode(', ', $display_tables)]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $job_store_util = new JobStoreUtil($this->connection);
    $job_store_util->renameDeprecatedJobstoreTables();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
