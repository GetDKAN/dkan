<?php

namespace DkanTools\Command;

use DkanTools\Util\Util;
use Robo\Tasks;

class InstallCommands extends Tasks
{
    /**
     * Perform Drupal/DKAN database installation
     *
     * @option bool existing-config
     *   Use drush site:install --existing-config option.
     */
    public function install($opts = ['existing-config' => false])
    {
        if ($opts['existing-config']) {
            $this->taskExec('dktl drush si -y --existing-config')
                ->dir(Util::getProjectDocroot())
                ->run();
        } else {
            $this->standardInstallation();
        }

        $result = $this->taskExecStack()
            // Ensure resources directories exists and are writable.
            ->exec('mkdir -p sites/default/files/uploaded_resources')
            ->exec('mkdir -p sites/default/files/resources')
            ->exec('chmod -R 777 sites/default/files')
            // Workaround for https://www.drupal.org/project/drupal/issues/3091285.
            ->exec('chmod u+w sites/default')
            ->dir(Util::getProjectDocroot())
            ->run();

        return $result;
    }

    /**
     * Run standard Drupal site installation, and enable config_update_ui.
     */
    private function standardInstallation()
    {
        $this->taskExecStack()
            ->stopOnFail()
            ->exec('dktl drush site:install standard --site-name "DKAN" -y')
            ->exec("dktl drush en dkan config_update_ui -y")
            ->exec("dktl drush config-set system.performance css.preprocess 0 -y")
            ->exec("dktl drush config-set system.performance js.preprocess 0 -y")
            ->dir(Util::getProjectDocroot())
            ->run();
    }

    /**
     * Install DKAN sample content.
     */
    public function installSample()
    {
        $this->taskExecStack()
            ->stopOnFail()
            ->exec('dktl drush en sample_content -y')
            ->exec('dktl drush  dkan:sample-content:create')
            ->exec('dktl drush  queue:run datastore_import')
            ->exec('dktl drush  dkan:metastore-search:rebuild-tracker')
            ->exec('dktl drush  sapi-i')
            ->dir(Util::getProjectDocroot())
            ->run();
    }
}
