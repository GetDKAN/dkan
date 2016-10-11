# Open Data Schema Mapper File Cache Endpoints

The Open Data Schema Map module now defines a drush command called `odsm-filecache`.  This command takes as  its argument the machine name for an ODSM endpoint.  For example:

```
drush odsm-filecache data_json_1_1;
```

The above command triggers the processing for the endpoint defined for the data_json_1_1 ODSM API and results in the following cached file being generated on completion:

```
public://odsm_cache_data_json_1_1
```

In order to enable the cached version of an API endpoint you need to run the command above replacing `data_json_1_1` with
the machine  name of the ODSM endpoint to be cached.

In order to update this cache you need to re-run the command that generated it.

We recommend you set up a cron job to run the command on a regular schedule, perhaps in sync with your data harvesting schedule.