Feature: Widgets

  Background:
    Given I am logged in as a user with the "administrator" role

  @api @javascript
  Scenario: Adds "New Link Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add links"
      And I fill in "edit-field-quick-links-links-und-0-title" with "Link example"
      And I fill in "edit-field-quick-links-links-und-0-url" with "http://demo.getdkan.com"
      And I press "Finish"
      And I press "Save"
      Then I should see "Link example"

  @api @javascript
  Scenario: Adds "New File Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add file"
      And I attach the file "actionplan.pdf" to "files[field_basic_file_file_und_0]"
      And I press "Finish"
      And I press "Save"
      Then I should see "actionplan.pdf"

  @api @javascript
  Scenario: Adds "New Image Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add image"
      And I fill in "field_basic_image_caption[und][0][value]" with "dkan logo image test"
      And I attach the file "dkan_logo.png" to "files[field_basic_image_image_und_0]"
      And I press "Finish"
      And I press "Save"
      Then I should see "dkan logo image test"

  @api @javascript
  Scenario: Adds "New Text Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add text"
      And I fill in "field_basic_text_text[und][0][value]" with "text example"
      And I press "Finish"
      And I press "Save"
      Then I should see "text example"

  @api @javascript
  Scenario: Adds "New Map Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add map"
      And I fill in "field_map_address[und][0][value]" with "175th St, Jamaica, NY 11433, USA"
      And I fill in "field_map_information[und][0][value]" with "map example"
      And I press "Finish"
      And I press "Save"
      Then I should see "map example"

  @api @javascript
  Scenario: Adds "New Table Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add table"
      And I fill in "tablefield_0_cell_0_0" with "date"
      And I fill in "tablefield_0_cell_0_1" with "price"
      And I fill in "tablefield_0_cell_1_0" with "05/05/15"
      And I fill in "tablefield_0_cell_1_1" with "12.3"
      And I fill in "tablefield_0_cell_2_0" with "05/06/15"
      And I fill in "tablefield_0_cell_2_1" with "9.3"
      And I press "Finish"
      And I press "Save"
      Then I should see "date"
      Then I should see "price"
      Then I should see "05/05/15"
      Then I should see "12.3"
      Then I should see "05/06/15"
      Then I should see "9.3"

  # @api @javascript
  # Scenario: Adds "New Video Widget" block to home page using panels ipe editor
  #   Given I am logged in as a user with the "administrator" role
  #     And I am on the homepage
  #     Then I should see "Customize this page"
  #   When I click "Customize this page"
  #     And I click "Add new pane"
  #     Then I should see "Please select a category from the left"
  #   When I click on the text " Add video"
  #   When I fill in "Testing video" for "edit-title"
  #   When I click "Browse"
  #     And I switch to the frame "mediaBrowser"
  #     And I wait for "3" seconds
  #   Then I should see "Supported internet media providers"
  #     And I should see "YouTube"
  #   When I fill in "File URL or media resource" with "https://www.youtube.com/watch?v=1TV0q4Sdxlc"
  #     And I press "Next"
  #     And I wait for "2" seconds
  #     And I press "Finish"
    #   And I press "Save"
    # Then I should see "Testing video"

  @api @javascript
  Scenario: Adds "New Spotlight Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add spotlight"
      And I fill in "field_basic_spotlight_items[und][0][title]" with "First spot"
      And I fill in "field_basic_spotlight_items[und][0][link]" with "http://demo.getdkan.com"
      And I attach the file "dkan_logo.png" to "files[field_basic_spotlight_items_und_0_fid]"
      And I press "Finish"
      And I press "Save"
      Then I should see "First spot" in the "content"

  @api @javascript
  Scenario: Adds "New Submenu Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add submenu"
      And I press "Finish"
      And I press "Save"
      Then I should see "About" in the "content"

  @api @javascript
  Scenario: Adds "New Content List Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add content list"
    When I select "Dataset" from "exposed[type]"
      And I select "Asc" from "exposed[sort_order]"
      And I select "Title" from "exposed[sort_by]"
      And I press "Finish"
      And I press "Save"
    Then I should see "Afghanistan Election Districts"
      And I should see "Posted by admin"

  @api @javascript
  Scenario: Adds "New Content Item Widget" block to home page using panels ipe editor
    Given I am on the homepage
      Then I should see "Customize this page"
    When I click "Customize this page"
      And I click "Add new pane"
      Then I should see "Please select a category from the left"
    When I click on the text " Add content item"
    When I select "Resource" from "exposed[type]"
      And I fill in "exposed[title]" with "District Names"
      And I press "Finish"
      And I wait for "3" seconds
      And I press "Save"
    Then I should see "District Names"
      And I should see "Posted by admin"