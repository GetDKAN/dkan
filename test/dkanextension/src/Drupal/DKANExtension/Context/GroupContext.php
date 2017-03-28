<?php
namespace Drupal\DKANExtension\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use EntityFieldQuery;
use \stdClass;

/**
 * Defines application features from the specific context.
 */
class GroupContext extends RawDKANEntityContext {
  /** @var  \Drupal\DKANExtension\Context\DKANContext */
  protected $dkanContext;

  public function __construct() {
    parent::__construct(
      'node',
      'group'
    );
  }

  /**
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope){
    parent::gatherContexts($scope);
    $environment = $scope->getEnvironment();
    $this->dkanContext = $environment->getContext('Drupal\DKANExtension\Context\DKANContext');
  }

  /**
   * Creates OG Groups from a table.
   *
   * @Given groups:
   */
  public function addGroups(TableNode $groupsTable) {
    parent::addMultipleFromTable($groupsTable);
  }

  /**
   * Creates multiple group memberships.
   *
   * Provide group membership data in the following format:
   *
   * | user  | group     | role on group        | membership status |
   * | Foo   | The Group | administrator member | Active            |
   *
   * @Given group memberships:
   */
  public function addGroupMemberships(TableNode $groupMembershipsTable)
  {
    foreach ($groupMembershipsTable->getHash() as $groupMembershipHash) {
      if (isset($groupMembershipHash['group']) && isset($groupMembershipHash['user'])) {
        $group = $this->getGroupByName($groupMembershipHash['group']);
        $user = user_load_by_name($groupMembershipHash['user']);
        // Add user to group with the proper group permissions and status
        if ($group && $user) {
          // Add the user to the group
          og_group("node", $group->nid->value(), array(
            "entity type" => "user",
            "entity" => $user,
            "membership type" => OG_MEMBERSHIP_TYPE_DEFAULT,
            "state" => $this->getMembershipStatusByName($groupMembershipHash['membership status'])
          ));
          // Grant user roles
          $group_role = $this->getGroupRoleByName($groupMembershipHash['role on group']);
          og_role_grant("node", $group->nid->value(), $user->uid, $group_role);
        } else {
          if (!$group) {
            throw new \Exception(sprintf("No group was found with name %s.", $groupMembershipHash['group']));
          }
          if (!$user) {
            throw new \Exception(sprintf("No user was found with name %s.", $groupMembershipHash['user']));
          }
        }
      } else {
        throw new \Exception(sprintf("The group and user information is required."));
      }
    }
  }

  /**
   * Grants the given role to the current user, for the given group.
   *
   * @Given /^I am a "([^"]*)" of the group "([^"]*)"$/
   */
  public function iAmAMemberOfTheGroup($role, $group_name) {
    // Get group
    $group = $this->getGroupByName($group_name);

    $role = $this->getGroupRoleByName($role);

    $account = $this->dkanContext->getCurrentUser();

    if (isset($account)) {
      og_group('node', $group->getIdentifier(), array(
          "entity type" => "user",
          "entity" => $account,
          "membership type" => OG_MEMBERSHIP_TYPE_DEFAULT,
      ));
      og_role_grant('node', $group->getIdentifier(), $account->uid, $role);
    }
    else {
      throw new \InvalidArgumentException(sprintf('Could not find current user'));
    }

  }

  /**
   * @Then I should see the list of permissions for the group
   */
  public function iShouldSeePermissionsForTheGroup(){
    $permissions = og_get_permissions();

    foreach($permissions as $machine_name => $perm) {
      $this->dkanContext->getMink()->assertPageContainsText(strip_tags($perm['title']));
    }
  }

  /**
   * @Then I should see the list of roles for the group :group
   */
  public function iShouldSeeRolesForGroup($group){
    $group = $this->getGroupByName($group);
    $roles = og_roles('node', 'group', $group->getIdentifier());

    foreach($roles as $machine_name => $role) {
      $this->dkanContext->getMink()->assertPageContainsText(strip_tags($role));
    }
  }

  /**
   * Get Group by name
   *
   * First looks inside this context's array of wrapped entities,
   * and if not found checks the site's database.
   *
   * @param $name - title of the group
   * @return EntityMetadataWrapper group or FALSE
   */
  public function getGroupByName($name) {
    if ($found_group = $this->entityStore->retrieve_by_name($name)) {
      return $found_group;
    }

    // In case the group was not created by 'Given groups',
    //  such as being created manually by form interaction,
    //  we fetch the group using entity_load
    $gids = og_get_all_group("node");
    foreach($gids as $gid){
      $groups = entity_load($this->entity_type, array($gid));
      foreach($groups as $group) {
        if ($group->title == $name) {
          return entity_metadata_wrapper('node', $group);
        }
      }
    }
    return FALSE;
  }
  /**
   * Get Group Role ID by name
   *
   * @param $name - title of the role
   * @return stdClass Role ID or FALSE
   */
  private function getGroupRoleByName($name) {
    $group_roles = og_get_user_roles_name();
    return array_search($name, $group_roles);
  }
  /**
   * Get Membership Status Code by name
   *
   * @param $name - name of the mapped status
   * @return group Membership constant status code or FALSE
   */
  private function getMembershipStatusByName($name) {
    switch($name) {
      case 'Active':
        return OG_STATE_ACTIVE;
        break;
      case 'Pending':
        return OG_STATE_PENDING;
        break;
      case 'Blocked':
        return OG_STATE_BLOCKED;
        break;
      default:
        break;
    }
    return FALSE;
  }
}
