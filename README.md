AjglSessionConcurrency
======================

The AjglSessionConcurrency component allows you to detect and control concurrent
sessions for the same user.


PR Reference
------------

This feature has been submitted to the Symfony project in the [PR #12810](https://github.com/symfony/symfony/pull/12810).

All your feedback and contributions to this repository will help me to improve the referenced PR.


Usage
-----

You have to override the default session authentication strategy with a composite
strategy chains:

1. The concurrency control strategy
2. The default strategy
3. The register strategy

If you want to expire old sessions when the maximun number of allowed sessions
is reached, you have to subscribe the `SessionRegistryExpirationListener` to the
`kernel.response` event of your application HTTP kernel.


Symfony Bundle
--------------

If you need to integrate this library into your Symfony Framework app, you
can install the [AjglSessionConcurrencyBundle](https://github.com/ajgarlag/AjglSessionConcurrencyBundle)


License
-------

This component is under the MIT license. See the complete license in the LICENSE file.


Badges
------

* **Travis CI**: [![Build Status](https://travis-ci.org/ajgarlag/AjglSessionConcurrency.png?branch=master)](https://travis-ci.org/ajgarlag/AjglSessionConcurrency)
* **Poser Latest Stable Version:** [![Latest Stable Version](https://poser.pugx.org/ajgl/session-concurrency/v/stable.png)](https://packagist.org/packages/ajgl/session-concurrency)
* **Poser Latest Unstable Version** [![Latest Unstable Version](https://poser.pugx.org/ajgl/session-concurrency/v/unstable.png)](https://packagist.org/packages/ajgl/session-concurrency)
* **Poser Total Downloads** [![Total Downloads](https://poser.pugx.org/ajgl/session-concurrency/downloads.png)](https://packagist.org/packages/ajgl/session-concurrency)
* **Poser Monthly Downloads** [![Montly Downloads](https://poser.pugx.org/ajgl/session-concurrency/d/monthly.png)](https://packagist.org/packages/ajgl/session-concurrency)
* **Poser Daily Downloads** [![Daily Downloads](https://poser.pugx.org/ajgl/session-concurrency/d/daily.png)](https://packagist.org/packages/ajgl/session-concurrency)
* **Poser License** [![License](https://poser.pugx.org/ajgl/session-concurrency/license.png)](https://packagist.org/packages/ajgl/session-concurrency)
* **Scrutinizer Quality** [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ajgarlag/AjglSessionConcurrency/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ajgarlag/AjglSessionConcurrency/?branch=master)
* **Scrutinizer Code Coverage** [![Code Coverage](https://scrutinizer-ci.com/g/ajgarlag/AjglSessionConcurrency/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ajgarlag/AjglSessionConcurrency/?branch=master)
* **SensioLabs Insight Quality** [![SensioLabsInsight](https://insight.sensiolabs.com/projects/684809e2-f473-4663-b340-69af09d07088/mini.png)](https://insight.sensiolabs.com/projects/684809e2-f473-4663-b340-69af09d07088)
* **VersionEye Dependency Status** [![Dependency Status](https://www.versioneye.com/php/ajgl:session-concurrency/dev-master/badge.png)](https://www.versioneye.com/php/ajgl:session-concurrency/dev-master)


About
-----

AjglSessionConcurrency is an [ajgarlag](http://aj.garcialagar.es) initiative.


Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/ajgarlag/AjglSessionConcurrency/issues).
