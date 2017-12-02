@api @enableDKAN_Workflow @disablecaptcha
Feature:
  Workflow (Workbench) tests for emails related to DKAN Workflow Module

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

  # EMAIL NOTIFICATIONS: Content WITH group.
  @api @mail @globalUser
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
  @api @mail @globalUser @no-group
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
