.. _frontend:

Decoupled Front-end
===================

DKAN2 uses a `React front-end <https://github.com/GetDKAN/data-catalog-frontend>`_ built with `GastbyJS <https://www.gatsbyjs.org/>`_.

The `frontend components <https://github.com/GetDKAN/data-catalog-components>`_ can be viewed here: https://GetDKAN.github.io/data-catalog-components

Using the App
-------------

DKAN comes with an integration module that allows the React App driving the frontend to be embedded in Drupal.

To get the integration working follow these steps:

- Place the source for the `data-catalog-frontend <https://github.com/GetDKAN/data-catalog-frontend>`_ inside of your ``docroot`` directory.
- Configure the ``.env.development`` and ``.env.production`` files to point to the backend urls.
- Install the dependencies with `npm <https://www.npmjs.com/>`_: ``cd data-catalog-frontend`` and ``npm install``
- Build: ``npm run build``
- Enable the integration module ``drush en dkan_frontend``
- Change the sites configuration to point the homepage (``/``) to ``/home``
