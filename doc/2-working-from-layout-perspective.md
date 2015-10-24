# Working with layouts

Layout files are an important aspect of this project.
They let you control and take power on the appearance
of the rendering output of your application.

Moreover its the backbone of communication
between back and front developers.

In order to let both concern work on their own
but still communicate in a comprehensible way
the system invite the principle of
__progressive layout enhancement__.

In order of declaration,
starting from the backend controller,
continued by imported frontend layout files,
if offers to progressively build and enhance the layout
of new features and behaviors.

Ok, lets make a drawing :p
```
   |
Request
   |
   |----------> Controller
                    |
     ______       Layout
    | Back |-->     |
    |______|        |
 create layout -->  |
 import layout -->  |       _______
                    |      | Front |
                    |  <-- |_______|
                    |  <-- declare or update layout
                    |  <-- import its own layout
 update layout -->  |
 render layout -->  |
                    |
                 Response
                    |----> Render
```


It describes the page to render as a tree of named block.
```
    VISUAL                  TREE
    __________________
    |   HEADER       |      | Root
    |________________|      | Root/HEADER
    |         |      |      | Root/MAIN
    |  MAIN   |  RB  |      | Root/RB
    |         |      |      | Root/FOOTER
    |_________|______|
    |   FOOTER       |
    |________________|
```
It has a root block, and many sub blocks.

Each block can render its part
of the layout by invoking other blocks.

__Each block id is unique within the layout__

Each block hold information such template file,
assets attached to it, data to inject into the view,
meta information.


It s using a simple but powerful syntax written with `yml`

`Hello The World` example

__FILE__ src/layouts/hello-the-world.yml
```yml
meta:
    id: hello-the-world
    description: Hello the world written with a layout

structure:
    - import: HTML:/1-column.yml
    body_content:
        body: |
            Hello the world !
```

the same `hello the world` example with comments

__FILE__ src/layouts/hello-the-world.yml
```yml

# provide information about the layout to be rendered
meta:
    id: hello-the-world
    description: Hello the world written with a layout

# describe and update the structure of the layout
structure:

    # Import a base layout which provides supports
    # for commons task regarding an HTMl website
    # HTML is a core module to provide such fundamentals.
    - import: HTML:/1-column.yml

    # select body_content node of the layout
    # body_content was delared by HTML:/1-column.yml
    body_content:
        # set its body
        body: |
            Hello the world !
```

find an advanced layout example demonstration at

https://github.com/maboiteaspam/Welcome/blob/master/src/layouts/formDemo.yml

##### Block role and structure

It s really about describing all of the components
 of a page via a layout object.
For that matter each block of the layout
holds information
about its template, assets and so son.
The later they are resolved into plain html text,
or other structure depending the desired action.

A block structure example
```yml
id: root
body: '<div>...' # the content body as plain html
options:
    template: 'HTML:/html.php' # the template path
data: {
       # .. A lits of displayed block
       # with their status of display
}
assets: {
        # .. A lits of assets block
        # to be injected in order to render this block
}
inline: {
        # .. A lits of inline assets content
        # to be injected in order to render this block
}
requires: {
       #  .. A lits of assets requirements
        # based on semver pattern
}
meta:
        # .. A handful list of meta information
        # injected by the various components
        # involved during the process
        # of building the layout
    debug_with: comments
    from: false
    etag: ''
displayed_blocks:
        # .. A lits of displayed block
        # with their status of display
    - { id: html_begin, shown: true }
stack:
        # .. when debug is enabled,
        # this gives stack trace values
        # of caller which updated the block
    - { file: .../src/C/Layout/Layout.php, line: 390, function: getStackTrace, class: C\Misc\Utils, type: '::' }
firstAssets: {
        # .. A lits of asset paths
        # to display first
}

```

We ll see next the feature the framework offer to enrich layout and blocks.

##### Layout file structure

The layout file takes root nodes

```yml
meta:
    # describe the layout object properties
structure:
    # describe the layout content structure
```

The `meta:` node takes an array of property nodes.

```yml
meta:
    id: layout-id
    description: description
```

The `structure:` node takes an array of action nodes.

```yml
structure:
    - action_node1
    - action_node2
    action_node3
    action_node4
```

Each action node can describe either
a structural change or select a block.

