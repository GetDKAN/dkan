<?php

namespace DkanTools\Command;

use DkanTools\Util\TestUserTrait;

use Robo\Tasks;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * This project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class ProjectCommands extends Tasks
{
    use TestUserTrait;

    /**
     * Path to project cypress tests.
     *
     * @var string
     */
    protected const TESTS_DIR = 'src/tests';

    /**
     * Run project cypress tests.
     *
     * @param array $args
     *   Cypress command arguments.
     */
    public function projectTestCypress(array $args)
    {
        // Prepare environment to run cypress.
        $this->symlinkOrInstallCypress();
        $this->installNpmDependencies();
        $this->createTestUsers();

        // Run Cypress.
        $this->io()->say('Running cypress...');
        $config_option = file_exists(self::TESTS_DIR . '/cypress.json') ? ' --config-file cypress.json' : '';
        $result = $this->taskExec('CYPRESS_baseUrl="http://$DKTL_PROXY_DOMAIN" npx cypress run' . $config_option)
            ->dir(self::TESTS_DIR)
            ->args($args)
            ->run();

        // Clean up environment.
        $this->deleteTestUsers();
        
        return $result;
    }

    /**
     * Attempt to symlink Cypress command.
     *
     * @throws \RuntimeException
     *   On failure.
     */
    protected function symlinkOrInstallCypress(): void
    {
        $this->io()->say("Determining if cypress is installed in it's standard location...");
        $cypress_path = '/usr/local/bin/node_modules/cypress';
        if (is_dir($cypress_path)) {
            $this->io()->say('Cypress installed in standard location; symlinking cypress package folder...');
            // Symlink cypress package folder.
            $result = $this->taskExec('npm link ' . $cypress_path)
                ->dir(self::TESTS_DIR)
                ->run();
            // Handle errors.
            if ($result->getExitCode() !== 0) {
                throw new \RuntimeException('Failed to symlink cypress package folder');
            }
        }
        else {
            $this->io()->warning('Cypress installation not found in standard location; Attempting to install cypress locally...');
            $result = $this->taskExec('npm install cypress')
                ->dir(self::TESTS_DIR)
                ->run();
            if ($result->getExitCode() !== 0) {
                throw new \RuntimeException('Failed to install cypress');
            }
            $this->io()->success('Successfully installed cypress!');
        }
    }

    /**
     * Attempt to install npm test dependencies.
     *
     * @throws \RuntimeException
     *   On failure.
     */
    protected function installNpmDependencies(): void
    {
        $this->io()->say('Installing test dependencies...');
        $result = $this->taskExec('npm install --force')
            ->dir(self::TESTS_DIR)
            ->run();
        if ($result->getExitCode() !== 0) {
            throw new \RuntimeException('Failed to install test dependencies');
        }
    }

    /**
     * Run Site PhpUnit Tests. Additional phpunit CLI options can be passed.
     *
     * @param array $args
     *   Arguments to append to phpunit command.
     *
     * @see https://phpunit.de/manual/6.5/en/textui.html#textui.clioptions
     */
    public function projectTestPhpunit(array $args)
    {
        $proj_dir = Util::getProjectDirectory();
        $phpunit_executable = $this->getPhpUnitExecutable();

        $phpunitExec = $this->taskExec($phpunit_executable)
            ->option('testsuite', 'Custom Test Suite')
            ->dir("{$proj_dir}/docroot/modules/custom");

        foreach ($args as $arg) {
            $phpunitExec->arg($arg);
        }

        return $phpunitExec->run();
    }

    /**
     * Determine path to PHPUnit executable.
     */
    private function getPhpUnitExecutable()
    {
        $proj_dir = Util::getProjectDirectory();

        $phpunit_executable = $phpunit_executable = "{$proj_dir}/vendor/bin/phpunit";

        if (!file_exists($phpunit_executable)) {
            $this->taskExec("dktl installphpunit")->run();
            $phpunit_executable = "phpunit";
        }

        return $phpunit_executable;
    }

    /**
     * Ensure current git branch is not in a detached state.
     *
     * @return bool
     *   Flag for whether the current branch branch is detached.
     */
    private function inGitDetachedState($dkanDirPath)
    {
        $output = [];
        exec("cd {$dkanDirPath} && git rev-parse --abbrev-ref HEAD", $output);
        return (isset($output[0]) && $output[0] == 'HEAD');
    }
}
