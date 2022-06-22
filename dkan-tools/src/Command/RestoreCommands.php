<?php

namespace DkanTools\Command;

use DkanTools\Util\Util;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RestoreCommands extends \Robo\Tasks
{
    /**
     * Restore files and database.
     *
     * A command that creates a DKAN site from a db dump and files. The restore
     * command supports files compressed in zip, .gz and .tar.gz formats, and
     * will accept URLs using the http, https or s3 protocols.
     *
     * For files, dktl expects that the archive contain a files/ dir either in
     * the root of the archive or under a dir with the same basename as the
     * archive. (For instance, files in mysite.tar.gz could be located either in
     * /mysite/files/ or just /files/).
     *
     * If your site uses private files, a second dir called private/ may be
     * included at the same level as files/. These files will be copied to a
     * new "/private" dir created in your project root.
     *
     * It is recommended that you set the options for this in command in
     * dktl.yml (see README for more information).
     *
     * @todo Give feedback when no options provided.
     *
     * @param array $opts
     * @option $db_url
     *   The database archive URL. sql and sql.gz files are supported.
     * @option $files_url
     *   A url to the site files archive. zip, gz, and tar.gz files are supported.
     */
    public function restore($opts = ['db_url' => null, 'files_url' => null])
    {
        if ($opts['db_url']) {
            $this->restoreDb($opts['db_url']);
        }
        if ($opts['files_url']) {
            $this->restoreFiles($opts['files_url']);
        }
    }

    /**
     * Restore the database from a backup.
     *
     * @param string $file
     *   URL to remote backup or a filename that lives in the /backups directory.
     */
    public function restoreDb($file = null)
    {
        // If no file argument provided, check out the backups dir.
        if (!$file) {
            $filepath = $this->getDbBackupPath();
        } elseif (filter_var($file, FILTER_VALIDATE_URL)) {
            // If provided a URL, get it with getFile().
            $filepath = $this->getFile($file);
            $tempNeedsCleanup = true;
        } elseif (!file_exists('backups') || !file_exists("backups/{$file}")) {
            // If not a URL check for existence of file in /backups.
            throw new \Exception("{$file} backup could not be found.");
        } else {
            $filepath = realpath("backups/$file");
        }
        $info = pathinfo($filepath);

        $s = $this->taskExecStack()->stopOnFail()->dir(Util::getProjectDocroot());
        $s->exec('drush -y sql-drop');
        if ($info['extension'] == "gz") {
            $s->exec("zcat $filepath | drush sqlc");
        } else {
            $s->exec("drush sqlc < $filepath");
        }
        $result = $s->run();

        if ($result->getExitCode() == 0) {
            $this->io()->success('Database restored.');
        } else {
            $this->io()->error('Issues restoring the database.');
        }
        Util::cleanupTmp();

        return $result;
    }

    /**
     * Ask for which backup to use from the project /backups dir
     */
    private function getDbBackupPath()
    {
        if (file_exists('backups')) {
            $backups = array_values(array_diff(scandir('backups'), ['.', '..']));
            if (empty($backups)) {
                throw new \Exception('No backup files available.');
            } elseif (count($backups) === 1) {
                $filename = current($backups);
                $this->io()->note("No filename provided; using $filename.");
            }
            if (!empty($backups)) {
                $filename = $this->io()->choice('Choose backup file', $backups);
            }
            return realpath("backups/$filename");
        } else {
            throw new \Exception("No backup files available.");
        }
    }

    /**
     * Restore a files archive to appropriate site directories.
     *
     * @param string $files_url Files URL to restore.
     * @see self::dkanRestore() For full documentation on URL params.
     */
    public function restoreFiles(string $files_url)
    {
        Util::prepareTmp();
        $filePath = $this->getFile($files_url);
        $projectDirectory = Util::getProjectDirectory();

        $parentDir = $this->restoreFilesExtract($filePath);

        if (is_dir("{$parentDir}/files")) {
            $this->restoreFilesCopy("{$parentDir}/files", "{$projectDirectory}/src/site/files");
            $this->_exec("chmod -R 777 {$projectDirectory}/src/site/files");
        }
        if (is_dir("{$parentDir}/private")) {
            $this->restoreFilesCopy("{$parentDir}/private", "{$projectDirectory}/private");
        }
        if (!is_dir("{$parentDir}/files") && !is_dir("{$parentDir}/private")) {
            $this->io->warning('No files found');
            return false;
        }

        Util::cleanupTmp();
    }

    /**
     * Extract a zip or tar.gz archive in the tmp dir.
     *
     * @param string $filePath  The path to the archive.
     *
     * @return string The directory to which the archive was extracted.
     */
    private function restoreFilesExtract(string $filePath)
    {
        $tmpPath = Util::TMP_DIR;
        $info = pathinfo($filePath);
        $extension = $info['extension'];

        if ($extension == "zip") {
            $taskUnzip = $this->taskExec("unzip $filePath -d {$tmpPath}");
            $parentDir = substr($filePath, 0, -4);
        } elseif ($extension == "gz") {
            if (substr_count($filePath, ".tar") > 0) {
                $taskUnzip = $this->taskExec("tar -xvzf {$filePath}")->dir($tmpPath);
                $parentDir = substr($filePath, 0, -7);
            } else {
                $taskUnzip = $this->taskExec("gunzip {$filePath}");
                $parentDir = substr($filePath, 0, -3);
            }
        } else {
            throw new \Exception('Could not extract file.');
        }
        $result = $taskUnzip->run();
        if (!is_dir($parentDir)) {
            $parentDir = dirname($parentDir);
        }
        if (is_dir($parentDir) && $result->getExitCode() == 0) {
            return $parentDir;
        }
        throw new \Exception('Extraction failed.');
    }

    private function restoreFilesCopy(string $source, string $destination)
    {
        if (is_dir($source)) {
            $this->say('Copying files');
            $result = $this->taskCopyDir([$source => $destination])->run();
            if ($result->getExitCode() == 0) {
                $this->io()->success("Files restored to $destination.");
            } else {
                throw new \Exception("Failed restoring files to $destination.");
            }
            return $result;
        }
        throw new \Exception("Source dir $source not found.");
    }

    private function getFile($url)
    {
        $tmp_dir_path = Util::TMP_DIR;

        if (substr_count($url, "http://") > 0 || substr_count($url, "https://")) {
            $info = pathinfo($url);
            $filename = $info['basename'];
            $approach = "wget -O {$tmp_dir_path}/{$filename} {$url}";
        } elseif (substr_count($url, "s3://")) {
            $parser = new \Aws\S3\S3UriParser();
            $info = $parser->parse($url);
            $filename = $info['key'];
            $approach = "aws s3 cp {$url} {$tmp_dir_path}/";
        } else {
            $this->io()->error("Unsupported file protocol.");
            return;
        }

        $result = $this->taskExec($approach)->run();

        if ($result->getExitCode() == 0) {
            $this->io()->success("Got the file from {$url}.");
            return "$tmp_dir_path/$filename";
        } else {
            $this->io()->error("Issues getting the file from {$url}.");
            throw new \Exception("Error retrieving file.");
        }
    }


  /**
   * Create a database dump excluding devel and datastore tables.
   *
   * Run drush command on $alias to create a database dump excluding tables
   * related to devel and datastore.
   *
   * @param String $alias Drush alias of the site we want the db from.
   */
    public function restoreGrabDatabase($alias)
    {
      // Tables for which we want the structure and not the data.
        $structure_tables_common = array(
        'accesslog', 'batch', 'cache', 'cache_*', '*_cache', 'ctools_views_cache',
        'ctools_object_cache', 'flood', 'history', 'queue', 'search_*',
        'semaphore', 'sessions', 'watchdog'
        );
        $structure_tables_devel = array('devel_queries', 'devel_times');
        $structure_tables_webform = array('webform_submitted_data');
        $structure_tables = array_merge(
            $structure_tables_common,
            $structure_tables_devel,
            $structure_tables_webform
        );
        $structure_tables_list = implode(', ', $structure_tables);
      // Tables to be completely skipped.
        $skip_tables = array('dkan_datastore_*');
        $skip_tables_list = implode(', ', $skip_tables);

        return $this->taskExec("drush $alias sql-dump")
            ->option('structure-tables-list', $structure_tables_list)
            ->option('skip-tables-list', $skip_tables_list)
            ->rawArg('> excluded_tables.sql')
            ->run();
    }
}
