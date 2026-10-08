# DKAN Datastore Preview

Renders a paginated, sortable HTML table of datastore data for each CSV/TSV distribution on the server-rendered dataset page (`/node/{nid}`). The React frontend (`/dataset/{uuid}`) is unaffected.

Enable with `drush en dkan_datastore_preview`. Install adds a **Data Preview** extra field to the `data` node type's default view display; toggle or reorder it under *Structure → Content types → Data → Manage display*. The `node--data.html.twig` template in `dkan_metastore` prints it as `{{ content.data_preview }}`.

## Behavior

* One table per importable distribution, captioned with the source filename. Non-tabular distributions (PDF, ZIP, ...) render nothing.
* Sorting, page size, and paging are plain links and a GET form; no JavaScript. Each table on a page uses its own query parameters (`dp0_order`, `dp0_sort`, `dp0_page_size`; `dp1_…` for the second table) so tables don't affect each other. Sort columns are validated against the table schema.
* If a distribution's datastore table does not exist yet, a status message renders instead ("still being processed" while the fetch/import is queued or running, an error message if the import failed). The preview carries the dataset and distribution cache tags, so it switches to the table automatically once the import's post-processing invalidates them.

## Limitations

* Text columns sort lexically (`"10" < "9"`) because the datastore stores CSV values as strings unless a data dictionary types them.
* No block placement yet; the preview is only available through the node display.
* The `dkan.datastore_preview.data_source.database` service is the only data source. `DataSourceInterface` is the extension point for future sources.
