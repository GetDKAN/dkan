#!/usr/bin/env bash

set -e

# This allows us to use !(dkan) to move all the files into a subfolder without recursion.
shopt -s extglob dotglob

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

Automatically installing dependencies:
--------------------------------------
  Usage: bash dkan-init.sh dkan [--install-dependencies | --deps ]

  Installs composer, drush (8.0.1), and ahoy (1.1.0). Some commands are run as sudo and may require your password.

Automatically installing dkan:
--------------------------------------
  Usage: bash dkan-init.sh dkan [--build-dkan | --build ] [DB_URL ie. mysql://root:rootpw/localhost/db]

  Builds a full dkan site by running the ahoy commands for you. (See using ahoy). To install to the correct database, you need to supply the dburl to use.

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

if [[ $EUID -eq 0 ]] || [ -z $USER ]; then
  IS_ROOT=true
  this_user="root"
  AUTO_SUDO=""
else
  this_user=$USER
  AUTO_SUDO="sudo"
fi
echo "User is $this_user"


get_platform() {
  PLATFORM="$(uname -s)"
}

error() {
  echo ""
  echo "[Error] $1"
  echo ""
  show_help
  exit 1
}

alert() {
  echo ""
  echo "$1"
  echo ""
}

install_dependencies() {
  alert "Installing dependencies if they don't exist."

  #TODO Check AHOY_CMD_PROXY
  # SUPPORT HOMEBREW (where available)
  if [ ! "$(which composer)" ]; then
    echo "> Installing Composer";
    echo "> Fake install composer"
    curl -sS https://getcomposer.org/installer | $AUTO_SUDO php -- --install-dir=/usr/local/bin --filename=composer
    if [ ! "$(which composer)" ]; then
      error "Installation of composer failed."
    fi
  else
    echo "> Composer already installed"
  fi

  COMPOSER_PATH=".composer/vendor/bin"

  if [[ "$PATH" != *"$COMPOSER_PATH"* ]]; then
    echo "> Composer PATH is not set. Adding temporarily.. (you should add to your .bashrc)"
    echo "PATH (prior) = $PATH"
    export PATH="$PATH:~/$COMPOSER_PATH"
  fi

  DRUSH_VERSION="8.0.2"
  if [ ! "$(which drush)" ]; then
    echo "> Installing Drush";
    composer global require --prefer-source --no-interaction drush/drush:$DRUSH_VERSION
    if [ ! "$(which drush)" ]; then
      error "Installation of drush failed."
    fi
  elif [[ "$(drush --version)" != *"$DRUSH_VERSION"* ]]; then
    old_version=$(drush --version)
    old_drush=$(which drush)
    echo "Drush version is not up to date: $drush_version should be $DRUSH_VERSION. Removing old drush and updating."
    $AUTO_SUDO mv "$old_drush" "$old_drush-old"
    composer global require --prefer-source --no-interaction drush/drush:"$DRUSH_VERSION"
    if [[ "$(drush --version)" != *"$DRUSH_VERSION"* ]]; then
      echo "Drush Path: $(which drush)"
      echo "\$PATH: $PATH"
      echo "$(drush --version)"
      error "Installation of drush failed."
    fi
    echo "Drush updated to $DRUSH_VERSION"
  else
    echo "> Drush already installed and up to date."
  fi

  if [ ! "$(which ahoy)" ]; then
    echo "> Installing Ahoy";
    $AUTO_SUDO wget -q http://nucivic-binaries.s3-us-west-1.amazonaws.com/ahoy -O /usr/local/bin/ahoy -O /usr/local/bin/ahoy &&
    $AUTO_SUDO chown $this_user /usr/local/bin/ahoy &&
    $AUDO_SUDO chmod +x /usr/local/bin/ahoy
    if [ ! "$(which ahoy)" ]; then
      error "Installation of ahoy failed."
    fi
  else
    echo "> Ahoy already installed"
  fi
}

install_dkan() {
  if [ -z "$1" ]; then
    error "Installing dkan for the first time requires a db_url."
  fi
  alert "Running 'ahoy dkan drupal-rebuild $1' .."
  eval "set -e; $AUTO_CONFIRM ahoy dkan drupal-rebuild $1" || error "Error while running drupal-rebuild." &&

  alert "Running 'ahoy dkan remake' .."
  ahoy dkan remake || error "Error while running remake." &&

  if [ ! "$SKIP_REINSTALL" ]; then
    alert "Running 'ahoy dkan reinstall'.."
    eval "set -e; $AUTO_CONFIRM ahoy dkan reinstall" || error "Error while running reinstall."
  fi
}

# Make sure that a parameter was set.
if [ -z $1 ]; then
  error "Missing the dkan module name or 'dkan' if using dkan core."
else
  MODULE_NAME=$1
  echo "Creating setup for $1..."
  shift
fi

BRANCH="7.x-1.x"

for i in "$@"; do
  case "$i" in
    -h|--help)
            show_help
            exit 0
            ;;
    --skip-init)
            SKIP_INIT=true
            ;;
    --skip-reinstall)
            SKIP_REINSTALL=true
            ;;
    --yes)
            AUTO_CONFIRM="echo -ne 'y\n' | "
            ;;
    --qa-users)
            QA_USERS=true
            ;;
    --no)
            AUTO_CONFIRM="echo -ne 'n\n' | "
            ;;
    --branch=*)
            BRANCH="${i#*=}"
            ;;
    --build)
            error"The '--build' flag must have a db url set:\n    --build=mysql://user:password@server/database_name"
            ;;
    --build=*)
            DB_URL="${i#*=}"
            ;;
    --deps)
            INSTALL_DEPS=true
            ;;
    --deps-only)
            echo "Only installing dependencies...."
            install_dependencies
            exit $?
            ;;
    *)
            error "not recognized flag or param ${i#*=}"
            ;;
  esac
