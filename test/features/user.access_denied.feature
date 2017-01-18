# time:2m5.09s
@api
Feature: Users with Editor, Content Creator or Site Manager roles should not have access to administration pages.

  Background:
    Given pages:
      | name                                             | url                                                              |
      # Entity type related pages.
      | add entity type                                  | /admin/structure/entity-type/add                                 |
      # Visualization Entity related pages.
      | edit visualization entity                        | /admin/structure/entity-type/visualization/edit                  |
      | delete visualization entity                      | /admin/structure/entity-type/visualization/delete                |
      | manage visualization entity                      | /admin/structure/entity-type/visualization/properties            |
      # Visualization Entity Charts bundle related pages.
      | add visualization bundle                         | /admin/structure/entity-type/visualization/add                   |
      | delete visualization chart bundle                | /admin/structure/entity-type/visualization/ve_chart/delete       |
      | edit visualization chart bundle                  | /admin/structure/entity-type/visualization/ve_chart/edit         |
      | manage visualization chart bundle fields         | /admin/structure/entity-type/visualization/ve_chart/fields       |
      | manage visualization chart bundle display        | /admin/structure/entity-type/visualization/ve_chart/display      |
      # Content type related pages.
      | add content types                                | /admin/structure/types/add                                       |
      # Dkan Data Story content type related pages.
      | edit dkan_data_story content type                | /admin/structure/types/manage/dkan-data-story                    |
      | delete dkan_data_story content type              | /admin/structure/types/manage/dkan-data-story/delete             |
      | manage dkan_data_story content type fields       | /admin/structure/types/manage/dkan-data-story/fields             |
      | manage dkan_data_story content type display      | /admin/structure/types/manage/dkan-data-story/display            |
      # Data Dashboard content type related pages.
      | edit data_dashboard content type                 | /admin/structure/types/manage/data_dashboard                     |
      | delete data_dashboard content type               | /admin/structure/types/manage/data_dashboard/delete              |
      | manage data_dashboard content type fields        | /admin/structure/types/manage/data_dashboard/fields              |
      | manage data_dashboard content type display       | /admin/structure/types/manage/data_dashboard/display             |
      # Dataset content type related pages.
      | edit dataset content type                        | /admin/structure/types/manage/dataset                            |
      | delete dataset content type                      | /admin/structure/types/manage/dataset/delete                     |
      | manage dataset content type fields               | /admin/structure/types/manage/dataset/fields                     |
      | manage dataset content type display              | /admin/structure/types/manage/dataset/display                    |
      # Resource content type related pages.
      | edit resource content type                       | /admin/structure/types/manage/resource                           |
      | delete resource content type                     | /admin/structure/types/manage/resource/delete                    |
      | manage resource content type fields              | /admin/structure/types/manage/resource/fields                    |
      | manage resource content type display             | /admin/structure/types/manage/resource/display                   |
      # Group content type related pages.
      | edit group content type                          | /admin/structure/types/manage/group                              |
      | delete group content type                        | /admin/structure/types/manage/group/delete                       |
      | manage group content type fields                 | /admin/structure/types/manage/group/fields                       |
      | manage group content type display                | /admin/structure/types/manage/group/display                      |

  Scenario Outline:: Users with Editor, Content Creator or Site Manager roles should not have access to administration pages
    Given I am logged in as a user with the "content creator" role
    Then I should be denied access to the "<administration page>" page
    Given I am logged in as a user with the "editor" role
    Then I should be denied access to the "<administration page>" page
    Given I am logged in as a user with the "site manager" role
    Then I should be denied access to the "<administration page>" page
  Examples:
    | administration page                              |
    # Entity type related pages.
    | add entity type                                  |
    # Visualization Entity related pages.
    | edit visualization entity                        |
    | delete visualization entity                      |
    | manage visualization entity                      |
    # Visualization Entity Charts bundle related pages.
    | add visualization bundle                         |
    | delete visualization chart bundle                |
    | edit visualization chart bundle                  |
    | manage visualization chart bundle fields         |
    | manage visualization chart bundle display        |
    # Content type related pages.
    | add content types                                |
    # Dkan Data Story content type related pages.
    | edit dkan_data_story content type                |
    | delete dkan_data_story content type              |
    | manage dkan_data_story content type fields       |
    | manage dkan_data_story content type display      |
    # Data Dashboard content type related pages.
    | edit data_dashboard content type                 |
    | delete data_dashboard content type               |
    | manage data_dashboard content type fields        |
    | manage data_dashboard content type display       |
    # Dataset content type related pages.
    | edit dataset content type                        |
    | delete dataset content type                      |
    | manage dataset content type fields               |
    | manage dataset content type display              |
    # Resource content type related pages.
    | edit resource content type                       |
    | delete resource content type                     |
    | manage resource content type fields              |
    | manage resource content type display             |
    # Group content type related pages.
    | edit group content type                          |
    | delete group content type                        |
    | manage group content type fields                 |
    | manage group content type display                |
