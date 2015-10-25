# Introduction

Welcome, C is a frontend framework wrote in php.
Based on top of symfony and silex components, it aims to provide
alternative tools to help in creating and maintaining
a complex frontend application.

## Getting started

The framework is compatible with major desktop environment systems.
Others like freebsd are untested but expected to work.

To get started with this framework
you shall first ensure those dependencies are available on your computer

- php >= 5.3

    Please get a copy here http://us3.php.net/downloads.php

    Please ensure `php` binary is available into your system's `PATH`.

- node >= 0.12.x

    Please get a copy here https://nodejs.org/en/download/

    Please ensure `node` and `npm` are available on into your system's `PATH`

- composer

    Please get a copy here https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx

    Please ensure `composer` binary is available into your system's `PATH`.

Once this is all set, open a terminal,
you will install `c2-bin`,
a dedicated command and control binary for C projects.

__read more__ about `c2-bin` https://github.com/maboiteaspam/c2-bin

```
shell > npm i maboiteaspam/c2-bin -g
npm WARN optional dep failed, continuing fsevents@1.0.2
/some/path/to/npm/c2-bin -> /some/path/to/npm/node_modules/c2-bin/bin.js
...
npm WARN unmet ...
npm WARN unmet dependency which is version 3.3.5
...
c2-bin@1.0.0 C:\Users\d1m\AppData\Roaming\npm\node_modules\c2-bin
├── findup-sync@0.3.0
├── minimist@1.2.0
├── resolve@1.1.6
├── once@1.3.2 (wrappy@1.0.1)
...
shell >
```

`npm` will take a moment to finalize the install, especially if its your first use of it.
Don t worry about the warnings if you encounter any.

`c2-bin` is now available on your path, you can give it a check by running `--version` switch and get an output similar to this

```
shell > c2-bin --version
grunt-cli v0.1.13
grunt v0.4.5
```

## Writing a first module

C framework tries its best to give you the right tool, the right configuration, and the right methods out-of-the-box.

Let s generate a first module so the later we can see and study a module composition.

For that matter, open a command line, make a new folder, and let s invoke `c2-bin generate`.

```
shell > cd /to/my/work/folder/
shell > mkdir a-new-folder
shell > cd a-new-folder
shell > c2-bin generate

Running "get-composer" task
>> Composer is in your project, let s move on !

Running "generate-app" task
? Which type of module would you like to create : (Use arrow keys)
> design-component
  component
  app

? Please tell me the namespace to use: My\Module
Got it! Generating new design-component under namespace My\Module
File [...] creating..

Running "install" task
? Would you like to run composer INSTALL command now ? (Use arrow keys)
> yes, please get it done
  no, i will do it later
```

- If you have not installed `composer` on your `PATH`, `c2-bin` will suggest you to download it, please do so.

- `c2-bin` will then ask you about the type of module you d like to create,
please choose __design-component__ at that moment.

- `c2-bin` will need you to give a name to your module, please follow `composer` convention `ProjectName\ModuleName`.

    The module will then use a template module to generate the files and structure.

- Finally, `c2-bin` will invite you to run `composer install` command. Which will install the `php` components dependencies.


## Starting your module

We are all set and ready, let s start the module now !

Open a terminal, browse to your module folder and use `c2-bin run`.

```
shell > cd /to/my/work/folder/a-new-folder/
shell > c2-bin

Running "check-module-install" task
>> Module is installed, let s move on !

Running "classes-dump" task

php -d opcache.enable_cli=0  composer.phar dumpautoload
>> Generating autoload files

Running "cache-init" task

[... and many more output like this ... ]

Running "start" task

php -S localhost:8000 -t www app.php
```

That`s it! Your system should now be spawning a new browser window and get you on your first C web application endpoint.


## Module overview

In order to get things done right, C framework is very opiniated.

Every time you ll create a module, a structure like this will be created :

```
 |   run/                           # Contains runtime data such cache
 |   vendor/                        # Contains all composer dependencies.
 |   www/                           # The www root folder for a web application.
 |   src/                           # Your local source code !
 |      src/assets/                    # Your front assets such as JS and CSS files.
 |      src/intl/                      # Intl files and translations lands here.
 |      src/layouts/                   # Layout files describing the view lands here.
 |      src/templates/                 # The view implementation files stand here.
 |      src/Controllers.php            # A Controller example implementation,
                                       # for your convenience.
 |      src/ControllersProvider.php    # Your application controller provider,
                                       # to declare your module as a service.
 |   .gitignore                     # get things done right.
 |   .editorconfig                  # get things done right.
 |   bower.json                     # get things done right.
 |   .bowerrc                       # get things done right.
 |   app.php                        # Web application entry point.
 |   bootstrap.php                  # Application modules registration and configuration.
 |   cli.php                        # Cli application entry point.
```

C framework tries not to re invent the wheel when it exits and looks good.

In that regard, please check and get to know about those underlying tools and their documentation.

- __Composer__

    Composer is the dependency manager C consumes to wires php module dependencies.

    https://getcomposer.org/

- __Symfony Foundation__

    All the foundation framework including HTTP request / response parsing, CLI console interface, Session management, conventions, good practices and more are coming from this framework.

    Its documentation is awesome, get to know it.

    http://symfony.com/doc/current/index.html

    See also http://www.symfony2cheatsheet.com/

- __Silex Foundation__

    Silex is the backbone of a C project, it s the container where dependencies are injected in.

    Its documentation is also awesome, get to know it.

    http://silex.sensiolabs.org/documentation





## What's next ?

That's it for this introduction about C framework.
To continue your tour and experience, please choose one of the following topic,

- I'm front end designer, get to know how to work with layouts
https://github.com/maboiteaspam/Foundation/blob/master/doc/2.0-working-from-layout-perspective.md
- I'm back end developer, get to know how to work with controllers, entities, cache
https://github.com/maboiteaspam/Foundation/blob/master/doc/3-working-from-controller-perspective.md
- I'm senior project developer, get to know how to work with framework internals and advanced topics
https://github.com/maboiteaspam/Foundation/blob/master/doc/4-advanced-topics.md

Don t forget to check out the Welcome module available here
https://github.com/maboiteaspam/Welcome

It s a working example with lots of comments.

