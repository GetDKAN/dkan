<?php

namespace DkanTools\Util;

/**
 * Misc utilities used throughout the application.
 *
 * @todo Refactor this to follow Robo standards (tasks? base command class?)
 */
class Util
{
    const TMP_DIR = "/tmp/dktl";

    public static function getDktlDirectory()
    {
        return getenv("DKTL_DIRECTORY");
    }

    public static function getDktlProxyDomain()
    {
        if ($proxy = getenv("DKTL_PROXY_DOMAIN")) {
            return $proxy;
        } else {
            return '';
        }
    }

    public static function getProjectDirectory() {
      if ($proj_dir = getenv("DKTL_PROJECT_DIRECTORY")) {
        return $proj_dir;
      }
      if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == "init") {
        $directory = exec("pwd");
        return $directory;
      }
    }

    public static function getUri()
    {
        $domain = getenv("DKTL_PROXY_DOMAIN");
        if ($domain) {
            $domain = "http://" . $domain;
        }
        return $domain;
    }

    public static function getProjectDocroot()
    {
        return self::getProjectDirectory() . "/docroot";
    }

    public static function drushConcurrency()
    {
        if (`uname` == "Darwin") {
            $concurrency = trim(`sysctl -n hw.ncpu`);
        } else {
            $concurrency = trim(`grep -c ^processor /proc/cpuinfo`);
        }
        return is_numeric($concurrency) ? $concurrency : '';
    }

    public static function prepareTmp()
    {
        $tmp_dir = self::TMP_DIR;
        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir);
        }
    }

    public static function cleanupTmp()
    {
        $tmp_dir = self::TMP_DIR;
        if (file_exists($tmp_dir)) {
            exec("rm -rf {$tmp_dir}");
        }
    }

    public static function urlExists($url)
    {
        $headers = @get_headers($url);
        return (count(preg_grep('/^HTTP.*404/', $headers)) > 0) ? false : true;
    }

    public static function directoryAndFileCreationCheck(\Robo\Result $result, $df, $io)
    {
        if ($result->getExitCode() == 0 && file_exists($df)) {
            $io->success("{$df} was created.");
        } else {
            throw new \Exception("{$df} was not created.");
        }
    }

    /**
     * Copy of \Drupal\Component\Utility\Crypt::randomBytesBase64()
     */
    public static function generateHashSalt($count = 32)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes($count)));
    }

    /**
     * For each file in the array $paths, make sure it exists.  If not, throw an
     * Exception.
     */

    public static function ensureFilesExist(array $paths, $message)
    {
        foreach ($paths as $path) {
            if (! file_exists($path)) {
                throw new \Exception("{$path} is missing.");
            }
        }
    }
}
