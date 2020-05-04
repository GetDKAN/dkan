@page admin Administration

The **ground_control** module provides administrative views for managing dataset content.

The default `admin/content/node` view will be replaced with DKAN's `admin/content/node` view that:
  1. adds an additional filter for "Data type"
  2. overrides the edit links of dataset-type data nodes to use the REACT metadata editor app

A dataset specific view is also added at `admin/content/dataset`.
