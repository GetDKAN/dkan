<?php

namespace DkanTools\Command;

use DkanTools\Util\Util;

class InitCommands extends \Robo\Tasks
{
    /**
     * Initialize DKAN project directory.
     *
     * This command will result in a project directory with all needed files
     * and directories for development, including a composer.json (but NOT
     * including any Composer dependencies.)
     *
     * @option str dkan
     *   DKAN version (expressed as composer constraint). Use 2.x-dev for current
     *   bleeding edge.
     * @option bool dkan-local
     *   Use DKAN from a "dkan" folder in your project root instead of composer.
     *   If no version constraint is provided via the --dkan option, dktl will
     *   attempt to generate one based on the current git branch in "dkan".
    */
    public function init($opts = ['dkan' => null, 'dkan-local' => false])
    {
        // Handled by Composer.
        // $this->initConfig();
        // $this->initSrc();
        // $this->initDrupal();
        if ($opts['dkan-local']) {
            $this->initLocalDkan();
            $version = $this->localDkanVersion();
        }
        if (isset($version) && !$opts['dkan']) {
            $opts['dkan'] = $version;
        }
        $this->initDkan($opts['dkan']);
    }

    /**
     * Create the dktl.yml file.
     */
    private function initConfig()
    {
        $this->io()->section('Initializing dktl configuration');
        if (file_exists('dktl.yml')) {
            $this->io()->note('The dktl.yml file already exists in this directory; skipping.');
            return;
        }
        $f = Util::getProjectDirectory() . '/dktl.yml';
        $result = $this->taskExec('touch')->arg($f)->run();
        $this->directoryAndFileCreationCheck($result, $f);
    }

    /**
     * Set up the src directory in a new project.
     */
    private function initSrc()
    {
        $this->io()->section('Initializing project code directory in /src');
        if (is_dir('src')) {
            $this->io()->note("A /src directory already exists; skipping.");
            return;
        }

        $directories = ['docker', 'modules', 'themes', 'libraries', 'site', 'test', 'script', 'command'];
        foreach ($directories as $directory) {
            $dir = "src/{$directory}";
            $result = $this->_mkdir($dir);
            if ($directory == "site") {
                $this->_exec("chmod -R 777 {$dir}");
            }
            $this->directoryAndFileCreationCheck($result, $dir);
        }

        $this->createSiteFilesDirectory();
        $this->createSettingsFiles();
        $this->setupScripts();
    }

    /**
     * Create command directory and copy in sample SiteCommands.php file.
     */
    private function createSiteCommands()
    {
        $dktlRoot = Util::getDktlDirectory();
        $f = 'command/SiteCommands.php';
        $result = $this->taskWriteToFile($f)
            ->textFromFile("$dktlRoot/assets/command/SiteCommands.php")
            ->run();

        $this->directoryAndFileCreationCheck($result, $f);
    }

    /**
     * Set up scripts directory and copy in standard deploy.sh scripts.
     */
    private function setupScripts()
    {
        $dktlRoot = Util::getDktlDirectory();
        $project_dir = Util::getProjectDirectory();

        $files = ['deploy', 'deploy.custom'];

        foreach ($files as $file) {
            $f = "src/script/{$file}.sh";

            $task = $this->taskWriteToFile($f)
                ->textFromFile("{$dktlRoot}/assets/script/{$file}.sh");
            $result = $task->run();
            $this->_exec("chmod +x {$project_dir}/src/script/{$file}.sh");

            $this->directoryAndFileCreationCheck($result, $f);
        }
    }

    /**
     * Create the "site" directory, which will by symlinked to
     * docroot/sites/default.
     */
    private function createSiteFilesDirectory()
    {
        $directory = 'src/site/files';
        $this->_mkdir($directory);
        $result = $this->_exec("chmod 777 {$directory}");

        $this->directoryAndFileCreationCheck($result, $directory);
    }

    /**
     * Add Drupal settings files to src/site.
     *
     * @todo The default.* files are probably no longer necessary.
     */
    private function createSettingsFiles()
    {
        $dktlRoot = Util::getDktlDirectory();
        $hash_salt = Util::generateHashSalt(55);

        $settings = ["default.settings.php", "settings.php", "settings.docker.php", "default.services.yml"];

        foreach ($settings as $setting) {
            $f = "src/site/{$setting}";
            if ($setting == 'settings.php') {
                $result = $this->taskWriteToFile($f)
                ->textFromFile("$dktlRoot/assets/site/{$setting}")
                ->place('HASH_SALT', $hash_salt)
                ->run();
            } else {
                $result = $this->taskWriteToFile($f)
                ->textFromFile("$dktlRoot/assets/site/{$setting}")
                ->run();
            }
            $this->directoryAndFileCreationCheck($result, $f);
        }
    }

