# C - Foundation

Foundation module for C framework.

C framework is a lightweight framework dedicated to frontend development for php applications.

Based on top of silex and symfony.

Please see more at
http://maboiteaspam.github.io/Foundation/index.html

# Installation

To hack it, just run usual `composer` commands.

```sh
composer require git@github.com:maboiteaspam/Foundation.git
```

# Register

This module provides a list of core service providers
to register to your application.

Get an eye on bootstrap module to know more.

https://github.com/maboiteaspam/Bootstrap

# Development

#### Running the tests

To run the tests, please install `phpunit`.

`c2-bin get-phpunit` can help you to get ready.

To run it,
```
php phpunit.phar
phpunit
c2-bin phpunit
```

#### Contributing guidelines

#### Documentation

The documentation is wrote using Markdown , GH flavor.

It s hosted on github at this address

- https://github.com/maboiteaspam/Foundation/tree/master/doc

At that day, the documentation is provided as a book to the end user at

- http://maboiteaspam.github.io/Foundation/1-Introduction.html

It s published and made public via github `gh-pages`

- https://github.com/maboiteaspam/Foundation/tree/gh-pages

At present time, to generate the documentation
`C` relies on `gitbook`

#### Writing the doc

##### Regarding packaged classes and libraries

`C` suggest the use of provided guidelines by `symfony` for
classes, methods signatures, examples and general documentation of
your modules,

- http://symfony.com/doc/current/contributing/documentation/format.html
- http://symfony.com/doc/current/contributing/documentation/standards.html


##### Regarding service provider, templates, layouts

`C` suggest to use `literal documentation` style.

- https://github.com/jashkenas/docco
- https://github.com/laughedelic/literator

#### Generating the doc

To generate the documentation please get to know `gitbook`

https://www.gitbook.com

###### __TLDR__

__setup__
```sh
> npm install gitbook-cli -g
> gitbook serve doc # to check your changes
> gitbook build doc book # to build the doc
```

__routine build__
```sh
rm -fr ../Foundation-book
mkdir ../Foundation-book
cd ../Foundation-book
git clone git@github.com:maboiteaspam/Foundation.git . -b gh-pages
gitbook build ../Foundation/doc .
find ../Foundation/src/C/Provider/ -type f -exec sh -c "docco -l linear -o providers {}" \;
git add -A
git commit -m "Book update"
git push --set-upstream origin gh-pages
cd ../Foundation
rm -fr ../Foundation-book
```

__docco build__
```sh
find src/layouts/ -type f -exec sh -c "docco -l linear -o providers {}" \;
find src/templates/ -type f -exec sh -c "docco -l linear -o providers {}" \;
```





========


@todo move following part out into per service README file.

## Modules provided

Find a list and an introduction of each modules of the Foundations.

- __AssetsServiceProvider__
    
    Provides facilities and helpers to work with assets in your app.

    - __assets.www_path__: Store path to the www directory.
    - __assets.bridge_type__: Store the type of bridge to generate according your web server. Value should be one of `builtin`, `nginx`, `apache`
    - __assets.bridge_file_path__: File to the path containing assets bridge information for your web server.
    - __assets.bridger__: Instance of Bridger which generates the file.
    - __assets.cache_store_name__: Name of the cache configuration used by __assets.fs__
    - __assets.fs__: Assets file system resolver.
    - __assets.responder__: Respond assets for a `builtin` web server.
    
- __CacheProvider__

    Provides new drivers to enhance cache module.

    - __cache.drivers__ : Extends `cache.drivers` to add a new `Include` driver. Use it for development purpose.
    
- __CapsuleServiceProvider__

    Boot and register Illuminate/Eloquent ORM.

    - __cache.drivers__ : Extends `cache.drivers` to add a new `Include` driver. Use it for development purpose.
    
- __IntlServiceProvider__
    
    Provides facilities and helpers to work with translations in your app.

    - __intl.cache_store_name__: Name of the cache configuration used by __intl.fs__
    - __intl.fs__: Intl file system resolver.
    - __intl-content.cache_store_name__: Name of the cache configuration used to store compiled translation files.
    - __intl-content.cache__: Cache store instance dedicated to translation files.
    - __intl.loader__: File loader for intl files (only yml supported at that time).
    
- __LayoutServiceProvider__
    
    Provides facilities and helpers to work with layouts in your app.

    - __layout.factory__: Factory to create new Layout instances appropriately.
    - __layout.main__: This is the main layout instance rendered during the application lifetime.
    - __layout.view.helpers__: Array of view helper to bind to the layout renderer. Default are LayoutViewHelper, CommonViewHelper, RoutingViewHelper, FormViewHelper
    - __layout.env.charset__: Charset to use for rendering. Defaults to `utf-8`.
    - __layout.env.date_format__: Defaults to ``.
    - __layout.env.timezone__: Defaults to ``.
    - __layout.env.number_format__: Defaults to ``.
    - __layout.env__: Provide Env instance required by rendered engine.
    - __layout.view__: View instance within the templates are rendered.
    - __layout.responder__: Provide a Layout responder according to the current configuration.
    - __layout.serializer__: Provide a Layout serializer to study or display it.
    - __layout.fs__: Templates file system resolver.
    
- __ModernAppServiceProvider__
    
    Provides facilities and helpers to create modern applications.

    - __modern.fs__: Layouts file system resolver.
    - __modern.layout.store__: Cache store instance dedicated to layout files.
    - __modern.layout.helpers__: Array of helpers injected in the file layout renderer.
    - __modern.dashboard.extensions__: Array of helpers injected in the dashboard for rendering.
    
- __DashboardExtensionProvider__

    Register new extension to consume in your dashboard.

    - __modern.dashboard.extensions__ : Attach new extension to display `time travel`, `Layout structure` and more dashboard tools.
    
- __EsiServiceProvider__

    Extension to detect ESI headers capability and configure the application accordingly during the boot.

    - __layout.fs__ : Register a new path to provide `ESI` template.
    
- __HttpCacheServiceProvider__

    Extension which modify application behavior to respond from cache, or record to cache.

    - __httpcache.cache_store_name__ : Name of the cache configuration used by __httpcache.store__
    - __httpcache.store__ : Cache store instance dedicated to http responses.
    
- __RepositoryServiceProvider__

    Extension which modify application behavior to respond from cache, or record to cache.

    - __httpcache.cache_store_name__ : Name of the cache configuration used by __httpcache.store__
    - __httpcache.store__ : Cache store instance dedicated to http responses.
    
- __WatcherServiceProvider__

    Extension which provides a watcher registry to add watchers implementation and run them.

    - __watchers.watched__ : An array of `WatchedInterface` objects.
    
    


