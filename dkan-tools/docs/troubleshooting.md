# Troubleshooting

## Using Xdebug

When using the standard docker-compose environment, [Xdebug](https://xdebug.org/) can be enabled on both the web and CLI containers as needed. Running it creates a significant performance hit, so it is disabled by default. To enable, simply run `dktl xdebug:start`. A new _xdebug.ini_ file will be added to _/src/docker/etc/php_, and the corresponding containers will restart. In most situations, this file should be excluded from version control with .gitignore.

To turn off Xdebug, run `dktl xdebug:stop`, the .ini file will be removed and the container restarted.

## Inspect the containers
- `dktl dc exec web bash`
- `dktl dc exec cli bash`

## View logs
- `dktl dc logs db`

## Error messages
Error | Solution
--- | ---
UnixHTTPConnectionPool(host='localhost', port=None): Read timed out. | Restart Docker
No container found for cli_1 | Find dangling container and remove it:```docker ps -a && docker stop [container-id] && docker rm -v [container-name]```
PHP Warning:  is_file(): Unable to find the wrapper "s3" | Delete the vendor directory in your local dkan-tools and run `dktl` in your project directory
hanging ownership of new files to host user ... chown: ...: illegal group name | Disable the chown behavior `export DKTL_CHOWN="FALSE"
