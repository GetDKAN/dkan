# Custom Commands

Projects to can define their own commands. To create a custom command, add a file named `CustomCommands.php` and add it to `src/command/`, create a new class in the file with a similar structure to this one:

```php
<?php
namespace DkanTools\Custom;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class CustomCommands extends \Robo\Tasks
{
    /**
     * Sample.
     */
    public function customSample()
    {
        $this->io()->comment("Hello World!!!");
    }
}
```

The critical parts of the example are:

1. The namespace
1. The extension of \Robo\Tasks
1. The name of the file for the class should match the class name. In this case the file name should be CustomCommands.php

Everything else (class names, function names) is flexible, and each public function inside of the class will show up as an available `dktl` command.


## Advanced configuration

### Disabling `chown`

DKTL, by default, performs most of its tasks inside of a docker container. The result is that any files created by scripts running inside the container will appear to be owned by "root" on the host machine, which often leads to permission issues when trying to use these files. To avoid this DKTL attempts to give ownership of all project files to the user running DKTL when it detects that files have changed, using the `chown` command via `sudo`. In some circumstances, such as environments where `sudo` is not available, you may not want this behavior. This can be controlled by setting a true/false environment variable, `DKTL_CHOWN`.

To disable the `chown` behavior, create the environment variable with this command:

```bash
export DKTL_CHOWN="FALSE"
```

### Configuration Brought into the Containers from the Host Machine.

Unless you are running in "HOST" mode, DKTL runs inside of docker containers. Some configuration from your host machine can be useful inside of the containers: ssh configuration, external services authentication tokens, etc.

DKTL recognized this and by default makes some configurations available to the containers by default. If any of these directories exist in your current user's profile, they will be available in the container:
* .ssh
* .aws
* .composer
