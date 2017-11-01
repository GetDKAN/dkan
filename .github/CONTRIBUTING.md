# Contributing to DKAN

## Issues

Issues submitted to this repo should follow one of the following two models:

### Bug Report

A bug report should explain the problem as specifically as possible along with steps to reproduce. For instance,

> The Dataset form is broken

...is not a useful bug report.

> When I click "Finished" after filling out the form to create a new dataset, I get sent to a 404 Not Found page and my dataset is not saved. This does not happen if I'm editing a new dataset. I am using version 7.x-1.9 of the dkan_dataset module.

...is a very useful bug report!

#### Bug Report Template:

```
## Description

## Steps to Reproduce

## Acceptance Criteria

## Test Updates

## Documenation Updates
```

### New Feature

Any new feature or other change in functionality that is NOT a bug in existing functionality should be expressed as a user story, with optional additional context. A user story should follow the general format of 

> _As a [user role], I want to [execute an action] so that I can [achieve an outcome]._

Mark both the user story and additional context with headings identifying them as such. 

Writing everything out as a traditional user story may seem tedious, but it becomes much easier to determine whether an issue is complete when there is a clear user story to test against.

#### New Feature Template
```
## Description

## User Stories

## Tests

## Documentation

## Pull Requests

## Acceptance Criteria
```

## Pull requests

Pull requests should include sufficient context for a maintainer to open them for the first time and understand clearly what if any action needs to be taken. This means a pull request should contain in its initial description, at minimum:

1. Either:
  * a link to a clearly-defined issue in either the [main DKAN issue queue]() or a specific DKAN implementation project (preferable), or
  * a clearly written explanation of the issue the pull request adresses, ideally formatted as a user story, if a ticket does not exist or belongs to a repo that the DKAN maintainers do not have access to
2. An acceptance test with easily-reproducable and verifiable steps formatted as github tasks. In most cases, the issue the pull request references will include acceptance criteria that can be cut and pasted in.

### Pull Request Template
```
## Description
ref: NuCivic/[repo]/[issue#]

## Acceptance Test


```

Please use a reasonably descriptive *title* as well. "Updating drupal-org.make" is not a helpful title; "Add views patch to drupal-org.make to address argument bug" is a helpful title!

[See this pull request for an example](https://github.com/GetDKAN/dkan/pull/629).

If a pull request is simply being created for QA purposes or should for some other reason NOT be merged, explain this in the description and add a "don't merge" tag.

### Changelog Guidelines

Pull requests must include a new line in CHANGELOG.txt before being merged into the master (7.x-1.x) branch explaining what has changed. Make sure to point out any modules added, modules removed, contrib modules updated, or patches applies.