    /**
     * Confirm file or directory was created successfully.
     *
     * @param \Robo\Result $result
     *   The result of the task called to create the file.
     * @param mixed $df
     *   Path to file created.
     *
     * @return [type]
     */
    private function directoryAndFileCreationCheck(\Robo\Result $result, $df)
    {
        if ($result->getExitCode() == 0 && file_exists($df)) {
            $this->io()->success("{$df} was created.");
        } else {
            $this->io()->error("{$df} was not created.");
            exit;
        }
    }

    /**
     * Generates basic configuration for a DKAN project to work with CircleCI.
     */
    private function initCircleCI()
    {
        $dktl_dir = Util::getDktlDirectory();
        $project_dir = Util::getProjectDirectory();
        return $this->taskExec("cp -r {$dktl_dir}/assets/.circleci {$project_dir}")->run();
    }

    /**
     * Create a new Drupal project in the current directory. If one exists, it
     * will be overwritten.
     */
    public function initDrupal()
    {
        $this->io()->section('Creating new Drupal project.');
        Util::prepareTmp();

        // Composer's create-project requires an empty folder, so run it in
        // Util::Tmp, then move the 2 composer files back into project root.
        $this->drupalProjectCreate();
        $this->drupalProjectMoveComposerFiles();

        // Modify project's scaffold and installation paths to `docroot`, then
        // install Drupal in it.
        if (!is_dir('docroot')) {
            $this->_mkdir('docroot');
        }

        Util::cleanupTmp();
    }

    /**
     * Add DKAN as a dependency to the project's composer.json.
     *
     * @param string|null $version
     *   Version of DKAN to pull in, expressed as Composer constraint.
     *
     * @return [type]
     */
    public function initDkan(string $version = null)
    {
        $this->io()->section('Adding DKAN project dependency.');
        $this->taskComposerRequire()
            ->dependency('getdkan/dkan', $version)
            ->option('--no-update')
            ->run();
    }

    /**
     * Add composer repository for /dkan folder in project.
     */
    private function initLocalDkan()
    {
        $this->io()->section('Adding local DKAN repository for /dkan.');
        $this->taskComposerConfig()
            ->repository('getdkan', 'dkan', 'path')
            ->run();
    }

    /**
     * Get branch of local DKAN clone.
     */
    private function localDkanVersion()
    {
        if (!is_dir('dkan')) {
            throw new \Exception('No local dkan folder in project root.');
        }
        $result = $this->taskGitStack()
            ->dir('dkan')
            ->exec("rev-parse --abbrev-ref HEAD")
            ->printOutput(false)
            ->run();

        if ($result->getExitCode() === 0) {
            $branch = $result->getMessage();
            return is_numeric(substr($branch, 0, 1)) ? "${branch}-dev" :  "dev-${branch}";
        }
    }

    /**
     * Create the project composer.json based on template.
     */
    private function drupalProjectCreate()
    {
        $projectSource = "getdkan/recommended-project:9.x-dev";
        $createFiles = $this->taskComposerCreateProject()
            ->source($projectSource)
            ->target(Util::TMP_DIR)
            ->preferDist(true)
            ->noInstall()
            ->run();
        if ($createFiles->getExitCode() != 0) {
            $this->io()->error('Could not run composer create-project.');
            exit;
        }
        $this->io()->success("Composer project created from {$projectSource}.");
    }

    /**
     * Move composer.json and .lock back to project dir.
     */
    private function drupalProjectMoveComposerFiles()
    {
        if (file_exists(Util::getProjectDirectory() . "/composer.json")) {
            $override = $this->confirm('composer.json already exists, replace?');
            if (!$override) {
                $this->io()->warning('Skipping composer.json');
                return;
            }
        }

        $moveFiles = $this->taskFilesystemStack()
            ->rename(
                Util::TMP_DIR . "/composer.json",
                Util::getProjectDirectory() . "/composer.json",
                true
            )
            ->run();
        if ($moveFiles->getExitCode() != 0) {
            $this->io()->error('could not move composer files.');
            exit;
        }
        $this->io()->success('composer.json moved to project root.');
    }
}
