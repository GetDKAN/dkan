<?php

namespace Drupal\harvest\Entity;

use Drupal\common\Entity\DkanDatabaseTableEntityBase;
use Drupal\harvest\HarvestPlanInterface;

/**
 * Defines the harvest plan entity class.
 *
 * @ContentEntityType(
 *   id = "harvest_plan",
 *   label = @Translation("Harvest Plan"),
 *   label_collection = @Translation("Harvest Plans"),
 *   label_singular = @Translation("harvest plan"),
 *   label_plural = @Translation("harvest plans"),
 *   label_count = @PluralTranslation(
 *     singular = "@count harvest plans",
 *     plural = "@count harvest plans",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\harvest\HarvestPlanListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\harvest\Form\HarvestPlanForm",
 *       "edit" = "Drupal\harvest\Form\HarvestPlanForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\harvest\Routing\HarvestPlanHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "harvest_plans",
 *   admin_permission = "administer harvest plan",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   links = {
 *     "collection" = "/admin/content/harvest-plan",
 *     "add-form" = "/harvest-plan/add",
 *     "canonical" = "/harvest-plan/{harvest_plan}",
 *     "edit-form" = "/harvest-plan/{harvest_plan}",
 *     "delete-form" = "/harvest-plan/{harvest_plan}/delete",
 *   },
 * )
 */
class HarvestPlan extends DkanDatabaseTableEntityBase implements HarvestPlanInterface {

}
