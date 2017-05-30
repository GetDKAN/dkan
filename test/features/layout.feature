Feature: Breadcrumb
  In order to navigate easily
  As a website user
  I need to be able to use breadcrumb links

@api
Scenario: Breadcrumb navigation
    Given users:
      | name            | mail                  | status     | roles        |
      | myadmin1        | myadmin@test.com      | 1          | 30037204     |
    When I am logged in as "myadmin1"
    And I am on the homepage
    Then I should not see any breadcrumb
    When I am on "node/add/dataset"
    Then I should see the breadcrumb "Home (linked to /) > Add content (linked to /node/add) > Add dataset"
    When I am on "about"
    Then I should see the breadcrumb "Home (linked to /) > About"
    When I am on "dataset"
    Then I should see the breadcrumb "Home (linked to /) > Datasets"
    When I am on "stories"
    Then I should see the breadcrumb "Home (linked to /) > Stories"
    When I am on "groups"
    Then I should see the breadcrumb "Home (linked to /) > Groups"
    When I am on "user"
    Then I should see the breadcrumb "Home (linked to /) > myadmin1"
