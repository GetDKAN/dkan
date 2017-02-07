# Background and Periodic Imports

By default Resource files are added to the DKAN Datastore manually. This can be changed to:

*   Import upon form submission
*   Import in the background
*   Import periodically

### Changing Default Datastore Import Behavior

Default behavior for linked and uploaded files is controlled through the [Feeds module](http://dgo.to/feeds). To access the Feeds administrative interface, enabled the **Feeds Admin UI** module which is included but not enabled by default in DKAN. Once turned on you can access the Feeds UI at /admin/structure/feeds. The are two Feeds Importers by default: ![feeds ui](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-07-31%20at%202.56.30%20PM.png)

### Import upon form submission

To import a Resource file upon saving the resource, click **Import on Form Submission** in the settings section for each importer: ![import on submission](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-07-31%20at%202.58.34%20PM.png) 

This is not recommended for large imports as a batch screen will be triggered that will not stop until the entire file is imported.

### Import in the background

This setting means that once an import has started, it will be processed in 50 row increments in the background. Processing will occur during cron. The queue of imports is managed by the [Job Schedule](http://dgo.to/job_scheduler) module. Each cron run will [process a maximum of 200 jobs in a maximum of 30 seconds](http://cgit.drupalcode.org/job_scheduler/tree/job_scheduler.module?id=7.x-2.0-alpha3#n54). Note that an import won't be started by saving the Resource form. This will only be triggered by clicking "Import" on the "Manage Datastore" page or if triggered programatically. This setting can be used in addition to "Import on for submission" to start imports that will be imported in the background.

### Import periodically

This setting imports items on a periodic basis. This makes the most sense if you have a file you are linking to that you want to periodically re-import. This setting is also reliant on cron running properly.