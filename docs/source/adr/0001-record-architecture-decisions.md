# 1. Record architecture decisions

Date: 2026-05-11

## Status

Accepted

## Context

We have decided, with the release of DKAN 4, to record the architectural decisions made on this project. We have suffered in the past from fading memories of why certain non-intuitive decisions were made. DKAN is also becoming more visible with its move to Drupal.org and some renewed interest around it, so being able to refer to ARDs will help the public as well.

There is a considerable amount of technical debt in DKAN that we are in the midst of addressing. Often when we make changes to underlying systems, it can be difficult to find an appropriate place to document the change itself, as the main docs are most effective when they are a concise reference on how the software works _now_.

## Decision

We will use Architecture Decision Records, as [described by Michael Nygard](http://thinkrelevance.com/blog/2011/11/15/documenting-architecture-decisions).

We will incorporate these ADRs into the existing Sphinx/readthedocs documentation.

## Consequences

See Michael Nygard's article, linked above. For a lightweight ADR toolset, see Nat Pryce's [adr-tools](https://github.com/npryce/adr-tools).