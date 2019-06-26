DKAN Periodic Updates
=====================

The DKAN Periodic Updates module provides the user the ability to execute automated updates on existing resources on a daily, weekly or monthly basis. It also provides a UI in which you can check at the status of the updates.
The module requires a CSV file to execute the updates, this file is known as "the manifest", when you upload it and enable the updates, this is what happens:

- The manifest is evaluated on every cron run to determine which resources need to be updated (based on the frequency defined in the manifest and the "last update" date).
- For each resource that needs an update, the system will:
  - Download the file URL specified in the manifest (or link it, according to which file field is used on the corresponding resource).
  - Update the resource node to use the new file.
  - If the file is a CSV or TSV, then the resource is imported into the datastore.

Periodic Updates Settings
-------------------------

You can find the periodic updates settings under DKAN --> Periodic Updates --> Settings (`/admin/dkan/periodic-updates`).
The settings for this module include:

  - Periodic updates state
  - Manifest for periodic updates

1- Periodic updates state
*************************

Enable periodic updates by checking the "Enable periodic updates" box on the Periodic Updates page, and click on "Save settings". You'll also need to upload a valid manifest in the section "Manifest for periodic updates", otherwise the updates cannot be executed.
If you already have a manifest in the system but need to pause the periodic updates, all you need to do is uncheck the option "Enable periodic updates" and save settings.

2- Manifest for periodic updates
********************************

The manifest is a CSV file in which you define all of the resources that need to be updated periodically in the system, this file should contain the following column names:

``resource_id,frequency,file_url``

:resource_id: is the UUID related to the resource node.
:frequency: it can be set to `daily`, `weekly` or `monthly`. If you leave it empty or put any other string, then the system asumes it has to be imported daily.
:file_url: the URL of the remote file that needs to be downloaded or linked and saved to the node.

Once you upload the manifest and click on "Add file", the file will be recognized by the system.

Note: for the periodic updates to be executed you need to have uploaded a valid manifest and enabled the periodic updates. If you only have one of these items, the updates will not be executed.

Periodic Updates Status
-------------------------

The status page can be found under DKAN --> Periodic Updates --> Status (`/admin/dkan/periodic-updates/status`). In this page you'll get information about the resources specified in the manifest.
If the "Enable periodic updates" checkbox from Settings is not marked, then you'll get a message saying "Periodic updates are disabled.". If it is marked but no manifest has been uploaded, then you'll get a message saying "No manifest was found.".
If the updates are enabled and a valid manifest is uploaded, then you'll see a list of the elements included in the manifest file, that list includes:

  - the Destination Resource ID: this shows the resource UUID specified in the manifest but it is also a link to the resource node when applicable,
  - the Source File URL,
  - the Update frequency,
  - the Status: this represents the messages generated on each update, it will tell you wether the resource has been updated, if the process finished correctly or if/what errors were produced,
  - the Last update date: this is the date in which the resource was last updated via periodic updates.
