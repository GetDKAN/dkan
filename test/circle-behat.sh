# This file is to help with running behat tests on circleCI. It's needed to:
#  - Handle circleCI's parallelization.
#  - Handle searching in multiple directories for feature files.
# Ex. There are 3 VMs setup: (3)
if [ -z "$CIRCLE_NODE_INDEX" ]; then
  echo "No parrallelism found, setting defaults to run all tests."
  CIRCLE_NODE_TOTAL=1
  CIRCLE_NODE_INDEX=0
fi

echo "\$CIRCLE_NODE_TOTAL = $CIRCLE_NODE_TOTAL"
echo "\$CIRCLE_NODE_INDEX = $CIRCLE_NODE_INDEX"

error=0
pwd=$(pwd)
declare -a files
# Fetch all of the feature files for each parameter (directories)
for search_dir in "$@"; do
    echo "Seaching $search_dir for feature files..."
    while read -r path; do
       echo "-- found $path"
       files=(${files[@]} "$path")
    # Bash wizardry so we can update the array (otherwise it's a subprocess)
    done < <(find $search_dir | grep "\.feature$")
done

for i in "${!files[@]}"; do
    if [ $(($i % $CIRCLE_NODE_TOTAL)) -eq $CIRCLE_NODE_INDEX ]; then
      echo "Running ahoy dkan test --format=pretty --out=std --format=junit --out=$CIRCLE_ARTIFACTS/junit `pwd`/${files[$i]}"
      time ahoy dkan test --format=pretty --out=std --format=junit --out=$CIRCLE_ARTIFACTS/junit `pwd`/${files[$i]}
    fi
    # Mark the entire script as a failure if any of the iterations fail.
    if [ ! $? -eq 0 ]
    then
      error=1
    fi
done
exit $error
