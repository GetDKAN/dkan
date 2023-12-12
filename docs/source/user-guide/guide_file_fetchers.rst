Implementing a custom file fetcher
----------------------------------

DKAN uses a library called getdkan/file-fetcher. This library allows developers to extend the file transfer functionality for their specialized needs.

This library is used to download a resource, such as a CSV file, so that it can be loaded into the database and presented through the UI and API. This process is called 'localization,' because the source resource is copied to the local file system.

The standard file fetcher processors will probably be adequate for most uses, but there could be other use cases, such as needing to authenticate, or getting a file from S3 instead of HTTP.

In cases such as these, we might want to add our own processor class to extend the file fetcher functionality.

TL;DR:
======

- A code example can be found in the ``custom_processor_test`` module, which is used to test this functionality.
- Implement ``FileFetcher\Processor\ProcessorInterface`` as a custom processor for ``FileFetcher``. Copy or subclass ``FileFetcher\Processor\Local`` and/or ``FileFetcher\Processor\Remote`` if it makes things easier.
- Create a ``FileFetcherFactory`` class which instantiates a ``FileFetcher`` using configuration specifying the new processor in the processors array. Always merge in the configuration supplied to ``getInstance()``, as well.
- Specify this new ``FileFetcherFactory`` as a service which decorates ``dkan.common.file_fetcher``.

How to:
=======

To implement a new file processor, a create a custom file-fetcher processor class. This class could extend ``FileFetcher\Processor\Remote`` or ``FileFetcher\Processor\Local``, or be a new implementation of `FileFetcher\Processor\ProcessorInterface`.

A ``FileFetcherFactory`` class should then be created. The new factory should configure ``FileFetcher`` to use the custom processor. This is done by merging configuration for your new processor within the ``$config`` parameter to ``getInstance()``, something like this:

    .. code-block:: php

      public function getInstance(string $identifier, array $config = []) {
        // Add OurProcessor as a custom processor.
        $config['processors'] = array_merge(
          [OurProcessor::class],
          $config['processors'] ?? []
        );
        // Get the instance from the decorated factory, using our modified config.
        return $this->decoratedFactory->getInstance($identifier, $config);
      }

It is also very important to declare your new factory class as a service. You accomplish this by decorating ``dkan.common.file_fetcher`` in your module's ``*.services.yml`` file, something like this:

    .. code-block:: yaml

      our_module.file_fetcher:
        class: Drupal\our_module\FileFetcher\FileFetcherFactory
        decorates: dkan.common.file_fetcher
        arguments: ['@our_module.file_fetcher.inner']

Now whenever DKAN uses the ``dkan.common.file_fetcher`` service, your file fetcher factory will be used instead.

How processors work
===================

A little discourse on how file-fetcher decides which processor to use...

File fetcher 'knows about' two processors by default: ``FileFetcher\Processor\Local`` and ``FileFetcher\Processor\Remote``. It also knows about whichever custom processor class names you configured in the ``processors`` array in configuration.

When you ask a file fetcher object to perform the transfer (using the ``copy()`` method), it will instantiate all the different types of processors it knows about.

Then it will loop through them and use the ``isServerCompatible()`` method to determine if the given ``source`` is suitable for use with that processor object. The file fetcher will use the first processor that answers ``TRUE``.

You can look at the implementations of ``FileFetcher\Processor\Local::isServerCompatible()`` or ``FileFetcher\Processor\Remote::isServerCompatible()`` to see how they each handle the question of whether they're suitable for the ``source``.
