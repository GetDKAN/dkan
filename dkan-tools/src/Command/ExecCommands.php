<?php

namespace DkanTools\Command;

use Robo\Result;
use DkanTools\Util\Util;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class ExecCommands extends \Robo\Tasks
{
    /**
     * Run drush command on current site.
     *
     * Run drush command on current site. For instance, to clear caches, run
     * "dktl drush cc all".
     *
     * @param array $cmd Array of arguments to create a full Drush command.
     *
     * @aliases drush
     */
    public function execDrush(array $cmd)
    {
        $projectDir = Util::getProjectDirectory();
        $drushExec = $this->taskExec("{$projectDir}/vendor/bin/drush")->dir($projectDir);
        foreach ($cmd as $arg) {
            $drushExec->arg($arg);
        }
        $drushExec->option('uri', Util::getUri());
        return $drushExec->run();
    }

    /**
     * Proxy to composer.
     *
     * @aliases composer
     */
    public function execComposer(array $cmd)
    {
        $exec = $this->taskExec('composer');
        foreach ($cmd as $arg) {
            $exec->arg($arg);
        }
        return $exec->run();
    }
}
