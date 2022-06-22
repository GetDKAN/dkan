<?php

namespace DkanTools\Command;

use Robo\Tasks;
use DkanTools\Util\Util;

class DeployCommands extends \Robo\Tasks
{
     /**
     * Performs common tasks when switching databases or code bases.
     *
     * Operations like running rr and updb. It also runs environment
     * switching which is provided by the environment module.
     *
     * @param string $target_environment
     *   One of the site environments. DKTL provides 4 by default: local,
     *   development, test, and production.
     */
    public function deploy($target_environment)
    {
        $project = Util::getProjectDirectory();
        $script = "{$project}/src/script/deploy.sh";
        $docroot = Util::getProjectDocroot();

        if (file_exists($script)) {
            $command = "{$script} {$docroot} {$target_environment}";
            $this->_exec($command);
        }
    }
}
