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
    __________________
    |   HEADER       |
    |________________|
    |         |      |
    |         |      |
    |  MAIN   |  RB  |
    |         |      |
    |         |      |
    |_________|______|
```
It as a root block, and many sub blocks.

__Each block id must be unique within the layout__

Each block hold information such template file,
assets attached to it,

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

##### Describing a layout

To describe the layout the system
gives you access to `action keywords`.

Those actions can affect the layout structure,
or a specific block content.

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



### The YML syntax

Quick references about this file format :
- https://en.wikipedia.org/wiki/YAML
- https://zh.wikipedia.org/wiki/YAML
- http://www.yaml.org/start.html
- http://docs.ansible.com/ansible/YAMLSyntax.html

