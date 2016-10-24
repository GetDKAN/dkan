<?php
include realpath(__DIR__ . '/../vendor/autoload.php');
$loader = new Twig_Loader_Filesystem(realpath(__DIR__ . '/../.templates'));

$twig = new Twig_Environment($loader, array(
    'cache' => realpath(__DIR__ . '/../.cache'),
));

$context = array(
  'path' => getenv('PWD'),
  'url' => getenv('URI'),
);

echo $twig->render(
  'sublime.project',
  $context
);