<?php
namespace Drupal\DKANExtension\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Drupal\DKANExtension\Hook\Scope\BeforeDKANEntityCreateScope;

use \stdClass;

/**
 * Defines application features from the specific context.
 */
class FastImportContext extends RawDKANContext {

  protected $old_global_user;
  public static $modules_before_feature = array();
  public static $users_before_feature = array();

  /**
   * @BeforeFeature @enableFastImport
   */
  public static function enableFastImport(BeforeFeatureScope $scope)
  {
    self::$modules_before_feature = module_list(TRUE);
    self::$users_before_feature = array_keys(entity_load('user'));
    define('MAINTENANCE_MODE', 'update');
    @module_enable(array(
      'dkan_datastore_fast_import',
    ));

    global $databases;
    $target = Database::getConnection()->getTarget();
    $key = Database::getConnection()->getKey();
    $database = $databases[$target][$key];

    $infile_enabled = array_key_exists(PDO::MYSQL_ATTR_LOCAL_INFILE, $database['pdo']) && $database['pdo'][PDO::MYSQL_ATTR_LOCAL_INFILE];
    if (!$infile_enabled) {
      $database['pdo'][PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
    }

    $buffered_query_enabled = array_key_exists(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $database['pdo']) && $database['pdo'][PDO::MYSQL_ATTR_USE_BUFFERED_QUERY];
    if (!$buffered_query_enabled) {
      $database['pdo'][PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = 1;
    }

    drupal_flush_all_caches();
  }

  /**
   * @AfterFeature @enableFastImport
   */
  public static function disableFastImport(AfterFeatureScope $event)
  {
    $modules_after_feature = module_list(TRUE);
    $users_after_feature = array_keys(entity_load('user'));

    $modules_to_disable = array_diff_assoc(
      $modules_after_feature,
      self::$modules_before_feature
    );

    $users_to_delete = array_diff_assoc(
      $users_after_feature,
      self::$users_before_feature
    );

    // Clean users and disable modules.
    entity_delete_multiple('user', $users_to_delete);
    module_disable(array_values($modules_to_disable));
    drupal_uninstall_modules(array_values($modules_to_disable));
    drupal_flush_all_caches();
  }
}
