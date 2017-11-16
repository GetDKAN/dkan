# time:5m51.88s
@api @enableDKAN_Workflow @disablecaptcha
Feature:
  Workflow (Workbench) tests for DKAN Workflow Module

  Background:
    Given pages:
      | name               | url                                  |
      | My Workbench       | /admin/workbench                     |
      | My Content         | /admin/workbench                     |
      | My Drafts          | /admin/workbench/drafts-active       |
      | Needs Review       | /admin/workbench/needs-review-active |
      | Stale Drafts       | /admin/workbench/drafts-stale        |
      | Stale Reviews      | /admin/workbench/needs-review-stale  |
      | My Edits           | /admin/workbench/content/edited      |
      | All Recent Content | /admin/workbench/content/all         |
      | Create User        | /admin/people/create                 |
    Given Users:
      | name        | mail             | status | roles                                 |
      | Contributor | WC@fakeemail.com | 1      | Workflow Contributor, Content Creator |
      | Moderator   | WM@fakeemail.com | 1      | Workflow Moderator, Editor            |
      | Supervisor  | WS@fakeemail.com | 1      | Workflow Supervisor, Site Manager     |
    Given groups:
      | title    | author     | published |
      | Group 01 | Supervisor | Yes       |
      | Group 02 | Supervisor | Yes       |
      | Group 03 | Supervisor | No        |
    And group memberships:
      | user       | group    | role on group        | membership status |
      | Supervisor | Group 01 | administrator member | Active            |
      | Moderator  | Group 01 | member               | Active            |
      | Contributor| Group 01 | member               | Active            |
      | Moderator  | Group 02 | member               | Active            |

  #Non workbench roles can see the menu item My Workflow. However
  #they can't access to the page.
  @workflow_01 @globalUser
  Scenario Outline: As a user without a Workbench role, I should not be able to access My Workbench or the My Workbench tabs
    Given I am logged in as a user with the "<non-workbench roles>" role
    Then I should not see the link "My Workbench"
    And I should be denied access to the "My Workbench" page
    And I should be denied access to the "My Drafts" page
    And I should be denied access to the "Needs Review" page
    And I should be denied access to the "Stale Drafts" page
    And I should be denied access to the "Stale Reviews" page
    And I should be denied access to the "My Edits" page
    And I should be denied access to the "All Recent Content" page
    Examples:
      | non-workbench roles |
      | anonymous user      |
      | authenticated user  |
      | content creator     |
      | editor              |
      | site manager        |

  @workflow_02 @ok @globalUser
  Scenario Outline: As a user with any Workflow role, I should be able to access My Workbench.
    Given I am logged in as a user with the "<workbench roles>" role
    When I am on "My Workbench" page
    Then I should see the link "My content"
    And I should see the link "My drafts"
    And I should see the link "Stale drafts"
    And I should see the link "My Edits"
    And I should see the link "All Recent Content"
    Examples:
      | workbench roles      |
      | Workflow Contributor |
      | Workflow Moderator   |
      | Workflow Supervisor  |

  @workflow_03 @api @javascript @globalUser
  Scenario Outline: As a user with any Workflow role, I should be able to upgrade my own draft content to needs review.
    Given I am logged in as "<user>"
    And datasets:
      | title              | author | moderation | moderation_date | date created  |
      | My Draft Dataset   | <user> | draft      | Jul 21, 2015    | Jul 21, 2015  |
    And resources:
      | title              | dataset             | author | format |  moderation |  moderation_date | date created  |
      | My Draft Resource  | My Draft Dataset    | <user> | csv    |  draft      |  Jul 21, 2015    | Jul 21, 2015  |
    When I am on the "My Drafts" page
    Then I should see the button "Submit for review"
    And I should see "My Draft Dataset"
    And I should see "My Draft Resource"
    And I check the box "Select all items on this page"
    And I press "Submit for review"
    # TODO: use correct form - CIVIC-4131
    #Then I wait for "Performed Submit for review on 2 items."
    Then I wait for "Performed Submit for review"
    Examples:
      | user        |
      | Contributor |
      | Moderator   |
      | Supervisor  |

  # TODO: This tests is commented because a known bug.
  # More information: CIVIC-4891
  #
  # @ok @mail @javascript @globalUser
  # Scenario Outline: DEBUG
  #   Given I am logged in as a user with the "Workflow Contributor" role
  #   And datasets:
  #     | title                      | author        | moderation |
  #     | Draft Dataset Needs Review | Contributor   | draft      |
  #   And resources:
  #     | title                        | author       | dataset                       | format |  published |
  #     | Draft Resource Needs Review  | Contributor  | Draft Dataset Needs Review    | csv    |  no        |
  #   And I update the moderation state of "Draft Dataset Needs Review" to "Needs Review"
  #   And I update the moderation state of "Draft Resource Needs Review" to "Needs Review"
  #   When  I am logged in as a user with the "<workbench reviewer roles>" role
  #   And I visit the "Needs Review" page
  #   Then I should see the button "Reject"
  #   And I should see the button "Publish"
  #   And I should see "Draft Dataset Needs Review"
  #   And I should see "Draft Resource Needs Review"
  #   When I check the box "Select all items on this page"
  #   And I press "Publish"
  #   Then I wait for "Performed Publish on 2 items."
  #   Examples:
  #     | workbench reviewer roles              |
  #     | Workflow Moderator, editor            |
  #     | Workflow Supervisor, site manager     |

  @workflow_04 @ok @javascript @globalUser
  Scenario: As a user with the Workflow Supervisor role, I should be able to publish stale 'Needs Review' content.
    Given I am logged in as "Contributor"
    And datasets:
      | title                                 | author       | published | moderation_date   | date created  |
      | Stale Dataset DKAN Test Needs Review  | Contributor  | No        | Jul 21, 2015      | Jul 21, 2015  |
      | Fresh Dataset DKAN Test Needs Review  | Contributor  | No        | Jul 21, 2015      | Jul 21, 2015  |
    And resources:
      | title                                  | author       | dataset                              | format |  published |
      | Stale Resource DKAN Test Needs Review  | Contributor  | Stale Dataset DKAN Test Needs Review | csv    |  no        |
    And I update the moderation state of "Stale Dataset DKAN Test Needs Review" to "Needs Review" on date "30 days ago"
    And I update the moderation state of "Stale Resource DKAN Test Needs Review" to "Needs Review" on date "30 days ago"
    And I update the moderation state of "Fresh Dataset DKAN Test Needs Review" to "Needs Review" on date "20 days ago"
    Given I am logged in as "Supervisor"
    And I visit the "Stale Reviews" page
    And I should see the button "Reject"
    And I should see the button "Publish"
    When I fill in "edit-title" with "DKAN Test"
    And I press "Filter"
    Then I should see "Stale Dataset DKAN Test Needs Review"
    And I should see "Stale Resource DKAN Test Needs Review"
    And I should not see "Fresh Resource DKAN Test Needs Review"
    And I check the box "Select all items on this page"
    When I press "Publish"
    Then I wait for "Performed Publish on 3 items"

  @workflow_05 @ok @globalUser
  Scenario Outline: As a user with Workflow Roles, I should not be able to see draft contents I did not author in 'My Drafts'
    Given I am logged in as a user with the "<workbench roles>" role
    Given users:
      | name            | roles                |
      | some-other-user | Workflow Contributor |
    And datasets:
      | title           | author          | published |
      | Not My Dataset  | some-other-user | No        |
    And resources:
      | title            | dataset           | author          | format |  published |
      | Not My Resource  | Not My Dataset    | some-other-user | csv    |  no        |

    And I visit the "My Drafts" page
    And I should not see "Not My Resource"
    And I should not see "Not My Dataset"
    Examples:
      | workbench roles                       |
      | Workflow Contributor, content creator |
      | Workflow Moderator, editor            |
      | Workflow Supervisor, site manager     |

  @workflow_06 @ok @globalUser
  Scenario Outline: As a user with Workflow Roles, I should be able to see draft content I authored in 'My Drafts'
    Given I am logged in as "<user>"
    And datasets:
      | title      | author | moderation |
      | My Dataset | <user> | draft      |
    And resources:
      | title       | author | dataset    | format | moderation |
      | My Resource | <user> | My Dataset | csv    | draft      |
    And I visit the "My Drafts" page
    And I should see "My Resource"
    And I should see "My Dataset"
    Examples:
      | user        |
      | Contributor |
      | Moderator   |
      | Supervisor  |

  @workflow_07 @ok @globalUser
  Scenario Outline: As a user with Workflow Roles, I should not be able to see Published content I authored in workbench pages
    Given I am logged in as "Contributor"
    And datasets:
      | title      | author      | published |
      | My Dataset | Contributor | No        |
    And resources:
      | title       | author      | dataset    | format | published |
      | My Resource | Contributor | My Dataset | csv    | Yes       |
    And I update the moderation state of "My Dataset" to "Needs Review"
    And I update the moderation state of "My Resource" to "Needs Review"
    And "Moderator" updates the moderation state of "My Dataset" to "Published"
    And "Moderator" updates the moderation state of "My Resource" to "Published"
    And I am on "<page>" page
    # TODO: see CIVIC-4133 (debunks my current working theory).
    Then I should not see "My Dataset"
    Then I should not see "My Resource"
    Examples:
      | page          | workflow role                         |
      | My Drafts     | Workflow Contributor, content creator |
      | Needs Review  | Workflow Contributor, content creator |
      | My Drafts     | Workflow Moderator, editor            |
      | Needs Review  | Workflow Moderator, editor            |
      | My Drafts     | Workflow Supervisor, site manager     |
      | Needs Review  | Workflow Supervisor, site manager     |

  @workflow_08 @ok @globalUser
  Scenario Outline: As a user with Workflow Roles, I should not be able to see Needs Review resources I authored in 'My Drafts'
    Given I am logged in as a user with the "<workbench roles>" role
    And datasets:
      | title      | author      | published |
      | My Dataset | Contributor | No        |
    And resources:
      | title       | author      | dataset    | format | published |
      | My Resource | Contributor | My Dataset | csv    | no        |
    And I update the moderation state of "My Dataset" to "Needs Review"
    And I update the moderation state of "My Resource" to "Needs Review"
    And I visit the "My Drafts" page
    And I should not see "My Resource"
    And I should not see "My Dataset"
    Examples:
      | workbench roles                       |
      | Workflow Contributor, content creator |
      | Workflow Moderator, editor            |
      | Workflow Supervisor, site manager     |

  @workflow_09 @ok @globalUser
  Scenario: As a user with the Workflow Contributor, I should be able to see Needs Review contents I authored in 'Needs Review'
    Given I am logged in as "Contributor"
    And datasets:
      | title      | author      | moderation |
      | My Dataset | Contributor | draft      |
    And resources:
      | title       | author      | dataset    | format | moderation |
      | My Resource | Contributor | My Dataset | csv    | draft      |
    And I update the moderation state of "My Dataset" to "Needs Review"
    And I update the moderation state of "My Resource" to "Needs Review"
    And I visit the "Needs Review" page
    And I should see "My Resource"
    And I should see "My Dataset"

  @workflow_10 @ok @globalUser
  Scenario: As a user with the Workflow Contributor, I should not be able to see Needs Review contents I did not author in 'Needs Review'
    Given I am logged in as a user with the "Workflow Contributor" role
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
    And datasets:
      | title          | author          | moderation |
      | Not My Dataset | some-other-user | draft      |
    And resources:
      | title           | dataset        | author          | format | moderation |
      | Not My Resource | Not My Dataset | some-other-user | csv    | draft      |

    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    And "some-other-user" updates the moderation state of "Not My Resource" to "Needs Review"
    And I visit the "Needs Review" page
    Then I should not see "Not My Resource"
    Then I should not see "Not My Dataset"

  @workflow_11 @ok @globalUser
  Scenario: As a Workflow Moderator, I should be able to see Needs Review datasets I did not author, but which belongs to my Group, in 'Needs Review'
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | some-other-user | Group 01 | administrator member | Active            |
    And datasets:
      | title           | author          | published | publisher |
      | Not My Dataset  | some-other-user | No        | Group 01  |
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    Given I am logged in as "Moderator"
    And I am on "Needs Review" page
    Then I should see the text "Not My Dataset"

  @workflow_12 @ok @globalUser
  Scenario: As a Workflow Moderator, I should not be able to see Needs Review datasets I did not author, and which do not belong to my Group, in 'Needs Review'
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
      | moderator2      | Workflow Moderator |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | some-other-user | Group 01 | administrator member | Active            |
      | moderator2      | Group 02 | administrator member | Active            |
    And datasets:
      | title           | author          | published | publisher |
      | Not My Dataset  | some-other-user | No        | Group 01  |
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    Given I am logged in as "moderator2"
    And I am on "Needs Review" page
    Then I should not see the text "Not My Dataset"

  @workflow_13 @ok @globalUser
  Scenario: As a Workflow Supervisor, I should be able to see Needs Review content I did not author, regardless whether it belongs to my group or not, in 'Needs Review'
    Given users:
      | name            | roles                                 |
      | other-user      | Workflow Contributor, content creator |
      | some-other-user | Workflow Contributor, content creator |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | other-user      | Group 02 | administrator member | Active            |
      | some-other-user | Group 01 | administrator member | Active            |
    And datasets:
      | title                 | author          | published | publisher |
      | Still Not My Dataset  | other-user      | No        | Group 01  |
      | Not My Dataset        | some-other-user | No        | Group 02  |
    And "other-user" updates the moderation state of "Still Not My Dataset" to "Needs Review"
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    Given I am logged in as "Supervisor"
    And I am on "Needs Review" page
    Then I should see the text "Still Not My Dataset"
    Then I should see the text "Not My Dataset"

  @workflow_14 @ok @globalUser
  Scenario: As a Workflow Supervisor I should be able to see content in the 'Needs Review' state I did not author, regardless whether it belongs to my group or not, but which were submitted greater than 72 hours before now, in the 'Stale Reviews'
    Given users:
      | name            | roles                                 |
      | other-user      | Workflow Contributor, content creator |
      | some-other-user | Workflow Contributor, content creator |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | other-user      | Group 02 | administrator member | Active            |
      | some-other-user | Group 01 | administrator member | Active            |
    And datasets:
      | title                 | author          | published | publisher |
      | Still Not My Dataset  | other-user      | No        | Group 01  |
      | Not My Dataset        | some-other-user | No        | Group 02  |
    And "other-user" updates the moderation state of "Still Not My Dataset" to "Needs Review" on date "30 days ago"
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review" on date "30 days ago"
    Given I am logged in as "Supervisor"
    And I am on "Stale Reviews" page
    Then I should see the text "Still Not My Dataset"
    Then I should see the text "Not My Dataset"

  @workflow_15 @ok @globalUser
  Scenario: As a Workflow Supervisor I should be able to see content in the 'Draft' state I did not author, regardless whether it belongs to my group or not, but which were submitted greater than 72 hours before now, in the 'Stale Drafts'
    Given users:
      | name            | roles                                 |
      | other-user      | Workflow Contributor, content creator |
      | some-other-user | Workflow Contributor, content creator |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | other-user      | Group 02 | administrator member | Active            |
      | some-other-user | Group 01 | administrator member | Active            |
    And datasets:
      | title                 | author          | published | publisher |
      | Still Not My Dataset  | other-user      | No        | Group 01  |
      | Not My Dataset        | some-other-user | No        | Group 02  |
    And "other-user" updates the moderation state of "Still Not My Dataset" to "Draft" on date "30 days ago"
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Draft" on date "30 days ago"
    Given I am logged in as "Supervisor"
    And I am on "Stale Drafts" page
    Then I should see the text "Still Not My Dataset"
    Then I should see the text "Not My Dataset"

  @workflow_16 @api @ahoyRunMe @javascript @globalUser
  Scenario: When administering users, role pairings with core roles should be enforced
    Given I am logged in as a user with the "administrator" role
    And I visit the "Create User" page
    # Needed because honeypot module give error when filling out the register form
    # too quickly, so we need to add a wait.
    And I wait for "6" seconds
    Then the checkbox "content creator" should not be checked
    When I fill in "Username" with "Contributor RolePairing"
    And I fill in "E-mail address" with "pairing@test.com"
    And I fill in "Password" with "password"
    And I fill in "Confirm password" with "password"
    And I check the box "Workflow Contributor"
    Then the checkbox "content creator" should be checked
    When I press "Create new account"
    Then I should see "Created a new user account for Contributor RolePairing"
    When I click "Contributor RolePairing"
    And I click "Edit"
    Then the checkbox "content creator" should be checked

  @workflow_17 @api @globalUser
  Scenario: Modify user workflow roles as site manager
    Given users:
      | name            | roles           | mail           |
      | content-creator | content creator | pat@test.com   |
      | site-manager    | site manager    | chris@test.com |
    Given pages:
      | name          | url           |
      | Users         | /admin/people |

    Given I am logged in as "site-manager"
    And I am on "Users" page
    And I fill in "edit-name" with "content-creator"
    And I press "Apply"
    When I click "edit" in the "content-creator" row
    And I check "Workflow Contributor"
    And I press "Save"
    And I wait for "People"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    Then I should see "Workflow Contributor" in the "content-creator" row

  @workflow_18 @api @ahoyRunMe @javascript @globalUser
  Scenario: Role pairings should also work for site managers.
    Given users:
      | name            | roles                             |
      | site-manager    | Workflow Supervisor, site manager |

    Given I am logged in as "site-manager"
    And I visit the "Create User" page
    # Needed because honeypot module give error when filling out the register form
    # too quickly, so we need to add a wait.
    And I wait for "6" seconds
    Then the checkbox "editor" should not be checked
    When I fill in "Username" with "Moderator RolePairing"
    And I fill in "E-mail address" with "pairing2@test.com"
    And I fill in "Password" with "password"
    And I fill in "Confirm password" with "password"
    And I check the box "Workflow Moderator"
    Then the checkbox "editor" should be checked
    When I press "Create new account"
    Then I should see "Created a new user account for Moderator RolePairing"
    When I click "Moderator RolePairing"
    And I click "Edit"
    Then the checkbox "editor" should be checked

  @workflow_19 @ok
  # https://jira.govdelivery.com/browse/CIVIC-5348
  Scenario: "View draft" should display the draft dataset and not the published revision.
    And datasets:
      | title         | author      | published | moderation | publisher |
      | Dataset title | Contributor | Yes       | published  | Group 01  |
    Given I update the moderation state of "Dataset title" to "Published"
    Given I am logged in as "Contributor"
    And I am on "Dataset title" page
    Then I should see the text "Dataset title"
    When I click "Edit draft"
    And for "title" I enter "Dataset draft title"
    And I press "Finish"
    And I click "View draft"
    Then I should see "Dataset draft title"

  @workflow_20 @api @javascript @harvest
  Scenario: Check harvested datasets are published by default even when dkan_workflow is enabled.
    Given users:
      | name               | mail                     | status | roles             |
      | Administrator      | admin@fakeemail.com      | 1      | administrator     |
    And The "source_one" source is harvested
    And the content "Gold Prices in London 1950-2008 (Monthly) Harvest" should be "published"
    
  @workflow_21 @ok @globalUser
  Scenario: As a Workflow Moderator, I should be able to see Stale Needs Review datasets I did not author, but which belongs to my Group, in 'Needs Review'
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | some-other-user | Group 01 | administrator member | Active            |
    And datasets:
      | title           | author          | published | publisher |
      | Not My Dataset  | some-other-user | No        | Group 01  |
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review" on date "30 days ago"
    Given I am logged in as "Moderator"
    And I am on "Stale Reviews" page
    Then I should see the text "Not My Dataset"
    
  @workflow_22 @api @javascript @globalUser
  Scenario: As a user I should be able to see my content back on "My Drafts" section if it was rejected
    # Submit a dataset to Needs Review
    Given I am logged in as "Contributor"
    And datasets:
      | title              | author      | moderation | moderation_date | date created  |
      | My Draft Dataset   | Contributor | draft      | Jul 21, 2015    | Jul 21, 2015  |
    When I am on the "My Drafts" page
    Then I should see the button "Submit for review"
    And I should see "My Draft Dataset"
    When I check the box "Select all items on this page"
    And I press "Submit for review"
    And I wait for "Performed Submit for review"
    And I am on the "My Drafts" page
    Then I should not see "My Draft Dataset"
    # Reject dataset
    Given I am logged in as "Supervisor"
    When I am on the "Needs Review" page
    Then I should see "My Draft Dataset"
    When I check the box "Select all items on this page"
    And I press "Reject"
    Then I wait for "Performed Reject"
    # Check that the dataset is back
    Given I am logged in as "Contributor"
    When I am on the "My Drafts" page
    Then I should see "My Draft Dataset"

  @workflow_23 @javascript
  Scenario: As an anonymous user I should see a revisions link when dkan_workflow is enabled.
    Given pages:
      | name               | url                                  |
      | Datasets           | /search/type/dataset                 |
      | Rebuild perms      | /admin/reports/status/rebuild        |
    And datasets:
      | title                 | publisher | author    | published   | description |
      | Dataset Revision Test | Group 01  | Moderator | Yes         | Test        |
    When I am on the "Datasets" page
    And I click "Dataset Revision Test"
    Then I should not see "Revisions"
    Given I am logged in as a user with the "administrator" role
    And I am on the "Dataset Revision Test" page
    When I click "New draft"
    And I fill in "edit-title" with "Dataset Revision Test NEW"
    And I click "Publishing options"
    Then I select "Published" from "edit-workbench-moderation-state-new"
    And I press "Finish"
    Given I am on the "Rebuild perms" page
    And I press "Rebuild permissions"
    And I wait for "Status report"
    And I click "Log out"
    And I am on the "Datasets" page
    And I click "Dataset Revision Test"
    Then I should see "Revisions"
