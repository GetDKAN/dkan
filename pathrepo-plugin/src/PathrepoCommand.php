<?php

namespace Dkan\Composer\Plugin\Pathrepo;

use Composer\Command\BaseCommand;
use Composer\Config;
use Composer\Config\JsonConfigSource;
use Composer\Factory;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The "dkan:pathrepo" command class.
 *
 * @internal
 */
class PathrepoCommand extends BaseCommand
{

  protected static $pluginPrefix = 'dkan_pathrepo_';

  /**
   * @var Config
   */
  protected $config;

  /**
   * @var JsonConfigSource
   */
  protected $configSource;

  /**
   * Represents the root composer.json file.
   *
   * @var JsonFile
   */
  protected $jsonFile;

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
      ->setName('dkan:pathrepo')
      ->setAliases(['pathrepo'])
      ->setDefinition(array(
        new InputArgument('relative_local_path', InputArgument::REQUIRED, 'Relative local path to add to the list of repositories.'),
        new InputOption('unset', null, InputOption::VALUE_NONE, 'Unset the given path repository.'),
        new InputOption('package', null, InputOption::VALUE_OPTIONAL, 'Assumed to be the package in the path repo, will be set to version constraint "@dev".'),
      ))
      ->setDescription('Set a path to be a path repository.')
      ->setHelp(
        <<<EOT
@todo: Improve this documentations.
EOT
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->jsonFile = new JsonFile(Factory::getComposerFile(), null, $this->getIO());

    $relative_local_path = $input->getArgument('relative_local_path');
    $repo_package = $input->getOption('package') ?? '';
    $unset = $input->getOption('unset') ?? FALSE;

    // We can't undo a package change, so tell the user they're out of luck on
    // unset.
    if ($unset && $repo_package) {
      $output->writeln('Can\'t undo a package change while unsetting a repo.');
      return 1;
    }

    // remove the dot for our repo name.
    $relative_local_path_pieces = explode('/', $relative_local_path);
    if ($first = $relative_local_path_pieces[0] ?? FALSE) {
      if ($first === '.') {
        unset($relative_local_path_pieces[0]);
      }
    }
    $repo_name = static::$pluginPrefix . implode('.', $relative_local_path_pieces);

    $config_source = new JsonConfigSource($this->jsonFile);
    $package_info = $this->jsonFile->read();

    $existing_repos = $this->findExistingReposForPath($relative_local_path);
    if ($unset) {
      // Remove all the repos with this path.
      $removed_repos = [];
      $package_info = $this->jsonFile->read();
      // Look for our path and remove, even duplicates.
      if ($repositories = $package_info['repositories'] ?? FALSE) {
        foreach ($repositories as $name => $info) {
          if (in_array($name, $existing_repos)) {
            $config_source->removeRepository($name);
            $removed_repos[] = $name;
          }
        }
      }
      if ($removed_repos) {
        $output->writeln('Removed: ' . implode(', ', $removed_repos));
      } else {
        $output->writeln('Unable to find a repo to remove for path ' . $relative_local_path);
      }
    } else {
      // Adding a repo, unless one already exists.
      if ($existing_repos) {
        $output->writeln('Repository ' . implode(', ', $existing_repos) . ' already exists. Taking no further action.');
        return 0;
      }
      // Add our repo.
      $config_source->addRepository($repo_name, ['type' => 'path', 'url' => $relative_local_path], false);
      $output->writeln('Added repo ' . $repo_name . ' for path ' . $relative_local_path);
    }

    if ($repo_package) {
      $package_info = $this->jsonFile->read();
      $changed = FALSE;
      if (key_exists($repo_package, $package_info['require'] ?? [])) {
        if ($package_info['require'][$repo_package] !== '@dev') {
          $package_info['require'][$repo_package] = '@dev';
          $output->writeln('Package ' . $repo_package . ' constraint set to @dev.');
          $changed = TRUE;
        } else {
          $output->writeln('Package ' . $repo_package . ' constraint already set to @dev.');
        }
      }
      if (key_exists($repo_package, $package_info['require-dev'] ?? [])) {
        if ($package_info['require-dev'][$repo_package] !== '@dev') {
          $package_info['require-dev'][$repo_package] = '@dev';
          $output->writeln('Package ' . $repo_package . ' constraint set to @dev.');
          $changed = TRUE;
        } else {
          $output->writeln('Package ' . $repo_package . ' constraint already set to @dev.');
        }
      }
      if ($changed) {
        $this->jsonFile->write($package_info);
      }
    }

    return 0;
  }

  /**
   * @param $pathrepo_path
   * @return string[]
   *   All the repository names which match the path.
   *
   * @throws \Seld\JsonLint\ParsingException
   */
  protected
  function findExistingReposForPath($pathrepo_path)
  {
    $existing = [];

    $real_pathrepo_path = realpath($pathrepo_path);
    // Realpath() says FALSE if the path doesn't exist. In that case, set it
    // to NULL so we can compare with the result from other realpath() calls.
    if ($real_pathrepo_path === FALSE) {
      $real_pathrepo_path = NULL;
    }

    $package_info = $this->jsonFile->read();
    if ($repositories = $package_info['repositories'] ?? FALSE) {
      foreach ($repositories as $name => $info) {
        if (($info['type'] ?? '') == 'path') {
          $repo_url = ($info['url'] ?? '');
          // Literal path match?
          if ($repo_url == $pathrepo_path) {
            $existing[] = $name;
          } else {
            // Try realpath. Always use strict type compare.
            if (realpath($repo_url) === $real_pathrepo_path) {
              $existing[] = $name;
            }
          }
        }
      }
    }

    return $existing;
  }

}
