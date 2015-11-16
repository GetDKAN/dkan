#!/usr/bin/env bash

show_help () {
  cat << EOF
=== DKAN INIT SCRIPT. v0.1 ===

This script will initialize the dkan install profile or a dkan-submodule to prepare a full drupal site. It should be run only once to set that up.

For DKAN core:
--------------
  Usage: bash dkan-init.sh dkan

  What it does:
    - Moves everything in the current folder into a new ./dkan subfolder
    - Creates an initial ahoy.yml file based on dkan/.ahoy/starter.ahoy.yml

For DKAN submodules:
--------------------
  Usage: bash dkan-init.sh dkan_submodule_name

  What it does:
    - Moves everything in the current folder into a temporary ./dkan_submodule_name subfolder
    - Clones dkan from git@github.com:NuCivic/dkan.git into a new ./dkan folder
    - Moves ./dkan_submodule_name to ./dkan/modules/dkan/dkan_submodule_name
    - Creates an initial ahoy.yml file based on dkan/.ahoy/starter.ahoy.yml

Using ahoy:
-----------
  After you run this script and install ahoy (if you haven't yet), use the 'ahoy' command for additional steps like the following:

  Create a new drupal website in the ./docroot folder:
      ahoy dkan drupal-rebuild [DB_URL i.e. mysql://user:password@server/database_name]"

  Run drush make to install all the dkan dependencies:
      ahoy dkan remake

  Reinstall the drupal database:
      ahoy dkan reinstall

Questions? Bugs?
----------------

  Internal teams should create a pluto ticket. Others should create a ticket on github.
EOF
}

# Make sure that a parameter was set.
if [ ! $1 ]; then
  echo "Error: Missing the dkan module name!"
  echo ""
  show_help
  exit 1
fi

# This allows us to use !(dkan) to move all the files into a subfolder without recursion.
shopt -s extglob dotglob

mkdir $1 2> /dev/null && echo "Created ./$1 folder.." || echo "./$1 folder already exists.. exiting."

mv !($1) $1 && echo "Moved all files into ./$1.." || echo "Error moving files into ./$1.. exiting."
shopt -u dotglob


if [ "$1" != "dkan" ]; then
  echo "Cloning dkan.."
  git clone git@github.com:NuCivic/dkan.git --branch dev-dkan-ahoy
fi

if [ -f dkan/.ahoy/starter.ahoy.yml ]; then
  cp dkan/.ahoy/starter.ahoy.yml .ahoy.yml && echo "Created an initial ahoy file at ./.ahoy.yml based on ./dkan/.ahoy/starter.ahoy.yml. Feel free to customize if you need."
else
  echo "dkan/.ahoy/starter.ahoy.yml doesn't exist. Make sure you use a dkan branch with the latest ahoy files."
fi

echo "A DKAN Drupal site has been initialized at ./docroot. Type 'ahoy' for DKAN commands."
ahoy || echo "Notice: ahoy is not installed. Follow the instructions at https://github.com/devinci-code/ahoy to install it."
echo "To complete a dkan installation, run the following commands :"
echo "  ahoy dkan drupal-rebuild [DB_URL i.e. mysql://user:password@server/database_name]"
echo "  ahoy dkan remake && "
echo "  ahoy dkan reinstall && "
