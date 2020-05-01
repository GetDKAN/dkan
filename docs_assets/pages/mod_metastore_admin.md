@page admin Metastore Administration

The **Metastore Administration** module provides administrative views for managing dataset content.

The default `admin/content/node` view will be replaced with DKAN's `admin/content/node` view that:
  1. Adds an additional filter for "Data type"
  2. The edit links of dataset-type data nodes will link to the standard node form
  3. The title links of dataset-type data nodes will link to the REACT datasaet page if [Frontend](docs/frontend.html) is enabled.

A dataset specific view is also added at `admin/content/dataset`.
  1. The edit links of dataset-type data nodes will link to the REACT [Metastore Form](docs/form.html) app
