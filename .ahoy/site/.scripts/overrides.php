<?php
include __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

try {
  $yaml = new Parser();
  $overrides_make = $yaml->parse(file_get_contents(__DIR__ . '/../../../../config/overrides.make'));
  $drupal_org_make = make_parse_info_file(realpath(__DIR__ . '/../../../../dkan/drupal-org.make'));

  if (is_array($overrides_make) && is_array($drupal_org_make))  {
    if (isset($overrides_make['projects']['drupal'])) {
      unset($overrides_make['projects']['drupal']);
    }
    $overriden_modules = array_keys($overrides_make['projects']);
    foreach ($overriden_modules as $key) {
      $module_definition = array_replace_recursive(
        $overrides_make['projects'][$key],
        $drupal_org_make['projects'][$key]
      );
      $overrides_make['projects'][$key] = $module_definition;
    }
    $dumper = new Dumper();
    $overriden_yaml = $dumper->dump($overrides_make, 4);
    file_put_contents(__DIR__ . '/../../../../overriden_make.make', $overriden_yaml);
  }

} catch (Exception $e) {

  echo "An error happened trying to override drupal-org.make:\n{$e->getMessage()}\n";
}

