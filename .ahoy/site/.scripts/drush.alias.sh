mkdir -p ~/.drush
cp -L ./assets/drush/aliases.local.php ~/.drush
name=$(ahoy utils name)
touch ~/.drush/$name.aliases.drushrc.php

echo '
<?php
// Added by NuCivic.
$aliases_local = realpath(dirname(__FILE__)) . "/aliases.local.php";
if (file_exists($aliases_local)) {
  include $aliases_local;
}
' > ~/.drush/$name.aliases.drushrc.php
