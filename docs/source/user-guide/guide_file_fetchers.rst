Implementing a custom file fetcher
----------------------------------

DKAN uses a library called getdkan/file-fetcher. This library allows developers to extend the file transfer functionality for their specialized needs.

This library is used to download a resource, such as a CSV file, so that it can be loaded into the database and presented through the UI and API. This process is called 'localization,' because the source resource is copied to the local file system.

The standard file fetcher processors will probably be adequate for most uses, but there could be other use cases, such as needing to authenticate, or getting a file from S3 instead of HTTP.

TL;DR:
======

- A code example can be found in the ``custom_processor_test`` module, which is used to test this functionality.
- Implement ``FileFetcher\Processor\ProcessorInterface`` as a custom processor for ``FileFetcher``.
- Create a ``FileFetcherFactory`` class which instantiates a ``FileFetcher`` using configuration specifying the new processor in the processors array.
- Specify this new ``FileFetcherFactory`` as a service which decorates ``dkan.common.file_fetcher``.

How to:
=======

To implement a new file processor, a custom file-fetcher processor class should be created, implementing ``FileFetcher\Processor\ProcessorInterface``. This class could subclass ``FileFetcher\Processor\Remote`` or ``FileFetcher\Processor\Local``, or be a new implementation.

A ``FileFetcherFactory`` class should then be created. It should implement ``Contracts\FactoryInterface``. The new factory should configure ``FileFetcher`` to use the custom processor. This is done by passing in the ``$config`` array to ``getInstance()``, something like this:

    .. code-block:: php

      return $this->decoratedFactory->getInstance($identifier, [
        'processors' => [MyNewProcessor::class]
      ]);

It is also very important to declare your new factory class as a service. You accomplish this by decorating ``dkan.common.file_fetcher`` in your module's ``*.services.yml`` file, something like this:

    .. code-block:: yaml

      our_module.file_fetcher:
        class: Drupal\our_module\FileFetcher\FileFetcherFactory
        decorates: dkan.common.file_fetcher
        arguments: ['@our_module.file_fetcher.inner']

Now whenever DKAN uses the ``dkan.common.file_fetcher`` service, your file fetcher factory will be used instead.