```yml
structure:
    - change_layout: [arguments]
    - change_layout2: [arguments]
    [block_id1]: [arguments]
    [block_id2]: [arguments]
```

When a block is selected, it is then possible
to apply it a series of block action nodes.

```yml
structure:
    - change_layout: [arguments]
    - change_layout2: [arguments]
    [block_id1]:
        set_template: /some/file.php
    [block_id2]:
        set_data: {some_data: "indeed"}
```

##### Describing a layout

To describe a layout, the system
gives you access to `action keywords`.

Those actions can affect the layout structure,
or a specific block content.

__Import a layout__
`import` keyword import
and process an additional layout file.

It takes a string, or an array of path to layout files.

```yml
structure:
    - import: path/to/layout.yml
    - import:
        - path/to/layout1.yml
        - path/to/layout2.yml
```


__Configure blocks__

`set_template` keyword change the template file of a layout.

It takes a string to the layout path.

```yml
structure:
    [block_id]:
        set_template: path/to/template.php
```


`body` keyword to set the body content of a block.

It takes a string, the HTML content to use.

```yml
structure:
    [block_id]:
        body: |
            The content in HTML
```


`set_default_data` keyword to set default data of a block.

Defaults data won t override existing data.

It takes an array, a dictionary.

```yml
structure:
    [block_id]:
        set_default_data:
            key: value
            pair: of data
```


`update_meta` keyword to update data.

Meta of the block will be overridden by this call.

It takes an array, a dictionary.

```yml
structure:
    [block_id]:
        update_meta:
            key: value
            pair: of data
```


`insert_before` keyword to insert a block before another one.

It takes a string.

```yml
structure:
    [block_id]:
        insert_before: [target]
```


`insert_after` keyword to insert a block after another one.

It takes a string.

```yml
structure:
    [block_id]:
        insert_after: [target]
```


`clear` keyword to clear the data or other information of a block.

It takes a string, its value should be one of
`all`, `data`, `meta`, `options`, `template`.

or a mixin `data options`

```yml
structure:
    [block_id]:
        clear: [what]
```


`delete` keyword to delete a block from the tree,
cancelling its display and all of its children.

It does not take any value.

```yml
structure:
    [block_id]:
        delete: ~
```

##### Working with assets

`add_assets` keyword to attach assets on the given block.
Assets are injected according to the specified targets.

```yml
structure:
    [block_id]:
      add_assets:
        template_head_js:
            - HTML:/normalizer.js
```

`remove_assets` keyword to remove an asset from the given block.

```yml
structure:
    [block_id]:
      remove_assets:
        template_head_js:
            - HTML:/normalizer.js
```

`register_assets` keyword to register a vendor asset.

```yml
structure:
  - register_assets:
        alias: js-normalizer
        path: HTML:/normalizer.js
        version: 1.1.1
        target: template_head_js
        first: true|false
```

`require` keyword to require a vendor asset on the given block.

```yml
structure:
    [block_id]:
      require: [js-normalizer:1.x]
```

##### Working with request facets

Request facets are request selector based on facet attributes.

Facet attributes can be such as

__device type__: desktop, mobile, tablet
__request kind__: any, ajax, esi-master, esi-slave
__request language__: put locale here
__request date__: put a period here

When working with layouts request facets
are expressed as layout transform switcher.

Within the flow of the layout definition,
the requests facets are invoked to specify
under which criterion of the request
the layout object should be transformed.

When the given request
does not match the provided criterion
the transformation are voided
and does not affect the layout object.


`for_facets` keyword to conditionally transform the underlying layout object.

It takes an array of `facet=>requirement`,
they must all satisfy the given request to trigger the transform.

They can be negated using `!`.
It must be prepended to the `requirement` value.

```yml
structure:

  - for_facets:
      device: mobile
    [block_id]:
      body: Hello, this is the layout for mobile devices !!

  - for_facets:
      device: !mobile
    [block_id]:
      body: Hello, this is all none mobile devices !!
```

##### Working with templates

##### Working with forms
##### Working with validation

##### Loading your own





### The YML syntax

Quick references about this file format :
- https://en.wikipedia.org/wiki/YAML
- https://zh.wikipedia.org/wiki/YAML
- http://www.yaml.org/start.html
- http://docs.ansible.com/ansible/YAMLSyntax.html
