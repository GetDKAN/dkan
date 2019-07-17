.. _frontend:

Decoupled Front-end
===================

DKAN2 uses a `React front-end <https://github.com/interra/data-catalog-frontend>`_ built with `Create React App <https://github.com/facebook/create-react-app>`_.

The `frontend components <https://github.com/interra/data-catalog-components>`_ can be viewed here: https://interra.github.io/data-catalog-components

Using the App
-------------

DKAN comes with an integration module that allows the React App driving the frontend to be embedded in Drupal.

To get the integration working follow these steps:

- Place the source for the Interra `data-catalog-frontend <https://github.com/interra/data-catalog-frontend>`_ inside of your ``docroot`` directory.
- Configure the ``.env.development`` and ``.env.production`` files to point to the backend urls.
- Install the dependencies with `npm <https://www.npmjs.com/>`_: ``cd data-catalog-frontend`` and ``npm install``
- Build: ``npm run build``
- Enable the integration module ``drush en interra_frontend``
- Change the sites configuration to point the homepage (``/``) to ``/home``


Available Scripts
-----------------

In the project directory, you can run:

.. code-block::

    npm start

Runs the app in the development mode.

Open `http://localhost:3000 <http://localhost:3000>`_ to view it in the browser.

The page will reload if you make edits.

You will also see any lint errors in the console.

.. code-block::

    npm test

Launches the test runner in the interactive watch mode.

See the section about `running tests <https://facebook.github.io/create-react-app/docs/running-tests>`_ for more information.

.. code-block::

    npm run build

Builds the app for production to the ``build`` folder.

It correctly bundles React in production mode and optimizes the build for the best performance.

The build is minified and the filenames include the hashes.

Your app is ready to be deployed!

See the section about `deployment <https://facebook.github.io/create-react-app/docs/deployment>`_ for more information.

.. code-block::

    npm run eject

.. note::

    this is a one-way operation. Once you `eject`, you can’t go back!

If you aren’t satisfied with the build tool and configuration choices, you can `eject` at any time. This command will remove the single build dependency from your project.

Instead, it will copy all the configuration files and the transitive dependencies (Webpack, Babel, ESLint, etc) right into your project so you have full control over them. All of the commands except `eject` will still work, but they will point to the copied scripts so you can tweak them. At this point you’re on your own.

You don’t have to ever use `eject`. The curated feature set is suitable for small and middle deployments, and you shouldn’t feel obligated to use this feature. However we understand that this tool wouldn’t be useful if you couldn’t customize it when you are ready for it.

Learn More
----------

You can learn more in the `Create React App documentation <https://facebook.github.io/create-react-app/docs/getting-started>`_ .

To learn React, check out the `React documentation <https://reactjs.org/>`_

- `Code Splitting <https://facebook.github.io/create-react-app/docs/code-splitting>`_
- `Analyzing the Bundle Size <https://facebook.github.io/create-react-app/docs/analyzing-the-bundle-size>`_
- `Making a Progressive Web App <https://facebook.github.io/create-react-app/docs/making-a-progressive-web-app>`_
- `Advanced Configuration <https://facebook.github.io/create-react-app/docs/advanced-configuration>`_
- `Deployment <https://facebook.github.io/create-react-app/docs/deployment>`_
- `npm run build fails to minify <https://facebook.github.io/create-react-app/docs/troubleshooting#npm-run-build-fails-to-minify>`_