@api @disablecaptcha @javascript
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

  # Non workbench roles can see the menu item My Workflow. However
  # they can't access to the page.
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

  @ok
  Scenario Outline: As a user with any Workflow role, I should be able to access My Workbench.
    Given I am logged in as a user with the "<workbench roles>" role
    Then I should see the link "My Workbench"
    When I follow "My Workbench"
    Then The page status should be "ok"
    And I should be on the "My Workbench" page
    And I should see the link "My content"
    #And I should see the link " My drafts"
    And I should see the link "My Edits"
    And I should see the link "All Recent Content"
    Examples:
      | workbench roles                       |
      | Workflow Contributor, content creator |
      | Workflow Moderator, editor            |
      | Workflow Supervisor, site manager     |

  @ok
  Scenario Outline: As a user with any Workflow role, I should be able to upgrade my own draft content to needs review.
    Given I am logged in as a user with the "<workbench roles>" role
    And datasets:
      | title              | published |
      | My Draft Dataset   | no        |
    And resources:
      | title              | dataset             | format |  published |
      | My Draft Resource  | My Draft Dataset    | csv    |  no        |
    And I am on the "My Drafts" page
    Then I should see the button "Submit for review"
    And I should see "My Draft Dataset"
    And I should see "My Draft Resource"
    And I check the box "Select all items on this page"
    And I press "Submit for review"
    Then I wait for "Performed Submit for review on 2 items."
    Examples:
      | workbench roles         |
      | Workflow Contributor, content creator |
      | Workflow Moderator, editor            |
      | Workflow Supervisor, site manager     |

  @ok
  Scenario Outline: As a user with the Workflow Moderator or Supervisor role, I should be able to publish 'Needs Review' content.
    Given I am logged in as a user with the "Workflow Contributor" role
    And datasets:
      | title                       | published |
      | Draft Dataset Needs Review  | No        |
    And resources:
      | title                        | dataset             | format |  published |
      | Draft Resource Needs Review  | Draft Dataset Needs Review    | csv    |  no        |
    And I update the moderation state of "Draft Dataset Needs Review" to "Needs Review"
    And I update the moderation state of "Draft Resource Needs Review" to "Needs Review"
    Given I am logged in as a user with the "<workbench reviewer roles>" role
    And I visit the "Needs Review" page
    And I should see the button "Reject"
    And I should see the button "Publish"
    And I should see "Draft Dataset Needs Review"
    And I should see "Draft Resource Needs Review"
    And I check the box "Select all items on this page"
    When I press "Publish"
    Then I wait for "Performed Publish on 2 items."
    Examples:
      | workbench reviewer roles              |
      | Workflow Moderator, editor            |
      | Workflow Supervisor, site manager     |

  @ok
  Scenario: As a user with the Workflow Supervisor role, I should be able to publish stale 'Needs Review' content.
    Given I am logged in as a user with the "Workflow Contributor" role
    And datasets:
      | title                       | published |
      | Stale Dataset Needs Review  | No        |
      | Fresh Dataset Needs Review  | No        |
    And resources:
      | title                        | dataset                    | format |  published |
      | Stale Resource Needs Review  | Stale Dataset Needs Review | csv    |  no        |
    And I update the moderation state of "Stale Dataset Needs Review" to "Needs Review" on date "30 days ago"
    And I update the moderation state of "Stale Resource Needs Review" to "Needs Review" on date "30 days ago"
    And I update the moderation state of "Fresh Dataset Needs Review" to "Needs Review" on date "20 days ago"
    Given I am logged in as a user with the "Workflow Supervisor" role
    And I visit the "Stale Reviews" page
    And I should see the button "Reject"
    And I should see the button "Publish"
    And I should see "Stale Dataset Needs Review"
    And I should see "Stale Resource Needs Review"
    And I should not see "Fresh Resource Needs Review"
    And I check the box "Select all items on this page"
    When I press "Publish"
    Then I wait for "Performed Publish on 3 items"

  @ok
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

  @ok
  Scenario Outline: As a user with Workflow Roles, I should be able to see draft content I authored in 'My Drafts'
    Given I am logged in as a user with the "<workbench roles>" role
    And datasets:
      | title       | published |
      | My Dataset  | No        |
    And resources:
      | title        | dataset    | format |  published |
      | My Resource  | My Dataset | csv    |  no        |
    And I visit the "My Drafts" page
    And I should see "My Resource"
    And I should see "My Dataset"
    Examples:
      | workbench roles                       |
      | Workflow Contributor, content creator |
      | Workflow Moderator, editor            |
      | Workflow Supervisor, site manager     |

  @ok
  Scenario Outline: As a user with Workflow Roles, I should not be able to see Published content I authored in workbench pages
    Given users:
      | name            | roles                                 | mail                  |
      | contributor     | Workflow Contributor, content creator | contributor@email.com |
      | moderator       | Workflow Moderator, editor            | moderator@email.com   |
    Given I am logged in as "contributor"
    And datasets:
      | title                         | published |
      | My Dataset                    | No        |
    And resources:
      | title        | dataset       | format |  published |
      | My Resource  | My Dataset    | csv    |  Yes       |
    And I update the moderation state of "My Dataset" to "Needs Review"
    And I update the moderation state of "My Resource" to "Needs Review"
    And "moderator" updates the moderation state of "My Dataset" to "Published"
    And "moderator" updates the moderation state of "My Resource" to "Published"
    And I am on "<page>" page
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

  @ok
  Scenario Outline: As a user with Workflow Roles, I should not be able to see Needs Review resources I authored in 'My Drafts'
    Given I am logged in as a user with the "<workbench roles>" role
    And datasets:
      | title       | published |
      | My Dataset  | No        |
    And resources:
      | title        | dataset       | format |  published |
      | My Resource  | My Dataset    | csv    |  no        |
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

  @ok
  Scenario: As a user with the Workflow Contributor, I should be able to see Needs Review contents I authored in 'Needs Review'
    Given I am logged in as a user with the "Workflow Contributor" role
    And datasets:
      | title       | published |
      | My Dataset  | Yes       |
    And resources:
      | title        | dataset       | format |  published |
      | My Resource  | My Dataset    | csv    |  Yes       |
    And I update the moderation state of "My Dataset" to "Needs Review"
    And I update the moderation state of "My Resource" to "Needs Review"
    And I visit the "Needs Review" page
    And I should see "My Resource"
    And I should see "My Dataset"

  @ok
  Scenario: As a user with the Workflow Contributor, I should not be able to see Needs Review contents I did not author in 'Needs Review'
    Given I am logged in as a user with the "Workflow Contributor" role
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
      | contributor     | Workflow Contributor, content creator |
    And datasets:
      | title           | author          | published |
      | Not My Dataset  | some-other-user | No        |
    And resources:
      | title            | dataset           | author          | format |  published  |
      | Not My Resource  | Not My Dataset    | some-other-user | csv    |  no         |

    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    And "some-other-user" updates the moderation state of "Not My Resource" to "Needs Review"
    And I visit the "Needs Review" page
    Then I should not see "Not My Resource"
    Then I should not see "Not My Dataset"

  @ok
  Scenario: As a Workflow Moderator, I should be able to see Needs Review datasets I did not author, but which belongs to my Group, in 'Needs Review'
    Given groups:
      | title    | published |
      | Group 01 | Yes       |
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
      | moderator       | Workflow Moderator, editor            |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | some-other-user | Group 01 | administrator member | Active            |
      | moderator       | Group 01 | administrator member | Active            |
    And datasets:
      | title           | author          | published | publisher |
      | Not My Dataset  | some-other-user | No        | Group 01  |
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    Given I am logged in as "moderator"
    And I am on "Needs Review" page
    Then I should see the text "Not My Dataset"

  @ok
  Scenario: As a Workflow Moderator, I should not be able to see Needs Review datasets I did not author, and which do not belong to my Group, in 'Needs Review'
    Given groups:
      | title    | published |
      | Group 01 | Yes       |
      | Group 02 | Yes       |
    Given users:
      | name            | roles                                 |
      | some-other-user | Workflow Contributor, content creator |
      | moderator       | Workflow Moderator, editor            |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | some-other-user | Group 01 | administrator member | Active            |
      | moderator       | Group 02 | administrator member | Active            |
    And datasets:
      | title           | author          | published | publisher |
      | Not My Dataset  | some-other-user | No        | Group 01  |
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    Given I am logged in as "moderator"
    And I am on "Needs Review" page
    Then I should not see the text "Not My Dataset"

  @ok
  Scenario: As a Workflow Supervisor, I should be able to see Needs Review content I did not author, regardless whether it belongs to my group or not, in 'Needs Review'
    Given groups:
      | title    | published |
      | Group 01 | Yes       |
      | Group 02 | Yes       |
    Given users:
      | name            | roles                                 |
      | other-user      | Workflow Contributor, content creator |
      | some-other-user | Workflow Contributor, content creator |
      | supervisor      | Workflow Supervisor, site manager     |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | other-user      | Group 02 | administrator member | Active            |
      | some-other-user | Group 01 | administrator member | Active            |
      | supervisor      | Group 01 | administrator member | Active            |
    And datasets:
      | title                 | author          | published | publisher |
      | Still Not My Dataset  | other-user      | No        | Group 01  |
      | Not My Dataset        | some-other-user | No        | Group 02  |
    And "other-user" updates the moderation state of "Still Not My Dataset" to "Needs Review"
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review"
    Given I am logged in as "supervisor"
    And I am on "Needs Review" page
    Then I should see the text "Still Not My Dataset"
    Then I should see the text "Not My Dataset"

  @ok
  Scenario: As a Workflow Supervisor I should be able to see content in the 'Needs Review' state I did not author, regardless whether it belongs to my group or not, but which were submitted greater than 72 hours before now, in the 'Stale Reviews'
    Given groups:
      | title    | published |
      | Group 01 | Yes       |
      | Group 02 | Yes       |
    Given users:
      | name            | roles                                 |
      | other-user      | Workflow Contributor, content creator |
      | some-other-user | Workflow Contributor, content creator |
      | supervisor      | Workflow Supervisor, site manager     |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | other-user      | Group 02 | administrator member | Active            |
      | some-other-user | Group 01 | administrator member | Active            |
      | supervisor      | Group 01 | administrator member | Active            |
    And datasets:
      | title                 | author          | published | publisher |
      | Still Not My Dataset  | other-user      | No        | Group 01  |
      | Not My Dataset        | some-other-user | No        | Group 02  |
    And "other-user" updates the moderation state of "Still Not My Dataset" to "Needs Review" on date "30 days ago"
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Needs Review" on date "30 days ago"
    Given I am logged in as "supervisor"
    And I am on "Stale Reviews" page
    Then I should see the text "Still Not My Dataset"
    Then I should see the text "Not My Dataset"

  @ok
  Scenario: As a Workflow Supervisor I should be able to see content in the 'Draft' state I did not author, regardless whether it belongs to my group or not, but which were submitted greater than 72 hours before now, in the 'Stale Drafts'
    Given groups:
      | title    | published |
      | Group 01 | Yes       |
      | Group 02 | Yes       |
    Given users:
      | name            | roles                                 |
      | other-user      | Workflow Contributor, content creator |
      | some-other-user | Workflow Contributor, content creator |
      | supervisor      | Workflow Supervisor, site manager     |
    And group memberships:
      | user            | group    | role on group        | membership status |
      | other-user      | Group 02 | administrator member | Active            |
      | some-other-user | Group 01 | administrator member | Active            |
      | supervisor      | Group 01 | administrator member | Active            |
    And datasets:
      | title                 | author          | published | publisher |
      | Still Not My Dataset  | other-user      | No        | Group 01  |
      | Not My Dataset        | some-other-user | No        | Group 02  |
    And "other-user" updates the moderation state of "Still Not My Dataset" to "Draft" on date "30 days ago"
    And "some-other-user" updates the moderation state of "Not My Dataset" to "Draft" on date "30 days ago"
    Given I am logged in as "supervisor"
    And I am on "Stale Drafts" page
    Then I should see the text "Still Not My Dataset"
    Then I should see the text "Not My Dataset"

  # EMAIL NOTIFICATIONS: Content WITH group.

  @api @disablecaptcha @mail
  Scenario Outline: As a user with a workflow role I should receive an email notification if needed when the moderation status on a content with group is changed
    Given users:
      | name             | mail                       | status | roles                                 |
      | Administrator    | admin@test.com             | 1      | administrator                         |
      | Contributor C1G1 | contributor-c1g1@test.com  | 1      | Workflow Contributor, content creator |
      | Contributor C2G1 | contributor-c2g1@test.com  | 1      | Workflow Contributor, content creator |
      | Moderator M1G1   | moderator-m1g1@test.com    | 1      | Workflow Moderator, editor            |
      | Moderator M2G1   | moderator-m2g1@test.com    | 1      | Workflow Moderator, editor            |
      | Supervisor S1G1  | supervisor-s1g1@test.com   | 1      | Workflow Supervisor, site manager     |
      | Contributor C1G2 | contributor-c1g2@test.com  | 1      | Workflow Contributor, content creator |
      | Moderator M1G2   | moderator-m1g2@test.com    | 1      | Workflow Moderator, editor            |
      | Supervisor S1G2  | supervisor-s1g2@test.com   | 1      | Workflow Supervisor, site manager     |

    Given groups:
      | title    | author         | published |
      | Group 01 | Administrator  | Yes       |
      | Group 02 | Administrator  | Yes       |
    And group memberships:
      | user              | group    | role on group        | membership status |
      | Administrator     | Group 01 | administrator member | Active            |
      | Contributor C1G1  | Group 01 | member               | Active            |
      | Contributor C2G1  | Group 01 | member               | Active            |
      | Moderator M1G1    | Group 01 | member               | Active            |
      | Moderator M2G1    | Group 01 | member               | Active            |
      | Supervisor S1G1   | Group 01 | member               | Active            |
      | Administrator     | Group 02 | administrator member | Active            |
      | Contributor C1G2  | Group 02 | member               | Active            |
      | Moderator M1G2    | Group 02 | member               | Active            |
      | Supervisor S1G2   | Group 02 | member               | Active            |
    And datasets:
      | title         | author           | published | publisher |
      | Dataset 01    | Contributor C1G1 | No        | Group 01  |

    Given I am logged in as "Moderator M1G1"
    # Transition: Draft -> Needs Review
    When "Moderator M1G1" updates the moderation state of "Dataset 01" to "Needs Review"
    Then the user "<user>" <outcome> receive an email

    # Transition: Needs Review -> Draft
    Given "Moderator M1G1" updates the moderation state of "Dataset 01" to "Needs Review"
    And the email queue is cleared
    When "Moderator M1G1" updates the moderation state of "Dataset 01" to "Draft"
    Then the user "<user>" <outcome> receive an email

    # Transition: Needs Review -> Published
    Given "Moderator M1G1" updates the moderation state of "Dataset 01" to "Needs Review"
    And the email queue is cleared
    When "Moderator M1G1" updates the moderation state of "Dataset 01" to "Published"
    Then the user "<user>" <outcome> receive an email

  Examples:
    | user             | outcome    |
    | Contributor C1G1 | should     |
    | Contributor C2G1 | should not |
    | Moderator M1G1   | should not |
    | Moderator M2G1   | should     |
    | Supervisor S1G1  | should not |
    | Contributor C1G2 | should not |
    | Moderator M1G2   | should not |
    | Supervisor S1G2  | should not |


  # EMAIL NOTIFICATIONS: Content WITHOUT group.

  @api @disablecaptcha @mail
  Scenario Outline: As a user with a workflow role I should receive an email notification if needed when the moderation status on a content without group is changed
    Given users:
      | name             | mail                       | status | roles                                 |
      | Administrator    | admin@test.com             | 1      | administrator                         |
      | Contributor C1G1 | contributor-c1g1@test.com  | 1      | Workflow Contributor, content creator |
      | Contributor C2G1 | contributor-c2g1@test.com  | 1      | Workflow Contributor, content creator |
      | Moderator M1G1   | moderator-m1g1@test.com    | 1      | Workflow Moderator, editor            |
      | Moderator M2G1   | moderator-m2g1@test.com    | 1      | Workflow Moderator, editor            |
      | Supervisor S1G1  | supervisor-s1g1@test.com   | 1      | Workflow Supervisor, site manager     |
      | Supervisor S2G1  | supervisor-s2g1@test.com   | 1      | Workflow Supervisor, site manager     |
      | Contributor C1G2 | contributor-c1g2@test.com  | 1      | Workflow Contributor, content creator |
      | Moderator M1G2   | moderator-m1g2@test.com    | 1      | Workflow Moderator, editor            |
      | Supervisor S1G2  | supervisor-s1g2@test.com   | 1      | Workflow Supervisor, site manager     |

    Given groups:
      | title    | author         | published |
      | Group 01 | Administrator  | Yes       |
      | Group 02 | Administrator  | Yes       |
    And group memberships:
      | user              | group    | role on group        | membership status |
      | Administrator     | Group 01 | administrator member | Active            |
      | Contributor C1G1  | Group 01 | member               | Active            |
      | Contributor C2G1  | Group 01 | member               | Active            |
      | Moderator M1G1    | Group 01 | member               | Active            |
      | Moderator M2G1    | Group 01 | member               | Active            |
      | Supervisor S1G1   | Group 01 | member               | Active            |
      | Administrator     | Group 02 | administrator member | Active            |
      | Contributor C1G2  | Group 02 | member               | Active            |
      | Moderator M1G2    | Group 02 | member               | Active            |
      | Supervisor S1G2   | Group 02 | member               | Active            |
    And datasets:
      | title         | author           | published | publisher |
      | Dataset 01    | Contributor C1G1 | No        |           |

    Given I am logged in as "Moderator M1G1"
    # Transition: Draft -> Needs Review
    When "Moderator M1G1" updates the moderation state of "Dataset 01" to "Needs Review"
    Then the user "<user>" <outcome> receive an email

    # Transition: Needs Review -> Draft
    Given "Moderator M1G1" updates the moderation state of "Dataset 01" to "Needs Review"
    And the email queue is cleared
    When "Moderator M1G1" updates the moderation state of "Dataset 01" to "Draft"
    Then the user "<user>" <outcome> receive an email

    # Transition: Needs Review -> Published
    Given "Moderator M1G1" updates the moderation state of "Dataset 01" to "Needs Review"
    And the email queue is cleared
    When "Moderator M1G1" updates the moderation state of "Dataset 01" to "Published"
    Then the user "<user>" <outcome> receive an email

  Examples:
    | user             | outcome    |
    | Contributor C1G1 | should     |
    | Contributor C2G1 | should not |
    | Moderator M1G1   | should not |
    | Moderator M2G1   | should not |
    | Supervisor S1G1  | should     |
    | Supervisor S2G1  | should     |
    | Contributor C1G2 | should not |
    | Moderator M1G2   | should not |
    | Supervisor S1G2  | should     |


  @api @javascript @ahoyRunMe
  Scenario: When administering users, role pairings with core roles should be enforced

    Given I am logged in as a user with the "administrator" role
    And I visit the "Create User" page
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

  @api
  Scenario: Modify user workflow roles as site manager
    Given users:
      | name     | roles           |
      | Pat      | content creator |
      | Chris    | site manager    |
    Given pages:
      | name          | url           |
      | Users         | /admin/people |

    Given I am logged in as "Chris"
    And I am on "Users" page
    When I click "edit" in the "Pat" row
    And I check "Workflow Contributor"
    And I press "Save"
    And I wait for "People"
    Then I should see "The changes have been saved"
    When I am on "Users" page
    Then I should see "Workflow Contributor" in the "Pat" row
