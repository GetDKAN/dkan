# Behat DKAN Context

This creates a feature context for DKAN specific steps.

## Install

1. Create a ``composer.json`` file with the following:

```json
{
  "require": {
    "nucivic/dkanextension": "dev-master"
  },
  "config": {
    "bin-dir": "bin/"
  },
}
```

2. Install dependencies: ``composer install``

3. Initialize: ``behat --init``

## Contexts

DKANExtension ships with a bunch of "Contexts" or Classes that Behat uses to add step functions or other functionality. By default, none of those contexts are loaded. You need to add each context that you want to use to your `behat.yml` file. Here is an example where we add all the contexts, but you can instead choose to only load the ones you want. This can be very useful if you want to override one of these contexts and use your custom version instead. If you've used the DrupalExtension, which DKANExtension depends on, it works the same way.

### Setting up your behat.yml

```Yaml
default:
  suites:
     default:
        contexts:
          - FeatureContext
          # Load the Drupal Context from DrupalExtension
          - Drupal\DrupalExtension\Context\DrupalContext
          # Load the generic DKAN context
          - Drupal\DKANExtension\Context\DKANContext
          # Load DKAN Groups functionality
          - Drupal\DKANExtension\Context\GroupContext
          # Load DevinciExtension debug functionality
          - Devinci\DevinciExtension\Context\DebugContext:
              asset_dump_path: %paths.base%/../assets/
          # Set the default max wait time when testing JS "wait for.." tests.
          - Devinci\DevinciExtension\Context\JavascriptContext:
              maximum_wait: 30
...
```

### Contexts Available

All contexts are in the src/Drupal/DKANExtension/Context folder. We're only showing the contexts that you'll consider loading in your behat.yml and only the custom steps they provide.

#### DKANContext.php
The generic context that holds some helper steps

* Sets the default php timezone to `date_default_timezone_set('America/New_York');`
* `@When I search for :term`
* `@Then /^I should see the administration menu$/`
* `@Then /^I should have an "([^"]*)" text format option$/`

