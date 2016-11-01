# Revision History for Datasets and Resources

DKAN Datasets and Resources track revisions in order to log and display changes. Revision tracking is turned on by default for the dataset and resource content types.

### User Interface

Revision log entries can be added through the user interface by clicking "Revision information" in the dataset or resource edit form and can be viewed by clicking "Revisions" on a Dataset or Resource page: 

![adding revisions](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-04-14%20at%202.36.03%20PM.png) ![viewing revisions](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-04-14%20at%202.30.29%20PM.png)

### Loading Revision information Programmatically

Revision comments generated in code can be viewed by loading a Dataset or Resource and viewing the log: `$node = node_load('dataset node id'); echo $node->log`

### Revision List API

A list of recent revisions are available through the revision_list API at "/api/3/action/revision_list"

### File Revisions

Diffs of individual files are not available by default but could be added using this or other similar strategy: https://drupal.org/node/2101377