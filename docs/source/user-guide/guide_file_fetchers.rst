Implementing a custom file fetcher
----------------------------------

DKAN uses a library called getdkan/file-fetcher. This library allows developers to extend the file transfer functionality for their specialized needs.

This library is used to download a resource, such as a CSV file, so that it can be loaded into the database and presented through the UI and API. This process is called 'localization,' because the source resource is copied to the local file system.

The standard file fetcher processors will probably be adequate for most uses, but there could be other use cases, such as needing to authenticate, or getting a file from S3.

TL;DR:
======

- Implement FileFetcher\Processor\ProcessorInterface as a custom processor for FileFetcher.
- Create a FileFetcherFactory class which instantiates a FileFetcher using configuration specifying the new processor in the processors array.
- Specify this new FileFetcherFactory as a service which decorates dkan.common.file_fetcher, like this:

  our_module.file_fetcher:
    class: Drupal\our_module\FileFetcher\FileFetcherFactory
    decorates: dkan.common.file_fetcher
    arguments: ['@our_module.file_fetcher.inner']

How to:
=======

In situations like this, a custom file-fetcher processor class should be created, implementing FileFetcher\Processor\ProcessorInterface. This class could subclass FileFetcher\Processor\Remote or FileFetcher\Processor\Local, or be a new implementation.

A FileFetcherFactory class should then be implemented, which configures FileFetcher to use the custom processor.

Then this FileFetcherFactory should be declared as a service. This service will decorate dkan.common.file_fetcher, so that it is used everywhere within the DKAN system.
