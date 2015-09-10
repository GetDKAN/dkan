Feature: Breadcrumb
  In order to navigate easily
  As a website user
  I need to be able to use breadcrumb links

@api
Scenario: Breadcrumb navigation
    Given users:
      | name            | mail                  | status     | roles        |
      | myadmin1        | myadmin@test.com      | 1          | 30037204     |
    And I am logged in as "myadmin1"
    When I am on the homepage
    Then I should not see a "ul.breadcrumb" element
    When I am on "node/add/dataset"
    Then I should see "Home" in the "ul.breadcrumb li:nth-child(1)" element
    And I should see "Add content" in the "ul.breadcrumb li:nth-child(2)" element
    And I should see "Add dataset" in the "ul.breadcrumb li:nth-child(3)" element
    When I am on "about"
    Then I should see "Home" in the "ul.breadcrumb li:nth-child(1)" element
    And I should see "About" in the "ul.breadcrumb li:nth-child(2)" element
    When I am on "dataset"
    Then I should see "Home" in the "ul.breadcrumb li:nth-child(1)" element
    And I should see "Datasets" in the "ul.breadcrumb li:nth-child(2)" element
    And I should see "Search" in the "ul.breadcrumb li:nth-child(3)" element
    When I am on "stories"
    Then I should see "Home" in the "ul.breadcrumb li:nth-child(1)" element
    And I should see "Stories" in the "ul.breadcrumb li:nth-child(2)" element
    When I am on "groups"
    Then I should see "Home" in the "ul.breadcrumb li:nth-child(1)" element
    And I should see "Groups" in the "ul.breadcrumb li:nth-child(2)" element
    When I am on "user"
    Then I should see "Home" in the "ul.breadcrumb li:nth-child(1)" element
    And I should see "myadmin1" in the "ul.breadcrumb li:nth-child(2)" element
