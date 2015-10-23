# Working with layouts

Layout files are an important aspect of this project.
They let you control and take power on the appearance
of the rendering output of your application.

Moreover its the backbone of communication
between back and front developers.

It s using a simple but powerful syntax written with `yml`


`Hello The World` example

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