#### DKANDataStoryContext.php
[Extends RawDKANEntityContext](#about-rawdkanentitycontext)

* `@Given data stories:`

  ```
  Data Story Field Mappings:
  'title' => 'title' - Title of the data story (string)
  'author' => 'author' - Author of the data story (username)
  'status' => 'status', - Is this published or not ('Yes', 'No')
  'description' => 'body' - Description of the data story (string)
  'tags' => 'field_tags' - Tags (comma separated strings)
  ```

  **Examples:**

  ```Cucumber
  Given data stories:
    | author    | title     | description       | status | tags     |
    | TestUser  | Story 01  | Some Description  | Yes    | some tag |
  And I am on the "Story 01" page
  ```

#### DataDashboardContext.php
[Extends RawDKANEntityContext](#about-rawdkanentitycontext)

* `@Given data dashboards:`

  ```
  Dashboard Field Mappings:
  'title' => 'title' - Title of the Data Dashboard (string)
  ```

  **Examples:**
  ```Cucumber
  Given data dashboards:
    | title         |
    | Dashboard 01  |
  ```

#### DatasetContext.php
[Extends RawDKANEntityContext](#about-rawdkanentitycontext)

* `@Given datasets:`

  ```
  Dataset Field Mappings:
  'author' => 'author' - author of the Dataset (username)
  'title' => 'title' - Dataset title (string)
  'description' => 'body' - Dataset description (string)
  'publisher' => 'og_group_ref' - Group name (string)
  'published' => 'status' - Published? ('Yes', 'No')
  'tags' => 'field_tags' - Tags to add (comma separated strings)
  ```

  **Examples:**

  ```Cucumber
  Given groups:
    | author    | title     | published     |
    | TestUser  | Group 01  | Yes           |
  Given datasets:
    | author    | title       | description       | publisher  | published | tags     |
    | TestUser  | Dataset 01  | Some Description  | Group 01   | Yes       | some tag |
  And I am on the "Dataset 01" page
  ```

* `@Then I should see a dataset called :text`

  - `:text` - Title of the dataset (string)

  **Examples:**

  ```Cucumber
  Given groups:
    | author    | title     | published     |
    | TestUser  | Group 01  | Yes           |
  Given datasets:
    | author    | title       | description       | publisher  | published | tags     |
    | TestUser  | Dataset 01  | Some Description  | Group 01   | Yes       | some tag |
  And I am on the "Group 01" page
  Then I should see a dataset called "Dataset 01"
  ```

* `@Then the Dataset search updates behind the scenes`

  (Deprecated)

**Handling required fields from custom deployments or when enabling ODFE**
Using the default build of DKAN, there is only one required field for datasets which is the title. If you enable Open Data Federal Extras, or add custom required fields to the dataset form, we need to account for these new fields in the tests that create and edit datasets.
We use profile and suite level controls to combine our default and custom behat configuration as described here: [Behat Setup](https://github.com/NuCivic/dkan_starter/blob/master/docs/docker-dev-env/behat-setup.rst)

Although this technique allows for some level of composition it does not actually allow for custom parameter configurations to be passed into the default context configuration. This state is problematic because we cannot easily adjust the behavior of a dkan test against custom context configurations.

So we need a way to pass custom parameters into the default context. Currently we are adding required fields directly to [DatasetContext.php](https://github.com/NuCivic/dkan/pull/1963/files#diff-c2f41d7be2fa9d3ff5ed50a75faabb1eR19)

In the near future, we want to introduce a build step that can merge our custom parameters into the the default behat.yml file in a similar fashion to the way we now merge the upstream config/config.yml to the site specific config/cofig.yml file. So running `ahoy build config` should would apply any custom parameters set in the `config/cofig.yml` into the `dkan/test/behat.yml` context configuration. See [dkan_starter issue](NuCivic/dkan_starter#332).


#### GroupContext.php
[Extends RawDKANEntityContext](#about-rawdkanentitycontext)

* `@Given groups:`

  ```
  Group Field Mappings:
  'author' => 'author' -  Author of the Group (username)
  'title' => 'title' - Group Name (string)
  'published' => 'status' - Published? ('Yes', 'No')
  ```

  **Examples:**

  ```Cucumber
  Given groups:
    | author    | title     | published     |
    | TestUser  | Group 01  | Yes           |
  And I am on the "Group 01" page
  ```

* `@Given group memberships:`

  *Assigns users as members of a group with specific roles.*

  ```
  Table fields:
  'user' - (username)
  'group' - Group Name (string)
  'role on group' - ('administrator member', 'member')
  'membership status' - What is the user's group status? ('Active')
  ```

  **Examples:**

  ```Cucumber
  Given groups:
    | author    | title     | published     |
    | TestUser  | Group 01  | Yes           |
  Given group memberships
    | user       | group     | role on group        | membership status |
    | TestUser   | Group 01  | administrator member | Active            |
  ```

* `@Given /^I am a "([^"]*)" of the group "([^"]*)"$/`

  *Grants the given role to the current user, for the given group.*

  - `:role` - role on group. See `@Given group memberships:`
  - `:group` - Group name (string)

  **Examples:**

  ```Cucumber
  Given groups:
    | author    | title     | published     |
    | TestUser  | Group 01  | Yes           |
  And I am a "administrator member" of the group "Group 01"
  ```

#### MailContext.php

* Creates the @mail tag for use when testing email notifications in your scenario

(currently no additinal steps)

#### PageContext.php

* `@Given pages:`

  ```
  Table Fields:
  title - Page Name (string) -- Must be unique per scenario.
  url - Relative Path ('/my-page') or full url ('https://facebook.com')
  ```
  **Examples:**

  ```Cucumber
  @Given pages:
    | title        | url    |
    | Content List | /node  |
  ```

* `@Given I am on (the) :page page`

  *Changes the current page. Knows about pages created by other contexts, like GroupContext as well.*

  - `:page` - title of the page created by `@Given Pages` or one of the other contexts that integrate with PageContext.

  **Examples:**

  Manually Created Pages:
  ```Cucumber
  Given pages:
    | title        | url    |
    | Content List | /node  |
  And I am on the "Content List" page
  Then I should see "Content"
  ```

  Use pages created by other contexts.
  ```Cucumber
  Given groups:
    | author    | title     | published     |
    | TestUser  | Group 01  | Yes           |
  And I am on the "Group 01" page
  ```

#### ResourceContext.php
[Extends RawDKANEntityContext](#about-rawdkanentitycontext)

* `@Given resources:`

  ```
  Resource Field Mappings:
  'author' => 'author' - Username of user (string)
  'title' => 'title' - Resource title (string)
  'description' => 'body' - Resource Description (string)
  'publisher' => 'og_group_ref' - Group name (string)
  'published' => 'status' - ('Yes', 'No')
  'resource format' => 'field_format' - ('CSV', 'XML')
  'dataset' => 'field_dataset_ref' - Dataset name (string)
  ```

## About RawDKANEntityContext

RawDKANEntityContext is a base Class that the other Content/Entity contexts extend from. It can be used to easily create new contexts for content that have a following features:

- Automatic Deletion of content on teardown.
- Handles any entity (nodes+)
- Creates "pages" in the PageContext so that you can navigate to an item after it's created.
- Handles mapping of "human" field labels to drupal field names and handles adding entities with Tables.
- Handles author mapping of username to uid.
- Uses entity api metadata wrappers throughout, making it easier to set fields in a consistent way.
- Keeps a list of created entities in $entities[$id] for teardown and other uses like finding an entity by name.

You should use the existing "entity contexts", or create a new one for your project instead of the DrupalExtension's `@Given content:` which lacks most of these features. Because the entity api needs to be bootstrapped, you can only use the 'drupal driver', not 'drush' or 'blackbox' when using relevant steps.

### Creating a new Entity Context

To create a new Context for any entity (and nodes), create a new context in your /bootstrap folder. For this example, we'll pretend the entity is a node type called "myentity". Call the file MyEntityContext.php (where MyEntity is the name of your entity.) You can use this template to get started.
```Php
<?php
namespace Drupal\DKANExtension\Context;
use Behat\Gherkin\Node\TableNode;
/**
 * Defines application features from the specific context.
 */
class MyEntityContext extends RawDKANEntityContext{

  public function __construct(){
    parent::__construct(array(
      // These are the mappings of human readable name => drupal field machine name or entity property name.
      'title' => 'title',
      'author' => 'author',
      'myfield' => 'field_my_field'
    ),
      // This is the bundle name if one exists, or null if one doesn't.
      // for nodes bundle == node_type name
      'myentity',
      // This is the entity type.
      'node'
    );
  }

  /**
   * @Given myentities:
   */
  public function addDataDashboard(TableNode $dashboardtable){
    //This is an example of calling parent helper functions.
    parent::addMultipleFromTable($dashboardtable);
  }

  /**
   * A contrived exmple of hooking into the Entity helpers by overriding them.
   *
   * Set all of the entities to be unpublished before they're created, no matter what.
   */
  public function wrap($entity){
    $wrapper = parent:wrap($wrap);
    $wrapper->status = 0;
    return $wrapper;
  }


  /**
   * @Given myentities:
   *
   * All custom entities will almost all want to add this type of function.
   */
  public function addDataDashboard(TableNode $dashboardtable){
    //This is an example of calling parent helper functions.
    parent::addMultipleFromTable($dashboardtable);
  }

  /**
   * @Given there should be ":number" MyEntities.
   *
   * An example showing how you can creat new step functions and get the entities array from the parent class.
   */
  public function assertNumberEntities($number){
    $actual = count($this->entities);
    if ($number !== count($this->entities) {
      throw new Exception("Asserted that there are $number myEntites, but there are actually $actual.");
    }
  }
}
```

Now add the new Entity Context to your behat.yml like you'd do for any new custom context:

```Yaml
default:
  suites:
     default:
        contexts:
          - FeatureContext
          # Add the new entity context
          - MyEntityContext
...
```


## TODO

- [ ] Make sure scripts works on install
- [ ] Add tests
- [ ] Deploy on DKAN and related modules
