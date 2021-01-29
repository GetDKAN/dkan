@page frontend Decoupled Frontend

## DKAN Frontend
This is an integration module that allows the React app driving the frontend to be embedded in Drupal. It will set the front page to use ``/home`` to serve the app.

The page routes are defined in `frontend/config/install/frontend.config.yml`

## [Data Catalog App](https://github.com/GetDKAN/data-catalog-app)

This is a React app that will use the [Data Catalog Components](https://github.com/GetDKAN/data-catalog-components) library to build the frontend. It will serve as a starting point for your own customizations. If you are **not** using DKAN Tools, follow the instructions on the [README file](https://github.com/GetDKAN/data-catalog-app/blob/master/README.md) for manual installation.
