<?php
/**
 * @file
 * Test transition emails for the dkan_workflow via dkan_workflow_mail_alter.
 *
 * The business logic we wish to capture:
 * Allow email send if the user is the origional author of the node.
 *
 * Only allow emails to "Worflow Moderator" that are in the same group as the
 * node.
 *
 * If the node does not belong to a group, then only allow emails to users
 * that belong to the "Workflow Supervisor" role.
 *
 * If the node belongs to group do not email with users "Workflow Supervisors".
 */

module_load_include('module', 'dkan_workflow');

/**
 * Unit tests for dkan_workflow_mail_alter.
 */
class DkanWorkflowMailAlterTest extends PHPUnit_Framework_TestCase {

  /**
   * Overrides parent::setUp().
   */
  public function setUp() {
    $uniqid = uniqid();
    $group = (object) [
      'title' => 'test-group',
      'type' => 'group',
      'status' => 1,
    ];
    node_save($group);
    $this->group = $group;

    $group2 = (object) [
      'title' => 'test-group 2',
      'type' => 'group',
      'status' => 1,
    ];
    node_save($group2);
    $this->group2 = $group2;

    $this->roles = array_flip(user_roles());
    $user = [
      'name' => 'test-user' . $uniqid,
      'pass' => 'fake',
      'mail' => $uniqid . '@gmail.com',
      'init' => $uniqid . '@gmail.com',
      'status' => 1,
      'roles' => array(),
    ];
    $this->user = user_save(NULL, $user);

    $node = (object) [
      'title' => 'test - dataset',
      'type' => 'dataset',
      'uid' => 1,
      'status' => '1',
    ];
    node_save($node);

    $message = [
      'id' => 'workbench_email_we_transition',
      'params' => [
        'node' => $node,
      ],
    ];
    $this->message = $message;
  }

  /**
   * Verify orgional node creater will get a pass.
   */
  public function testOrigUserOfNodePasses() {
    $this->message['to'] = $this->user->mail;
    $this->message['params']['node']->uid = $this->user->uid;
    dkan_workflow_mail_alter($this->message);

    $this->assertTrue($this->message['send']);
  }

  /**
   * Verify Workflow Moderator that belongs to same group as node gets a pass.
   */
  public function testWorkflowModeratorSameGroupPasses() {
    $workflow_moderator = $this->roles['Workflow Moderator'];
    $this->user->roles[$workflow_moderator] = $workflow_moderator;
    user_save($this->user);
    $this->message['to'] = $this->user->mail;
    og_group('node', $this->group->nid, array(
      'entity_type' => 'user',
      'entity' => $this->user,
    ));
    og_group('node', $this->group->nid, array(
      'entity_type' => 'node',
      'entity' => $this->message['params']['node'],
      'field_name' => 'og_group_ref',
    ));
    dkan_workflow_mail_alter($this->message);

    $this->assertTrue($this->message['send']);
  }

  /**
   * Verify Workflow Moderator that belongs to multiple groups can pass.
   *
   * Making sure that multiple groups does not change outcomes.
   */
  public function testWorkflowModeratorMultipleGroupsPasses() {
    $workflow_moderator = $this->roles['Workflow Moderator'];
    $this->user->roles[$workflow_moderator] = $workflow_moderator;
    user_save($this->user);
    $this->message['to'] = $this->user->mail;
    og_group('node', $this->group->nid, array(
      'entity_type' => 'user',
      'entity' => $this->user,
    ));
    og_group('node', $this->group2->nid, array(
      'entity_type' => 'user',
      'entity' => $this->user,
    ));
    og_group('node', $this->group->nid, array(
      'entity_type' => 'node',
      'entity' => $this->message['params']['node'],
      'field_name' => 'og_group_ref',
    ));
    dkan_workflow_mail_alter($this->message);

    $this->assertTrue($this->message['send']);
  }

  /**
   * Verify Workflow Moderator that does not share group with  node is stopped.
   */
  public function testWorkflowModeratorNoGroupFails() {
    $role = $this->roles['Workflow Moderator'];
    $this->user->roles[$role] = $role;
    user_save($this->user);
    $this->message['to'] = $this->user->mail;
    og_group('node', $this->group->nid, array(
      'entity_type' => 'user',
      'entity' => $this->user,
    ));
    dkan_workflow_mail_alter($this->message);

    $this->assertFalse($this->message['send']);
  }

  /**
   * Allow emails to "Worflow Supervisor" when node is groupless.
   */
  public function testWorkflowSupervisorGrouplessNode() {
    $role = $this->roles['Workflow Supervisor'];
    $this->user->roles[$role] = $role;
    user_save($this->user);
    $this->message['to'] = $this->user->mail;
    dkan_workflow_mail_alter($this->message);

    $this->assertTrue($this->message['send']);
  }

  /**
   * Disallow emails to "Workflow Supervisor" when node has group.
   */
  public function testWorkflowSupervisorGroupedNode() {
    $role = $this->roles['Workflow Supervisor'];
    $this->user->roles[$role] = $role;
    user_save($this->user);
    og_group('node', $this->group->nid, array(
      'entity_type' => 'node',
      'entity' => $this->message['params']['node'],
      'field_name' => 'og_group_ref',
    ));
    $this->message['to'] = $this->user->mail;
    dkan_workflow_mail_alter($this->message);

    $this->assertFalse($this->message['send']);
  }


  /**
   * Overrides parent::tearDown().
   */
  public function tearDown() {
    node_delete($this->group->nid);
    node_delete($this->group2->nid);
    node_delete($this->message['params']['node']->nid);
    user_delete($this->user->uid);
  }

}
