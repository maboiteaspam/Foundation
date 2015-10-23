# Working with layouts

Layout files are an important aspect of this project.
They let you control and take power on the appearance
of the rendering output of your application.

Moreover its the backbone of communication
between back and front developers.

Ok, lets make a drawing :p
```
   |
Request
   |
   |----> Controller
              |
 ______    Layout    _______
| Back |-->   |     | Front |
|______|      |  <--|_______|
              |
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

or a mixin `data options`

```yml
structure:
    [block_id]:
        delete: ~
```

##### Working with assets
##### Working with forms
##### Working with validation
##### Loading your own





### The YML syntax

Quick references about this file format :
- https://en.wikipedia.org/wiki/YAML
- https://zh.wikipedia.org/wiki/YAML
- http://www.yaml.org/start.html
- http://docs.ansible.com/ansible/YAMLSyntax.html

