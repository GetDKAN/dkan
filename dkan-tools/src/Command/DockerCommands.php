<?php

namespace DkanTools\Command;

use DkanTools\Util\Util;
use Robo\Task;

/**
 * These functions are not meant to be run, simply to provide documentation for
 * the docker commands supplied by dktl.sh.
 *
 * @see http://robo.li/
 */
class DockerCommands extends \Robo\Tasks
{
    const DKTL_DOCKER_PHP_ERROR = "This command was run in error; docker commands should run through dktl.sh";

    /**
     * Run a docker-compose command. E.g. "dktl docker:compose ps".
     *
     * Run a docker-compose command, with configuration options already added.
     *
     * Examples:
     *
     * * "dktl docker:compose ps" to list project's containers and their state
     * * "dktl docker:compose up -d" to start up docker containers
     * * "dktl docker:compose exec cli bash" for a command prompt in the cli
     * * "dktl docker:compose stop" to shut down a project's containers
     * * "dktl docker:compose rm" to erase a project's containers
     *
     * You may also run a shorter alias, "dc" (e.g. "dktl dc ps"). See
     * https://docs.docker.com/compose/reference/ for a full list of commands.
     *
     * @aliases dc
     */
    public function dockerCompose()
    {
        throw new \Exception(self::DKTL_DOCKER_PHP_ERROR);
    }

    /**
     * Display the http web URL for the current project.
     *
     * @aliases url
     */
    public function dockerUrl()
    {
        $uri = Util::getUri();
        if ($uri) {
            return $uri;
        } else {
            throw new \Exception("URI not available!");
        }
    }

    /**
     * Connect the web container to the proxy.
     *
     * @aliases proxy:connect
     */
    public function dockerProxyConnect()
    {
        throw new \Exception(self::DKTL_DOCKER_PHP_ERROR);
    }

    /**
     * Kill the dktl proxy service.
     *
     * @aliases proxy:kill
     */
    public function dockerProxyKill()
    {
        throw new \Exception(self::DKTL_DOCKER_PHP_ERROR);
    }
}
