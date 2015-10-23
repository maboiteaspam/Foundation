# Introduction

Welcome, C is a frontend framework wrote in php.
Based on top of symfony and silex components, it aims to provide
alternative tools to help in creating and maintaining a complex frontend application.

## Getting started

To get started with this framework you shall first ensure those dependencies are available on your computer

- php >= 5.3
    Please get a copy here http://us3.php.net/downloads.php
    Please ensure `php` binary is available into your system's `PATH`.

- node >= 0.12.x
    Please get a copy here https://nodejs.org/en/download/
    Please ensure `node` and `npm` are available on into your system's `PATH`

- composer
    Please get a copy here https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx
    Please ensure `composer` binary is available into your system's `PATH`.

Once this is all set, open a terminal, you will install `c2-bin`, a dedicated command and control binary for C projects.

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

Let s generate a first module and see what s inside.

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




