# LTI 1.3 Deep Linking Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-lti1p3-deep-linking.svg?style=flat&label=release)](https://github.com/oat-sa/lib-lti1p3-deep-linking/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://github.com/oat-sa/lib-lti1p3-deep-linking/actions/workflows/build.yaml/badge.svg?branch=master)](https://github.com/oat-sa/lib-lti1p3-deep-linking/actions)
[![Test Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-lti1p3-deep-linking/badge.svg?branch=master)](https://coveralls.io/github/oat-sa/lib-lti1p3-deep-linking?branch=master)
[![Psalm Level Status](https://shepherd.dev/github/oat-sa/lib-lti1p3-deep-linking/level.svg)](https://shepherd.dev/github/oat-sa/lib-lti1p3-deep-linking)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-lti1p3-deep-linking.svg)](https://packagist.org/packages/oat-sa/lib-lti1p3-deep-linking)

> PHP library for [LTI 1.3 Deep Linking](https://www.imsglobal.org/spec/lti-dl/v2p0/) implementations as platforms and / or as tools, based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [Specifications](#specifications)
- [Installation](#installation)
- [Tutorials](#tutorials)
- [Tests](#tests)

## Specifications

- [IMS LTI 1.3 Deep Linking](https://www.imsglobal.org/spec/lti-dl/v2p0/)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-deep-linking
```

## Tutorials

You can then find below usage tutorials, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)

### Workflow

- how to [implement the Deep Linking workflow (for platform and / or tool)](doc/deep-linking-workflow.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
