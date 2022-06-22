<?php

namespace DkanTools\Command;

use DkanTools\Util\Util;
use Robo\Tasks;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class MakeCommands extends Tasks
{
    const DRUPAL_FOLDER_NAME = "docroot";

    /**
     * Get all necessary dependencies and "make" a working codebase.
     *
     * Running `dktl make` will:
     *   1. Run composer install.
     *   2. Rebuild symlinks between the src and docroot dirs.
     *   3. If in docker mode, connect the proxy to enable local domain.
     *   4. If a frontend is present, install and build it.
     *
     * @option prefer-dist
     *   Prefer dist for packages. See composer docs.
     * @option prefer-source
     *   Prefer dist for packages. See composer docs.
     * @option no-dev
     *   Skip installing packages listed in require-dev.
     * @option optimize-autoloader
     *   Convert PSR-0/4 autoloading to classmap to get a faster autoloader.
     */
    public function make($opts = [
        'prefer-source' => false,
        'prefer-dist' => true,
        'optimize-autoloader' => false,
        'no-dev' => false,
    ])
    {
        $this->io()->section("Running dktl make");

        // Run composer install while passing the options.
        $composerInstall = $this->taskComposerInstall();
        $composerOptions = ['prefer-source', 'prefer-dist', 'optimize-autoloader', 'no-dev'];
        foreach ($composerOptions as $opt) {
            if ($opts[$opt]) {
                $composerInstall->option($opt);
            }
        }
        $result = $composerInstall->run();
        if ($result->getExitCode() != 0) {
            return $result;
        }

        // Symlink dirs from src into docroot.
        $this->makeSymlinks();

        $this->io()->success("dktl make completed.");
    }

    /**
     * Create symlinks from docroot to folders in src.
     *
     * This will run automatically as part of the main make command, but can be
     * run separately if your symlinks have been lost but you don't actually
     * need to re-run make.
     */
    public function makeSymlinks()
    {
        $targetsAndLinks = [
            ['target' => 'src/site',    'link' => '/sites/default'],
            ['target' => 'src/modules', 'link' => '/modules/custom'],
            ['target' => 'src/themes',  'link' => '/themes/custom'],
            ['target' => 'src/libraries',  'link' => '/libraries'],
            ['target' => 'src/schema',  'link' => '/schema'],
        ];
        foreach ($targetsAndLinks as $targetAndLink) {
            $this->docrootSymlink(
                $targetAndLink['target'],
                self::DRUPAL_FOLDER_NAME . $targetAndLink['link']
            );
        }
    }

    private function docrootSymlink($target, $link)
    {
        $project_dir = Util::getProjectDirectory();
        $target = $project_dir . "/{$target}";
        $link = $project_dir . "/{$link}";
        $link_parts = pathinfo($link);
        $link_dirname = $link_parts['dirname'];
        $target_path_relative_to_link = (new Filesystem())->makePathRelative($target, $link_dirname);

        if (!file_exists($target) || !file_exists(self::DRUPAL_FOLDER_NAME)) {
            $this->io()->warning(
                "Skipping linking $target. Folders $target and '" .
                self::DRUPAL_FOLDER_NAME . "' must both be present to create link."
            );
            return;
        }

        $result = $this->taskFilesystemStack()->stopOnFail()
            ->remove($link)
            ->symlink($target_path_relative_to_link, $link)
            ->run();

        if ($result->getExitCode() != 0) {
            $this->io()->warning('Could not create link');
        } else {
            $this->io()->success("Symlinked $target to $link");
        }
        return $result;
    }
}
