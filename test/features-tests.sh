#!/bin/bash
set -e

# First check if any features are overridden.
if [ -n "$(drush fd | grep Overridden)" ]; then
      echo "---> Error: Features are showing as overridden.";
      drush fd;
      exit 1
fi

# Then reexport all the features and make sure there aren't unexpected changes.
drush fua -y

# Note that this actually checks for ANY changes to files that git is tracking,
# not just features.
if [ -n "$(git status -uno --porcelain)" ]; then
#if  ! git status --untracked-files=no --porcelain then
      echo "---> Error: Features need to be re-exported, or you have some changes to other files in git. ";
      git status;
      git diff;
      exit 1
fi

# Otherwise we should be good to go.
echo "---> Success: Features are up to date.";
