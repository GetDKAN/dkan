<?php
namespace Drupal\metastore_admin;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\moderated_content_bulk_publish\AdminHelper;

/**
 * A Helper Class to assist with the publishing, archiving and unpublishing bulk action.
 *   - Called by Publish Latest Revision, Archive Latest Revision and Unpublish Current Revision Bulk Operations
 *   - Easy one-stop shop to make modifications to these bulk actions.
 */
class MetastoreAdminModeration
{
    //set this to true to send to $testEmailList
    private $testMode = false;
    private $entity = null;
    private $id = 0;
    private $status = 0; // Default is 0, unpublish.

    public function __construct($entity, $status)
    {
      $this->entity = $entity;
      if (!is_null($status)) {
        $this->status = $status;
      }
      $this->id = $this->entity->id();
    }

    /**
     * Hide (exclude from search) current revision.
     */
    public function hide() {
      $user = \Drupal::currentUser();
      $currentLang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $allLanguages = AdminHelper::getAllEnabledLanguages();
      foreach ($allLanguages as $langcode => $languageName) {
        if ($this->entity->hasTranslation($langcode)) {
          \Drupal::logger('moderated_content_bulk_publish')->notice(
            utf8_encode("Hide $langcode for " . $this->id . " in moderated_content_bulk_publish")
          );
          $this->entity = $this->entity->getTranslation($langcode);
          $this->entity->set('moderation_state', 'hidden');
          if ($this->entity instanceof RevisionLogInterface) {
            // $now = time();
            $this->entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
            $msg = 'Bulk operation create hidden revision';
            $this->entity->setRevisionLogMessage($msg);
            $current_uid = \Drupal::currentUser()->id();
            $this->entity->setRevisionUserId($current_uid);
          }
          // $this->entity->setSyncing(TRUE);  Removing and using shutdown call to complete save of alt lang.

          $this->entity->setRevisionTranslationAffected(TRUE);
          if ($user->hasPermission('use dkan_publishing transition hidden')) {
            if($langcode == $currentLang) {
              $this->entity->save();
            }
            else {
              drupal_register_shutdown_function('Drupal\moderated_content_bulk_publish\AdminHelper::bulkPublishShutdown', $this->entity, $langcode, 'hidden');
            }
          }
          else {
            \Drupal::logger('moderated_content_bulk_publish')->notice(
              utf8_encode("Bulk hide not permitted, check permissions")
            );
          }
        }
      }
      return $this->entity;
    }

}
