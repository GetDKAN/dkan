<?php
/**
 * Transpose config.yml to php array.
 */

include realpath(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

// Setup twig.
$loader = new Twig_Loader_Filesystem(realpath(__DIR__ . '/../.templates'));
$twig = new Twig_Environment($loader, array(
    'cache' => realpath(__DIR__ . '/../.cache'),
));
$twig->addFunction(new Twig_SimpleFunction('var_export', 'var_export'));

try {
  // Parse yaml.
  $yaml = new Parser();
  $config = $yaml->parse(file_get_contents(__DIR__ . '/../../../../config/config.yml'));

  // Render yaml using twig template.
  $context = array(
    'config' => $config,
  );
  $output = $twig->render(
    'config.php.twig',
    $context
  );

  // Write the php file.
  $file = fopen(__DIR__ . '/../../../../config/config.php', 'w');
  fwrite($file, $output);
} catch (Exception $e) {
  echo "An error happened trying to transpose the config.yml file:\n{$e->getMessage()}\n";
} finally {
  if ($file) {
    fclose($file);
  }
}

