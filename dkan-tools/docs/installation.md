# Installation

1. [Download or clone](https://github.com/GetDKAN/dkan-tools/) this repository into any location on your development machine.
1. Add _bin/dktl_ to your `$PATH`.
    - This is often accomplished by adding a symbolic link to a folder already in your path, like _~/bin_. For instance, if DKAN Tools is located in _/myworkspace_:

    ```
    ln -s  /myworkspace/dkan-tools/bin/dktl ~/bin/dktl
    ```

    - Alternatively, you could add _/myworkspace/dkan-tools/bin_ directly to your `$PATH`. Enter this in your terminal or add it to your session permanently by adding a line in _.bashrc_ or _.bash_profile_:

    ```
    export PATH=$PATH:/myworkspace/dkan-tools/bin
    ```


## DKAN Quick-Start Demo

Create a project directory, initialize the project and run the demo script.

```bash
mkdir my_project && cd my_project
dktl init
dktl demo
```

This will set up the Drupal backend with sample content and build the frontend app to demonstrate how a standard site would function.

To get a better idea of how DKAN and DKAN-tools work, you may want to follow the more [detailed steps](../newproject) in the next section.
