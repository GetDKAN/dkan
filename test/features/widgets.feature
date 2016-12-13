@api @javascript
Feature: Widgets

  Background:
    Given groups:
      | title    | author  | published |
      | Group 01 | admin  | Yes       |
    And "Tags" terms:
      | name     |
      | Health 2 |
    And "Format" terms:
      | name   |
      | csv 2  |
    And datasets:
      | title                          | publisher | author | published        | tags     | description |
      | 11111AAAAAAafghanistan Election Districts | Group 01  | admin  | Yes              | Health 2 | Test        |
    And resources:
      | title          | publisher | format | author | published | dataset                        | description |
      | District Names | Group 01  | csv 2  | admin  | Yes       | 11111AAAAAAafghanistan Election Districts |             |
    And I am logged in as a user with the "site manager" role
    And I am on the homepage
    And I wait for "Customize this page"
    When I click "Customize this page"
    And I wait for "Add new pane"
    And I click "Add new pane"
    And I wait for "Please select a category from the left"

  Scenario: Adds "New Link Widget" block to home page using panels ipe editor
    When I click on the text " Add links"
      And I wait for "Configure new Add links"
      And I fill in "field_quick_links_links[und][0][title]" with "Link example"
      And I fill in "field_quick_links_links[und][0][url]" with "http://demo.getdkan.com"
      And I press "Finish"
      And I wait and press "Save"
      Then I should see "Link example"

  Scenario: Adds "New File Widget" block to home page using panels ipe editor
    When I click on the text " Add file"
      And I wait for "Configure new Add file"
      And I attach the drupal file "dkan/actionplan.pdf" to "files[field_basic_file_file_und_0]"
      And I press "Finish"
      And I wait and press "Save"
      Then I should see "actionplan.pdf"

  Scenario: Adds "New Image Widget" block to home page using panels ipe editor
    When I click on the text " Add image"
      And I wait for "Configure new Add image"
      And I fill in "field_basic_image_caption[und][0][value]" with "dkan logo image test"
      And I attach the drupal file "dkan/dkan_logo.png" to "files[field_basic_image_image_und_0]"
      And I press "Finish"
      And I wait and press "Save"
      Then I should see "dkan logo image test"

  Scenario: Adds "New Text Widget" block to home page using panels ipe editor
    When I click on the text " Add text"
      And I wait for "Configure new Add text"
      And I fill in "field_basic_text_text[und][0][value]" with "text example"
      And I press "Finish"
      And I wait and press "Save"
      Then I should see "text example"

  Scenario: Adds "New Map Widget" block to home page using panels ipe editor
    When I click on the text " Add map"
    And I wait for "Configure new Add map"
      And I fill in "field_map_address[und][0][value]" with "175th St, Jamaica, NY 11433, USA"
      And I fill in "field_map_information[und][0][value]" with "map example"
      And I press "Finish"
      And I wait and press "Save"
      Then I should see "map example"

  Scenario: Adds "New Table Widget" block to home page using panels ipe editor
    When I click on the text " Add table"
    And I wait for "Configure new Add table"
      And I fill in "field-basic-table-table-und-0-tablefield-cell-0-0" with "date"
      And I fill in "field-basic-table-table-und-0-tablefield-cell-0-1" with "price"
      And I fill in "field-basic-table-table-und-0-tablefield-cell-1-0" with "05/05/15"
      And I fill in "field-basic-table-table-und-0-tablefield-cell-1-1" with "12.3"
      And I fill in "field-basic-table-table-und-0-tablefield-cell-2-0" with "05/06/15"
      And I fill in "field-basic-table-table-und-0-tablefield-cell-2-1" with "9.3"
      And I press "Finish"
      And I wait and press "Save"
      Then I should see "date"
      Then I should see "price"
      Then I should see "05/05/15"
      Then I should see "12.3"
      Then I should see "05/06/15"
      Then I should see "9.3"

  Scenario: Adds "New Video Widget" block to home page using panels ipe editor
    When I click on the text " Add video"
    And I wait for "Configure new Add video"
    When I fill in "Testing video" for "edit-title"
    When I click "Browse"
      And I wait for "2" seconds
      And I switch to the frame "mediaBrowser"
    Then I wait for "Supported internet media providers"
      And I should see "YouTube"
    When I fill in "File URL or media resource" with "https://www.youtube.com/watch?v=1TV0q4Sdxlc"
      And I press "Next"
    And I wait and press "Finish"
    And I wait and press "Save"
    Then I should see "Testing video"

  Scenario: Adds "New Spotlight Widget" block to home page using panels ipe editor
    When I click on the text " Add spotlight"
    And I wait for "Configure new Add spotlight"
      And I fill in "field_basic_spotlight_items[und][0][title]" with "First spot"
      And I fill in "field_basic_spotlight_items[und][0][link]" with "http://demo.getdkan.com"
      And I attach the drupal file "dkan/dkan_logo.png" to "files[field_basic_spotlight_items_und_0_fid]"
      And I press "Finish"
    And I wait and press "Save"
      And I wait for "First spot"
      Then I should see "First spot" in the "content"

  Scenario: Adds "New Submenu Widget" block to home page using panels ipe editor
    When I click on the text " Add submenu"
    And I wait for "Configure new Add submenu"
      And I press "Finish"
    And I wait and press "Save"
      Then I should see "Datasets" in the "content"

  Scenario: Adds "New Content List Widget" block to home page using panels ipe editor
    When I click on the text " Add content list"
    And I wait for "Configure new Add content list"
    When I select "Dataset" from "exposed[type]"
      And I select "Asc" from "exposed[sort_order]"
      And I select "Title" from "exposed[sort_by]"
      And I press "Finish"
    And I wait and press "Save"
    Then I should see "11111AAAAAAafghanistan"
      And I should see "Posted by admin"

  Scenario: Adds "New Content Item Widget" block to home page using panels ipe editor
    When I click on the text " Add content item"
    And I wait for "Configure new Add content item"
    When I select "Resource" from "exposed[type]"
      And I fill in "exposed[title]" with "District Names"
      And I press "Finish"
    And I wait and press "Save"
    Then I should see "District Names"
      And I should see "Posted by admin"
