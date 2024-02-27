<?php declare(strict_types = 1);

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\harvest\HarvestRunInterface;

/**
 * Defines the harvest run entity class.
 *
 * @ContentEntityType(
 *   id = "harvest_run",
 *   label = @Translation("Harvest Run"),
 *   label_collection = @Translation("Harvest Runs"),
 *   label_singular = @Translation("harvest run"),
 *   label_plural = @Translation("harvest runs"),
 *   label_count = @PluralTranslation(
 *     singular = "@count harvest runs",
 *     plural = "@count harvest runs",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\harvest\HarvestRunListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\harvest\Form\HarvestRunForm",
 *       "edit" = "Drupal\harvest\Form\HarvestRunForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\harvest\Routing\HarvestRunHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "harvest_runs",
 *   admin_permission = "administer harvest_run",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/run",
 *     "add-form" = "/harvest-run/add",
 *     "canonical" = "/harvest-run/{harvest_run}",
 *     "edit-form" = "/harvest-run/{harvest_run}",
 *     "delete-form" = "/harvest-run/{harvest_run}/delete",
 *     "delete-multiple-form" = "/admin/content/run/delete-multiple",
 *   },
 * )
 */
final class HarvestRun extends ContentEntityBase implements HarvestRunInterface {

}
