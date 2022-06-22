<?php

namespace DkanTools\Command;

use DkanTools\Util\Util;
use Robo\Tasks;

class XdebugCommands extends \Robo\Tasks
{
    /**
     * Check for existence of XDEBUG_DKTL environment variable.
     */
    private function xdebugCheck()
    {
        $xdebugDktl = getenv("XDEBUG_DKTL");
        if (!$xdebugDktl) {
            throw new \Exception("XDEBUG_DKTL environment variable must be "
            . " set to use this command.");
        }
    }

    /**
     * Start xdebug on CLI and web containers.
     *
     * This command adds a .ini file to your src/docker/etc/php directory and
     * restarts the CLI and web containers. It checks for compatibility by
     * looking for a DKTL_XDEBUG environment variable. This is set by default in
     * DKAN Tools' containers, but you may need to set it again if using your
     * own.
     *
     * Adding /src/docker/etc to your project .gitignore is recommended.
     */
    public function xdebugStart()
    {
        $this->xdebugCheck();

        $platform = getenv("PLATFORM");
        $sourceFile = ($platform == 'Darwin') ? 'xdebug-macos.ini' : 'xdebug-linux.ini';
        $dktlRoot = Util::getDktlDirectory();
        $this->io()->text("Creating new xdebug.ini file for {$platform} platform.");

        $f = 'src/docker/etc/php/xdebug.ini';
        if (file_exists($f)) {
            throw new \Exception("File {$f} already exists.");
        }

        $result = $this->taskWriteToFile($f)
            ->textFromFile("$dktlRoot/assets/docker/etc/php/$sourceFile")
            ->run();

        Util::directoryAndFileCreationCheck($result, $f, $this->io());
    }

    /**
     * Stop xdebug on CLI and web containers.
     *
     * Removes the xdebug.ini file and restarts CLI and web containers. See
     * xdebug:start for more information.
     */
    public function xdebugStop()
    {
        $this->xdebugCheck();

        $f = 'src/docker/etc/php/xdebug.ini';
        $result = unlink($f);
        if ($result) {
            $this->io()->success("Removed xdebug.ini; restarting.");
            return $result;
        } else {
            throw new \Exception("Failed, xdebug.ini not found.");
        }
    }
}
