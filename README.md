# <img src="doc/images/logo/logo.png" width="40" height="40"> [TAO](https://www.taotesting.com/) - LTI 1.3 Deep Linking Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-lti1p3-deep-linking.svg?style=flat&label=release)](https://github.com/oat-sa/lib-lti1p3-deep-linking/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://github.com/oat-sa/lib-lti1p3-deep-linking/actions/workflows/build.yaml/badge.svg?branch=master)](https://github.com/oat-sa/lib-lti1p3-deep-linking/actions)
[![Test Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-lti1p3-deep-linking/badge.svg?branch=master)](https://coveralls.io/github/oat-sa/lib-lti1p3-deep-linking?branch=master)
[![Psalm Level Status](https://shepherd.dev/github/oat-sa/lib-lti1p3-deep-linking/level.svg)](https://shepherd.dev/github/oat-sa/lib-lti1p3-deep-linking)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-lti1p3-deep-linking.svg)](https://packagist.org/packages/oat-sa/lib-lti1p3-deep-linking)
[![IMS Certified](https://img.shields.io/badge/IMS-certified-brightgreen)](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit)

> [IMS certified](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit) PHP library for [LTI 1.3 Deep Linking](https://www.imsglobal.org/spec/lti-dl/v2p0/) implementations as [platforms and / or as tools](http://www.imsglobal.org/spec/lti/v1p3/#platforms-and-tools), based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [TAO LTI 1.3 PHP framework](#tao-lti-13-php-framework)
- [IMS](#ims)
- [Installation](#installation)
- [Documentation](#documentation)
- [Tests](#tests)

## TAO LTI 1.3 PHP framework

This library is part of the [TAO LTI 1.3 PHP framework](https://oat-sa.github.io/doc-lti1p3/).

## IMS

You can find below [IMS](https://www.imsglobal.org/) related information.

### Related certifications

- [LTI 1.3 advantage complete](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit)

### Related specifications

- [IMS LTI 1.3 Deep Linking](https://www.imsglobal.org/spec/lti-dl/v2p0/)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-deep-linking
```

## Documentation

You can find below the library documentation, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)

### Workflow

- how to [implement the deep linking workflow (as platform and / or tool)](doc/deep-linking-workflow.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