done

# -------- MAIN -----------
if [ "$INSTALL_DEPS" ]; then
  install_dependencies
fi

# -- BEGIN SETUP FOLDERS --
if [ ! "$SKIP_INIT" ]; then

  mkdir $MODULE_NAME 2> /dev/null && echo "Created ./$MODULE_NAME folder.." || ( echo "./$MODULE_NAME folder already exists.. exiting."; ls -la; exit 1)

  mv !($MODULE_NAME) $MODULE_NAME && echo "Moved all files into ./$MODULE_NAME.." || ( echo "Error moving files into ./$MODULE_NAME.. exiting."; exit 1)

  if [ "$MODULE_NAME" != "dkan" ]; then
    echo "Cloning dkan.."
    # switched to https because ssh keys may not exist in all environments (Probo)
    git clone https://github.com/NuCivic/dkan.git --branch $BRANCH
  fi

  if [ -f dkan/.ahoy/starter.ahoy.yml ]; then
    cp dkan/.ahoy/starter.ahoy.yml .ahoy.yml && echo "Created an initial ahoy file at ./.ahoy.yml based on ./dkan/.ahoy/starter.ahoy.yml. Feel free to customize if you need."
  else
    echo "dkan/.ahoy/starter.ahoy.yml doesn't exist. Make sure you use a dkan branch with the latest ahoy files."
  fi

  echo "A DKAN Drupal site has been initialized at ./docroot. Type 'ahoy' for DKAN commands."
else
  alert "Skipping folder initialization and assuming it was already run..."
fi

# -- END SETUP FOLDERS --

if [ "$DB_URL" ]; then
  alert "Building and installing dkan..."
  install_dkan $DB_URL

  if [ "$QA_USERS" ]; then
    ahoy dkan create-qa-users
  fi


  alert "DKAN should be fully installed. Make sure you add the docroot folder to your apache config."
else
  # Give some help for manually installing for users who don't set --build flag.
  ahoy dkan || echo "Notice: ahoy is not installed. Follow the instructions at https://github.com/devinci-code/ahoy to install it."
  echo "To complete a dkan installation, run the following commands :"
  echo "  ahoy dkan drupal-rebuild [DB_URL i.e. mysql://user:password@server/database_name]"
  echo "  ahoy dkan remake && "
  echo "  ahoy dkan reinstall && "
fi

shopt -u dotglob

