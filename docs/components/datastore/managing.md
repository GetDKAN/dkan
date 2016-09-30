# Managing and Importing Files to the Datastore

If you have successfully created a dataset with resources, you now have data in DKAN which you can display and store in several ways. However, DKAN is still reading this data directly from the file or API you added as a resource. 

To get the fullest functionality possible out of your datasets, including a public API that can be used to develop 3rd party applications, you must complete the final step of adding your resources to DKAN's own datastore. (At the moment, a DKAN datastore is simply a table in the main database.) 

If you are exploring a resource that is not yet in the datastore, you will see a message advising you of this. Click the "Manage Datastore" button at the top of the screen. On the "Manage Datastore" page, use the "Import" button at the bottom of the page to import the data from your file or API into DKAN's local datastore.

#### Notification to import resource to datastore:

 ![Manage Datastore: Notification](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-04-03%20at%206.19.09%20PM.png) 
 
#### Importing the resource:

 ![Manage Datastore: Import](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-04-03%20at%206.19.26%20PM.png)
 
#### Notification of a successful import:

 ![Manage Datastore: Success](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202015-04-03%20at%206.19.53%20PM.png) Your data is now ready to use via the API! Click the "Data API" button at the top of the resource screen for specific instructions. DKAN datastores can also be created and updated from files using the following Drush commands: 

To create a datastore from a local file: `drush dsc (path-to-local-file)` 

To update a datastore from a local file: `drush dsu (datastore-id) (path-to-local-file)` 

To delete a datastore file (imported items will be deleted as well): `drush dsfd (datastore-id)` To get the URL of the datastore file: `drush dsfuri (datastore-id)`