Feature: Homepage
  In order to know all the features provided by Dkan
  As a website user
  I need to be able to see default content

  @api
  Scenario: All default group content should be present
    Given I am logged in as a user with the "administrator" role
    When I am on "/admin/content"
    Then I should see "Committee on International Affairs"
    And I should see "State Economic Council"
    And I should see "Wisconsin Parks and Rec Commission"
    And I should see "Advisory Council for Infectious Disease"

  @api
  Scenario: All default resources should be present
    Given I am logged in as a user with the "administrator" role
    When I am on "/admin/content"
    Then I should see "Table of Gold Prices"
    And I should see "Tobacco Taxation by State as of April 2016"
    And I should see "Varicella Incidence Rates After Vaccine Introduced in U.S., 1995-2009"
    And I should see "U.S. Adult Smoking Rate"
    And I should see "LA Metro GTFS Data March 2016"
    And I should see "Long Island Rail Road GTFS Data"
    And I should see "Retirements (2011 - 2015)"
    And I should see "Varicella Mortality by Age Group, 1995-2009"
    And I should see "Varicella Mortality by Age Group, 1970-1994 (High-Risk vs. Otherwise Healthy Individuals)"
    And I should see "Violent Crime Data for the Ten Most Populous Cities in the U.S."
    And I should see "LA Metro GTFS Data February 2016"
    And I should see "District Names"
    And I should see "1-2012-Foreclosures-by-State"
    And I should see "Varicella Mortality by Age Group, 1970-1994"
    And I should see "Workforce By Generation (2011-2015)"
    And I should see "Varicella Incidence by Age Group, 1995-2009"
    And I should see "Madison, WI Street Trees"
    And I should see "Property Crime Data for the Ten Most Populous Cities in the U.S."
    And I should see "Madison Polling Places"
    And I should see "Retirements: Eligible vs. Actual"
    And I should see "English Indices of Deprivation 2010"

  @api
  Scenario: All default datasets should be present
    Given I am logged in as a user with the "administrator" role
    When I am on "/admin/content"
    Then I should see "Afghanistan Election Districts"
    And I should see "Crime Data for the Ten Most Populous Cities in the U.S."
    And I should see "Gold Prices in London 1950-2008 (Monthly)"
    And I should see "LA Metro GTFS Data"
    And I should see "London Deprivation Index"
    And I should see "Long Island Railroad GTFS Data"
    And I should see "Madison Street Trees"
    And I should see "State Workforce by Generation (2011-2015)"
    And I should see "US National Foreclosure Statistics January 2012"
    And I should see "U.S. Tobacco Usage Statistics"
    And I should see "Varicella (Chickenpox) Incidence and Mortality, Before and After the Vaccine"
    And I should see "Wisconsin Polling Places"

  @api
  Scenario: All default content should be removed if the module is disabled
    Given I am logged in as a user with the "administrator" role
    When I disable the module "dkan_fixtures_default"
    When I am on "/admin/content"
    # I should not see the default groups.
    Then I should not see "Committee on International Affairs"
    And I should not see "State Economic Council"
    And I should not see "Wisconsin Parks and Rec Commission"
    And I should not see "Advisory Council for Infectious Disease"
    # I should not see the default resources
    And I should not see "Table of Gold Prices"
    And I should not see "Tobacco Taxation by State as of April 2016"
    And I should not see "Varicella Incidence Rates After Vaccine Introduced in U.S., 1995-2009"
    And I should not see "U.S. Adult Smoking Rate"
    And I should not see "LA Metro GTFS Data March 2016"
    And I should not see "Long Island Rail Road GTFS Data"
    And I should not see "Retirements (2011 - 2015)"
    And I should not see "Varicella Mortality by Age Group, 1995-2009"
    And I should not see "Varicella Mortality by Age Group, 1970-1994 (High-Risk vs. Otherwise Healthy Individuals)"
    And I should not see "Violent Crime Data for the Ten Most Populous Cities in the U.S."
    And I should not see "LA Metro GTFS Data February 2016"
    And I should not see "District Names"
    And I should not see "1-2012-Foreclosures-by-State"
    And I should not see "Varicella Mortality by Age Group, 1970-1994"
    And I should not see "Workforce By Generation (2011-2015)"
    And I should not see "Varicella Incidence by Age Group, 1995-2009"
    And I should not see "Madison, WI Street Trees"
    And I should not see "Property Crime Data for the Ten Most Populous Cities in the U.S."
    And I should not see "Madison Polling Places"
    And I should not see "Retirements: Eligible vs. Actual"
    And I should not see "English Indices of Deprivation 2010"
    # I should not see the default datasets
    And I should not see "Afghanistan Election Districts"
    And I should not see "Crime Data for the Ten Most Populous Cities in the U.S."
    And I should not see "Gold Prices in London 1950-2008 (Monthly)"
    And I should not see "LA Metro GTFS Data"
    And I should not see "London Deprivation Index"
    And I should not see "Long Island Railroad GTFS Data"
    And I should not see "Madison Street Trees"
    And I should not see "State Workforce by Generation (2011-2015)"
    And I should not see "US National Foreclosure Statistics January 2012"
    And I should not see "U.S. Tobacco Usage Statistics"
    And I should not see "Varicella (Chickenpox) Incidence and Mortality, Before and After the Vaccine"
    And I should not see "Wisconsin Polling Places"
    # Enable the module again for the rest of the tests
    Then I enable the module "dkan_fixtures_default"