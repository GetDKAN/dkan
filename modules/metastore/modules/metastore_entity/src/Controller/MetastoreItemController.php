<?php

namespace Drupal\metastore_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\metastore_entity\Entity\MetastoreItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MetastoreItemController.
 *
 *  Returns responses for Metastore item routes.
 */
class MetastoreItemController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Metastore item revision.
   *
   * @param int $metastore_item_revision
   *   The Metastore item revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($metastore_item_revision) {
    $metastore_item = $this->entityTypeManager()->getStorage('metastore_item')
      ->loadRevision($metastore_item_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('metastore_item');

    return $view_builder->view($metastore_item);
  }

  /**
   * Page title callback for a Metastore item revision.
   *
   * @param int $metastore_item_revision
   *   The Metastore item revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($metastore_item_revision) {
    $metastore_item = $this->entityTypeManager()->getStorage('metastore_item')
      ->loadRevision($metastore_item_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $metastore_item->label(),
      '%date' => $this->dateFormatter->format($metastore_item->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Metastore item.
   *
   * @param \Drupal\metastore_entity\Entity\MetastoreItemInterface $metastore_item
   *   A Metastore item object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(MetastoreItemInterface $metastore_item) {
    $account = $this->currentUser();
    $metastore_item_storage = $this->entityTypeManager()->getStorage('metastore_item');

    $langcode = $metastore_item->language()->getId();
    $langname = $metastore_item->language()->getName();
    $languages = $metastore_item->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $metastore_item->label()]) : $this->t('Revisions for %title', ['%title' => $metastore_item->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all metastore item revisions") || $account->hasPermission('administer metastore item entities')));
    $delete_permission = (($account->hasPermission("delete all metastore item revisions") || $account->hasPermission('administer metastore item entities')));

    $rows = [];

    $vids = $metastore_item_storage->revisionIds($metastore_item);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\metastore_entity\MetastoreItemInterface $revision */
      $revision = $metastore_item_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $metastore_item->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.metastore_item.revision', [
            'metastore_item' => $metastore_item->id(),
            'metastore_item_revision' => $vid,
          ]));
        }
        else {
          $link = $metastore_item->toLink($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.metastore_item.translation_revert', [
                'metastore_item' => $metastore_item->id(),
                'metastore_item_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.metastore_item.revision_revert', [
                'metastore_item' => $metastore_item->id(),
                'metastore_item_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.metastore_item.revision_delete', [
                'metastore_item' => $metastore_item->id(),
                'metastore_item_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['metastore_item_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
